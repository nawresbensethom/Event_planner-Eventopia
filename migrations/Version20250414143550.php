<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250414143550 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add statut column to signalement_post table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE service CHANGE description description LONGTEXT NOT NULL, CHANGE date_ajout date_ajout DATE NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service ADD CONSTRAINT FK_E19D9AD26B3CA4B FOREIGN KEY (id_user) REFERENCES utilisateur (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service ADD CONSTRAINT FK_E19D9AD24F295C67 FOREIGN KEY (id_categorie_service) REFERENCES categorieservice (id_categorie_service)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_service_user ON service
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E19D9AD26B3CA4B ON service (id_user)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_service_categorie ON service
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E19D9AD24F295C67 ON service (id_categorie_service)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_commentaire DROP FOREIGN KEY fk_signalement_commentaire_commentaire
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_commentaire DROP FOREIGN KEY fk_signalement_commentaire_utilisateur
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_commentaire DROP FOREIGN KEY FK_2CA6DCCE7FE2A54B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_commentaire DROP FOREIGN KEY fk_signalement_commentaire_commentaire
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_commentaire DROP FOREIGN KEY FK_2CA6DCCE50EAE44
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_commentaire DROP FOREIGN KEY fk_signalement_commentaire_utilisateur
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_commentaire CHANGE raison raison LONGTEXT NOT NULL, CHANGE date_signalement date_signalement DATETIME NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_signalement_commentaire_commentaire ON signalement_commentaire
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2CA6DCCE7FE2A54B ON signalement_commentaire (id_commentaire)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_signalement_commentaire_utilisateur ON signalement_commentaire
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2CA6DCCE50EAE44 ON signalement_commentaire (id_utilisateur)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_commentaire ADD CONSTRAINT FK_2CA6DCCE7FE2A54B FOREIGN KEY (id_commentaire) REFERENCES commentaire (id_commentaire) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_commentaire ADD CONSTRAINT fk_signalement_commentaire_commentaire FOREIGN KEY (id_commentaire) REFERENCES commentaire (id_commentaire) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_commentaire ADD CONSTRAINT FK_2CA6DCCE50EAE44 FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_commentaire ADD CONSTRAINT fk_signalement_commentaire_utilisateur FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_post DROP FOREIGN KEY fk_signalement_post_post
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_post DROP FOREIGN KEY fk_signalement_post_utilisateur
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_post DROP FOREIGN KEY fk_signalement_post_post
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_post DROP FOREIGN KEY fk_signalement_post_utilisateur
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_post ADD statut VARCHAR(20) NOT NULL, CHANGE raison raison LONGTEXT NOT NULL, CHANGE date_signalement date_signalement DATETIME NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_post ADD CONSTRAINT FK_523B5D51D1AA708F FOREIGN KEY (id_post) REFERENCES post (id_post) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_post ADD CONSTRAINT FK_523B5D5150EAE44 FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_signalement_post_post ON signalement_post
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_523B5D51D1AA708F ON signalement_post (id_post)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_signalement_post_utilisateur ON signalement_post
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_523B5D5150EAE44 ON signalement_post (id_utilisateur)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_post ADD CONSTRAINT fk_signalement_post_post FOREIGN KEY (id_post) REFERENCES post (id_post) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_post ADD CONSTRAINT fk_signalement_post_utilisateur FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tache DROP FOREIGN KEY FK_TACHE_ID_PLAN
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tache CHANGE description description LONGTEXT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX id_plan ON tache
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_93872075567A477F ON tache (id_plan)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tache ADD CONSTRAINT FK_TACHE_ID_PLAN FOREIGN KEY (id_plan) REFERENCES plan (id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX email ON utilisateur
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE utilisateur CHANGE privileges privileges LONGTEXT DEFAULT NULL, CHANGE numtel numtel INT NOT NULL
        SQL);
        $this->addSql('ALTER TABLE signalement_post ADD statut VARCHAR(20) NOT NULL DEFAULT "En attente"');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD26B3CA4B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD24F295C67
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD26B3CA4B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD24F295C67
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service CHANGE description description TEXT NOT NULL, CHANGE date_ajout date_ajout DATE DEFAULT CURRENT_DATE NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_e19d9ad26b3ca4b ON service
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fk_service_user ON service (id_user)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_e19d9ad24f295c67 ON service
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fk_service_categorie ON service (id_categorie_service)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service ADD CONSTRAINT FK_E19D9AD26B3CA4B FOREIGN KEY (id_user) REFERENCES utilisateur (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service ADD CONSTRAINT FK_E19D9AD24F295C67 FOREIGN KEY (id_categorie_service) REFERENCES categorieservice (id_categorie_service)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_commentaire DROP FOREIGN KEY FK_2CA6DCCE7FE2A54B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_commentaire DROP FOREIGN KEY FK_2CA6DCCE50EAE44
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_commentaire CHANGE raison raison TEXT NOT NULL, CHANGE date_signalement date_signalement DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_commentaire ADD CONSTRAINT fk_signalement_commentaire_commentaire FOREIGN KEY (id_commentaire) REFERENCES commentaire (id_commentaire) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_commentaire ADD CONSTRAINT fk_signalement_commentaire_utilisateur FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_2ca6dcce50eae44 ON signalement_commentaire
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fk_signalement_commentaire_utilisateur ON signalement_commentaire (id_utilisateur)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_2ca6dcce7fe2a54b ON signalement_commentaire
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fk_signalement_commentaire_commentaire ON signalement_commentaire (id_commentaire)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_commentaire ADD CONSTRAINT FK_2CA6DCCE7FE2A54B FOREIGN KEY (id_commentaire) REFERENCES commentaire (id_commentaire) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_commentaire ADD CONSTRAINT FK_2CA6DCCE50EAE44 FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_post DROP FOREIGN KEY FK_523B5D51D1AA708F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_post DROP FOREIGN KEY FK_523B5D5150EAE44
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_post DROP FOREIGN KEY FK_523B5D51D1AA708F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_post DROP FOREIGN KEY FK_523B5D5150EAE44
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_post DROP statut, CHANGE raison raison TEXT NOT NULL, CHANGE date_signalement date_signalement DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_post ADD CONSTRAINT fk_signalement_post_post FOREIGN KEY (id_post) REFERENCES post (id_post) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_post ADD CONSTRAINT fk_signalement_post_utilisateur FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_523b5d5150eae44 ON signalement_post
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fk_signalement_post_utilisateur ON signalement_post (id_utilisateur)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_523b5d51d1aa708f ON signalement_post
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fk_signalement_post_post ON signalement_post (id_post)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_post ADD CONSTRAINT FK_523B5D51D1AA708F FOREIGN KEY (id_post) REFERENCES post (id_post) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_post ADD CONSTRAINT FK_523B5D5150EAE44 FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tache DROP FOREIGN KEY FK_93872075567A477F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tache CHANGE description description TEXT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_93872075567a477f ON tache
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX id_plan ON tache (id_plan)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tache ADD CONSTRAINT FK_93872075567A477F FOREIGN KEY (id_plan) REFERENCES plan (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE utilisateur CHANGE privileges privileges LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_general_ci`, CHANGE numtel numtel INT DEFAULT 51756897 NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX email ON utilisateur (email)
        SQL);
        $this->addSql('ALTER TABLE signalement_post DROP statut');
    }
}
