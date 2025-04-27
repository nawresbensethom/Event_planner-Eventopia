<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250427012329 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migration pour mettre à jour les contraintes et les index des tables';
    }

    public function up(Schema $schema): void
    {
        // Désactiver les vérifications de clés étrangères
        $this->addSql('SET FOREIGN_KEY_CHECKS=0');

        // Nettoyage des données invalides
        $this->addSql(<<<'SQL'
            UPDATE code_promos SET service_id = NULL 
            WHERE service_id IS NOT NULL AND service_id NOT IN (SELECT id_service FROM service)
        SQL);

        // Nettoyage des emails en double
        $this->addSql(<<<'SQL'
            CREATE TEMPORARY TABLE temp_emails AS
            SELECT MIN(id) as keep_id, email
            FROM utilisateur
            GROUP BY email
            HAVING COUNT(*) > 1
        SQL);

        $this->addSql(<<<'SQL'
            DELETE u FROM utilisateur u
            INNER JOIN temp_emails t ON u.email = t.email
            WHERE u.id != t.keep_id
        SQL);

        $this->addSql('DROP TEMPORARY TABLE IF EXISTS temp_emails');

        // Modifications sur code_promos
        $this->addSql(<<<'SQL'
            ALTER TABLE code_promos DROP FOREIGN KEY IF EXISTS FK_4E57AFA7ED5CA9E6
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS fk_codepromos_service ON code_promos
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS IDX_4E57AFA7ED5CA9E6 ON code_promos
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4E57AFA7ED5CA9E6 ON code_promos (service_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE code_promos ADD CONSTRAINT FK_4E57AFA7ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id_service) ON DELETE SET NULL
        SQL);

        // Modifications sur offreemploi
        $this->addSql(<<<'SQL'
            ALTER TABLE offreemploi DROP FOREIGN KEY IF EXISTS offreemploi_ibfk_1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offreemploi DROP FOREIGN KEY IF EXISTS FK_A9B2BBFD6B3CA4B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offreemploi CHANGE description description LONGTEXT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS id_organisateur ON offreemploi
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS IDX_A9B2BBFD6B3CA4B ON offreemploi
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A9B2BBFD6B3CA4B ON offreemploi (id_user)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offreemploi ADD CONSTRAINT FK_A9B2BBFD6B3CA4B FOREIGN KEY (id_user) REFERENCES utilisateur (id)
        SQL);

        // Modifications sur candidature
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature 
            DROP FOREIGN KEY IF EXISTS fk_offre,
            DROP FOREIGN KEY IF EXISTS fk_offre_candidature,
            DROP FOREIGN KEY IF EXISTS fk_prestataire,
            DROP FOREIGN KEY IF EXISTS FK_E33BD3B84103C75F,
            DROP FOREIGN KEY IF EXISTS FK_E33BD3B878B0A977
        SQL);

        $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS fk_offre_candidature ON candidature
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS fk_prestataire ON candidature
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS IDX_E33BD3B84103C75F ON candidature
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS IDX_E33BD3B878B0A977 ON candidature
        SQL);

        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E33BD3B84103C75F ON candidature (id_offre)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E33BD3B878B0A977 ON candidature (id_prestataire)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE candidature 
            ADD CONSTRAINT FK_E33BD3B84103C75F FOREIGN KEY (id_offre) REFERENCES offreemploi (id_offre) ON DELETE CASCADE,
            ADD CONSTRAINT FK_E33BD3B878B0A977 FOREIGN KEY (id_prestataire) REFERENCES utilisateur (id) ON DELETE CASCADE
        SQL);

        // Modifications sur evenement
        $this->addSql(<<<'SQL'
            ALTER TABLE evenement DROP FOREIGN KEY IF EXISTS FK_B26681E68161836
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE evenement ADD CONSTRAINT FK_B26681E68161836 FOREIGN KEY (id_organisateur) REFERENCES utilisateur (id)
        SQL);

        // Modifications sur plan
        $this->addSql(<<<'SQL'
            ALTER TABLE plan CHANGE priorite priorite VARCHAR(255) NOT NULL
        SQL);

        // Modifications sur post
        $this->addSql(<<<'SQL'
            SELECT COUNT(*) INTO @like_count_exists
            FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'post' 
            AND COLUMN_NAME = 'like_count'
        SQL);

        $this->addSql(<<<'SQL'
            SET @add_columns = IF(@like_count_exists = 0,
                'ALTER TABLE post ADD like_count INT DEFAULT 0 NOT NULL, ADD dislike_count INT DEFAULT 0 NOT NULL',
                'SELECT 1'
            )
        SQL);

        $this->addSql('PREPARE stmt FROM @add_columns');
        $this->addSql('EXECUTE stmt');
        $this->addSql('DEALLOCATE PREPARE stmt');

        // Modifications sur profil
        $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS IDX_E6D6B297A76ED395 ON profil
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS UNIQ_E6D6B297A76ED395 ON profil
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_E6D6B297A76ED395 ON profil (user_id)
        SQL);

        // Modifications sur reservation
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP FOREIGN KEY IF EXISTS FK_42C849558B13D439
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP FOREIGN KEY IF EXISTS FK_42C84955E173B1B8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT FK_42C849558B13D439 FOREIGN KEY (id_evenement) REFERENCES evenement (id_evenement)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT FK_42C84955E173B1B8 FOREIGN KEY (id_client) REFERENCES utilisateur (id)
        SQL);

        // Modifications sur service
        $this->addSql(<<<'SQL'
            ALTER TABLE service CHANGE date_ajout date_ajout DATETIME NOT NULL
        SQL);

        // Modifications sur utilisateur
        $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS UNIQ_1D1C63B3E7927C74 ON utilisateur
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_1D1C63B3E7927C74 ON utilisateur (email)
        SQL);

        // Réactiver les vérifications de clés étrangères
        $this->addSql('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(Schema $schema): void
    {
        // Désactiver les vérifications de clés étrangères
        $this->addSql('SET FOREIGN_KEY_CHECKS=0');

        // Restauration utilisateur
        $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS UNIQ_1D1C63B3E7927C74 ON utilisateur
        SQL);

        // Restauration service
        $this->addSql(<<<'SQL'
            ALTER TABLE service CHANGE date_ajout date_ajout DATE NOT NULL
        SQL);

        // Restauration reservation
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP FOREIGN KEY IF EXISTS FK_42C849558B13D439
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP FOREIGN KEY IF EXISTS FK_42C84955E173B1B8
        SQL);

        // Restauration profil
        $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS UNIQ_E6D6B297A76ED395 ON profil
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E6D6B297A76ED395 ON profil (user_id)
        SQL);

        // Restauration post
        $this->addSql(<<<'SQL'
            ALTER TABLE post DROP COLUMN IF EXISTS like_count, DROP COLUMN IF EXISTS dislike_count
        SQL);

        // Restauration plan
        $this->addSql(<<<'SQL'
            ALTER TABLE plan CHANGE priorite priorite VARCHAR(10) NOT NULL
        SQL);

        // Restauration offreemploi
        $this->addSql(<<<'SQL'
            ALTER TABLE offreemploi DROP FOREIGN KEY IF EXISTS FK_A9B2BBFD6B3CA4B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offreemploi CHANGE description description TEXT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS IDX_A9B2BBFD6B3CA4B ON offreemploi
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX id_organisateur ON offreemploi (id_user)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offreemploi ADD CONSTRAINT offreemploi_ibfk_1 FOREIGN KEY (id_user) REFERENCES utilisateur (id)
        SQL);

        // Restauration evenement
        $this->addSql(<<<'SQL'
            ALTER TABLE evenement DROP FOREIGN KEY IF EXISTS FK_B26681E68161836
        SQL);

        // Restauration code_promos
        $this->addSql(<<<'SQL'
            ALTER TABLE code_promos DROP FOREIGN KEY IF EXISTS FK_4E57AFA7ED5CA9E6
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS IDX_4E57AFA7ED5CA9E6 ON code_promos
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fk_codepromos_service ON code_promos (service_id)
        SQL);

        // Restauration candidature
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature 
            DROP FOREIGN KEY IF EXISTS FK_E33BD3B84103C75F,
            DROP FOREIGN KEY IF EXISTS FK_E33BD3B878B0A977
        SQL);

        $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS IDX_E33BD3B84103C75F ON candidature
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IF EXISTS IDX_E33BD3B878B0A977 ON candidature
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE candidature 
            ADD CONSTRAINT fk_offre FOREIGN KEY (id_offre) REFERENCES offreemploi (id_offre) ON UPDATE CASCADE ON DELETE CASCADE,
            ADD CONSTRAINT fk_prestataire FOREIGN KEY (id_prestataire) REFERENCES utilisateur (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);

        $this->addSql(<<<'SQL'
            CREATE INDEX fk_offre_candidature ON candidature (id_offre)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fk_prestataire ON candidature (id_prestataire)
        SQL);

        // Réactiver les vérifications de clés étrangères
        $this->addSql('SET FOREIGN_KEY_CHECKS=1');
    }
}

