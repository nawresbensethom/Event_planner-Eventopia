<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Symfony\Component\Console\Attribute\AsCommand;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:generate:entities',
    description: 'Automatically generates entity classes and repositories from the database schema',
)]
class GenerateEntitiesCommand extends Command
{
    private Connection $connection;
    private Filesystem $filesystem;
    private string $entityDir;
    private string $repositoryDir;
    private array $generatedRelations = [];

    public function __construct(Connection $connection, Filesystem $filesystem, string $projectDir)
    {
        parent::__construct();
        $this->connection = $connection;
        $this->filesystem = $filesystem;
        $this->entityDir = $projectDir . '/src/Entity';
        $this->repositoryDir = $projectDir . '/src/Repository';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Generating Entity Classes and Repositories from Database...');

        try {
            $schemaManager = $this->connection->createSchemaManager();
            $tables = $schemaManager->listTables();

            if (empty($tables)) {
                $io->warning('No tables found in the database.');
                return Command::SUCCESS;
            }

            // Collect all relations upfront
            $relations = $this->collectRelations($tables);

            // Generate entities and repositories
            foreach ($tables as $table) {
                $this->generateEntity($table, $relations);
                $this->generateRepository($table);
                $io->success(sprintf(
                    'Generated: %s/%s.php and %s/%sRepository.php',
                    $this->entityDir,
                    ucfirst($table->getName()),
                    $this->repositoryDir,
                    ucfirst($table->getName())
                ));
            }

            $io->success('Entities and repositories successfully generated!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to generate entities: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function collectRelations(array $tables): array
    {
        $relations = [
            'manyToOne' => [],
            'oneToMany' => [],
        ];

        foreach ($tables as $table) {
            $tableName = $table->getName();
            $foreignKeys = $this->getForeignKeys([$tableName]);

            foreach ($foreignKeys as $columnName => $fk) {
                $relatedTable = $fk['referencedTable'];
                $relatedClassName = ucfirst($relatedTable);
                $className = ucfirst($tableName);

                // ManyToOne: From current table to referenced table
                $relations['manyToOne'][$tableName][$columnName] = [
                    'targetEntity' => $relatedClassName,
                    'referencedColumn' => $fk['referencedColumn'],
                ];

                // OneToMany: From referenced table to current table
                $relations['oneToMany'][$relatedTable][] = [
                    'mappedBy' => $columnName,
                    'targetEntity' => $className,
                ];
            }
        }

        return $relations;
    }

    private function generateEntity(Table $table, array $relations): void
    {
        $tableName = $table->getName();
        $className = ucfirst($tableName);
        $entityCode = "<?php\n\nnamespace App\\Entity;\n\nuse Doctrine\\ORM\\Mapping as ORM;\nuse App\\Repository\\{$className}Repository;\n";

        // Collect imports
        $imports = ['Doctrine\\Common\\Collections\\ArrayCollection', 'Doctrine\\Common\\Collections\\Collection'];
        foreach ($relations['manyToOne'][$tableName] ?? [] as $relation) {
            $imports[] = "App\\Entity\\{$relation['targetEntity']}";
        }
        foreach ($relations['oneToMany'][$tableName] ?? [] as $relation) {
            $imports[] = "App\\Entity\\{$relation['targetEntity']}";
        }
        $imports = array_unique($imports);
        sort($imports);
        $entityCode .= implode(";\n", array_map(fn($import) => "use $import", $imports)) . ";\n\n";

        $entityCode .= "#[ORM\\Entity(repositoryClass: {$className}Repository::class)]\n";
        $entityCode .= "#[ORM\\Table(name: \"{$tableName}\")]\n";
        $entityCode .= "class {$className}\n{\n";

        // Generate properties
        $primaryKeys = $table->getPrimaryKey()?->getColumns() ?? [];
        $foreignKeys = $this->getForeignKeys([$tableName]);

        foreach ($table->getColumns() as $column) {
            $entityCode .= $this->generateProperty($column, $primaryKeys, $foreignKeys, $className, $relations);
        }

        // Generate OneToMany collection properties
        if (isset($relations['oneToMany'][$tableName])) {
            foreach ($relations['oneToMany'][$tableName] as $relation) {
                $targetEntity = $relation['targetEntity'];
                $mappedBy = $relation['mappedBy'];
                $propertyName = lcfirst($targetEntity) . 's';
                $entityCode .= "\n    #[ORM\\OneToMany(mappedBy: \"{$mappedBy}\", targetEntity: {$targetEntity}::class, cascade: [\"persist\", \"remove\"])]\n";
                $entityCode .= "    private Collection \${$propertyName};\n";
            }
        }

        // Constructor for initializing collections
        $entityCode .= "\n    public function __construct()\n    {\n";
        if (isset($relations['oneToMany'][$tableName])) {
            foreach ($relations['oneToMany'][$tableName] as $relation) {
                $targetEntity = $relation['targetEntity'];
                $propertyName = lcfirst($targetEntity) . 's';
                $entityCode .= "        \$this->{$propertyName} = new ArrayCollection();\n";
            }
        }
        $entityCode .= "    }\n";

        // Generate getters and setters
        foreach ($table->getColumns() as $column) {
            $entityCode .= $this->generateGettersAndSetters($column, $foreignKeys, $className, $relations);
        }

        // Generate methods for OneToMany relations
        if (isset($relations['oneToMany'][$tableName])) {
            foreach ($relations['oneToMany'][$tableName] as $relation) {
                $targetEntity = $relation['targetEntity'];
                $mappedBy = $relation['mappedBy'];
                $entityCode .= $this->generateRelationMethods($className, $mappedBy, $targetEntity);
            }
        }

        $entityCode .= "}\n";

        // Write the file
        $filePath = "{$this->entityDir}/{$className}.php";
        $this->filesystem->mkdir($this->entityDir);
        $this->filesystem->dumpFile($filePath, $entityCode);
    }

    private function generateRepository(Table $table): void
    {
        $className = ucfirst($table->getName());
        $repositoryCode = "<?php\n\nnamespace App\\Repository;\n\nuse App\\Entity\\{$className};\nuse Doctrine\\Bundle\\DoctrineBundle\\Repository\\ServiceEntityRepository;\nuse Doctrine\\Persistence\\ManagerRegistry;\n\n";
        $repositoryCode .= "/**\n * @extends ServiceEntityRepository<{$className}>\n */\n";
        $repositoryCode .= "class {$className}Repository extends ServiceEntityRepository\n{\n";
        $repositoryCode .= "    public function __construct(ManagerRegistry \$registry)\n    {\n";
        $repositoryCode .= "        parent::__construct(\$registry, {$className}::class);\n    }\n\n";
        $repositoryCode .= "    // Add custom query methods here\n";
        $repositoryCode .= "}\n";

        $filePath = "{$this->repositoryDir}/{$className}Repository.php";
        $this->filesystem->mkdir($this->repositoryDir);
        $this->filesystem->dumpFile($filePath, $repositoryCode);
    }

    private function getForeignKeys(array $tables): array
    {
        $foreignKeys = [];
        $schemaManager = $this->connection->createSchemaManager();

        foreach ($tables as $tableName) {
            $foreignKeyConstraints = $schemaManager->listTableForeignKeys($tableName);
            foreach ($foreignKeyConstraints as $fk) {
                $columns = $fk->getColumns();
                $foreignTable = $fk->getForeignTableName();
                $foreignColumns = $fk->getForeignColumns();

                foreach ($columns as $index => $column) {
                    $foreignKeys[$column] = [
                        'referencedTable' => $foreignTable,
                        'referencedColumn' => $foreignColumns[$index],
                    ];
                }
            }
        }

        return $foreignKeys;
    }

    private function generateProperty(Column $column, array $primaryKeys, array $foreignKeys, string $className, array $relations): string
    {
        $columnName = $column->getName();
        $typeClass = get_class($column->getType());
        $length = $column->getLength();
        $isNullable = !$column->getNotnull();
        $isPrimaryKey = in_array($columnName, $primaryKeys);
        $isForeignKey = isset($foreignKeys[$columnName]);

        $doctrineType = match ($typeClass) {
            'Doctrine\DBAL\Types\IntegerType' => 'integer',
            'Doctrine\DBAL\Types\BigIntType' => 'bigint',
            'Doctrine\DBAL\Types\SmallIntType' => 'smallint',
            'Doctrine\DBAL\Types\BooleanType' => 'boolean',
            'Doctrine\DBAL\Types\DateTimeType' => 'datetime',
            'Doctrine\DBAL\Types\DateType' => 'date',
            'Doctrine\DBAL\Types\TextType' => 'text',
            'Doctrine\DBAL\Types\DecimalType' => 'decimal',
            'Doctrine\DBAL\Types\FloatType' => 'float',
            'Doctrine\DBAL\Types\StringType' => 'string',
            default => 'string',
        };

        $propertyCode = "\n";

        if ($isPrimaryKey) {
            $propertyCode .= "    #[ORM\\Id]\n";
            $propertyCode .= "    #[ORM\\GeneratedValue]\n";
        }

        if ($isForeignKey) {
            $relatedEntity = $foreignKeys[$columnName]['referencedTable'];
            $relatedClassName = ucfirst($relatedEntity);
            $referencedColumn = $foreignKeys[$columnName]['referencedColumn'];
            $propertyCode .= "    #[ORM\\ManyToOne(targetEntity: {$relatedClassName}::class, inversedBy: \"" . lcfirst($className) . "s\")]\n";
            $propertyCode .= "    #[ORM\\JoinColumn(name: \"{$columnName}\", referencedColumnName: \"{$referencedColumn}\", nullable: " . ($isNullable ? 'true' : 'false') . ")]\n";
            $propertyCode .= "    private ?{$relatedClassName} \${$columnName} = null;\n";
        } else {
            $options = [];
            if ($doctrineType === 'string' && $length) {
                $options[] = "length: {$length}";
            }
            if ($isNullable) {
                $options[] = 'nullable: true';
            }
            $optionsStr = $options ? ', ' . implode(', ', $options) : '';
            $propertyCode .= "    #[ORM\\Column(type: \"{$doctrineType}\"{$optionsStr})]\n";
            $phpType = $this->getPHPTypeFromDoctrine($doctrineType);
            $propertyCode .= "    private " . ($isNullable ? '?' : '') . "{$phpType} \${$columnName}" . ($isNullable ? ' = null' : '') . ";\n";
        }

        return $propertyCode;
    }

    private function getPHPTypeFromDoctrine(string $doctrineType): string
    {
        return match ($doctrineType) {
            'integer', 'smallint' => 'int',
            'bigint', 'decimal' => 'string',
            'string', 'text' => 'string',
            'boolean' => 'bool',
            'float' => 'float',
            'datetime', 'date' => '\DateTimeInterface',
            default => 'mixed',
        };
    }

    private function generateGettersAndSetters(Column $column, array $foreignKeys, string $className, array $relations): string
    {
        $columnName = $column->getName();
        $methodName = ucfirst($columnName);
        $isForeignKey = isset($foreignKeys[$columnName]);
        $isNullable = !$column->getNotnull();

        if ($isForeignKey) {
            $relatedClassName = ucfirst($foreignKeys[$columnName]['referencedTable']);
            $getter = "    public function get{$methodName}(): ?{$relatedClassName}\n    {\n        return \$this->{$columnName};\n    }\n\n";
            $setter = "    public function set{$methodName}(?{$relatedClassName} \${$columnName}): self\n    {\n        \$this->{$columnName} = \${$columnName};\n        return \$this;\n    }\n\n";
        } else {
            $typeClass = get_class($column->getType());
            $doctrineType = match ($typeClass) {
                'Doctrine\DBAL\Types\IntegerType', 'Doctrine\DBAL\Types\SmallIntType' => 'integer',
                'Doctrine\DBAL\Types\BigIntType' => 'bigint',
                'Doctrine\DBAL\Types\BooleanType' => 'boolean',
                'Doctrine\DBAL\Types\DateTimeType', 'Doctrine\DBAL\Types\DateType' => 'datetime',
                'Doctrine\DBAL\Types\TextType' => 'text',
                'Doctrine\DBAL\Types\DecimalType', 'Doctrine\DBAL\Types\FloatType' => 'float',
                'Doctrine\DBAL\Types\StringType' => 'string',
                default => 'string',
            };
            $phpType = $this->getPHPTypeFromDoctrine($doctrineType);
            $getter = "    public function get{$methodName}(): " . ($isNullable ? '?' : '') . "{$phpType}\n    {\n        return \$this->{$columnName};\n    }\n\n";
            $setter = "    public function set{$methodName}(" . ($isNullable ? '?' : '') . "{$phpType} \${$columnName}): self\n    {\n        \$this->{$columnName} = \${$columnName};\n        return \$this;\n    }\n\n";
        }

        return $getter . $setter;
    }

    private function generateRelationMethods(string $currentEntity, string $propertyName, string $relatedEntity): string
    {
        $relatedClassName = ucfirst($relatedEntity);
        $relatedProperty = lcfirst($relatedEntity);
        $collectionProperty = "{$relatedProperty}s";

        return "
    /**
     * @return Collection<int, {$relatedClassName}>
     */
    public function get{$relatedClassName}s(): Collection
    {
        return \$this->{$collectionProperty};
    }

    public function add{$relatedClassName}({$relatedClassName} \${$relatedProperty}): self
    {
        if (!\$this->{$collectionProperty}->contains(\${$relatedProperty})) {
            \$this->{$collectionProperty}[] = \${$relatedProperty};
            \${$relatedProperty}->set" . ucfirst($propertyName) . "(\$this);
        }
        return \$this;
    }

    public function remove{$relatedClassName}({$relatedClassName} \${$relatedProperty}): self
    {
        if (\$this->{$collectionProperty}->removeElement(\${$relatedProperty})) {
            if (\${$relatedProperty}->get" . ucfirst($propertyName) . "() === \$this) {
                \${$relatedProperty}->set" . ucfirst($propertyName) . "(null);
            }
        }
        return \$this;
    }\n";
    }
}