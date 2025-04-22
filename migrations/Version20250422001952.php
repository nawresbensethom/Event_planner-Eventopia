<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250422001952 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP FOREIGN KEY fk_offre
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP FOREIGN KEY fk_offre_candidature
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP FOREIGN KEY fk_prestataire
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP FOREIGN KEY fk_offre
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP FOREIGN KEY fk_offre_candidature
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP FOREIGN KEY fk_prestataire
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B84103C75F FOREIGN KEY (id_offre) REFERENCES offreemploi (id_offre) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B878B0A977 FOREIGN KEY (id_prestataire) REFERENCES utilisateur (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_offre_candidature ON candidature
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E33BD3B84103C75F ON candidature (id_offre)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_prestataire ON candidature
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E33BD3B878B0A977 ON candidature (id_prestataire)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT fk_offre FOREIGN KEY (id_offre) REFERENCES offreemploi (id_offre) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT fk_offre_candidature FOREIGN KEY (id_offre) REFERENCES offreemploi (id_offre) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT fk_prestataire FOREIGN KEY (id_prestataire) REFERENCES utilisateur (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE code_promos ADD CONSTRAINT FK_4E57AFA7ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id_service) ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX fk_codepromos_service ON code_promos
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4E57AFA7ED5CA9E6 ON code_promos (service_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE evenement ADD CONSTRAINT FK_B26681E68161836 FOREIGN KEY (id_organisateur) REFERENCES utilisateur (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offreemploi DROP FOREIGN KEY offreemploi_ibfk_1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offreemploi CHANGE description description LONGTEXT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX id_organisateur ON offreemploi
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A9B2BBFD6B3CA4B ON offreemploi (id_user)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offreemploi ADD CONSTRAINT offreemploi_ibfk_1 FOREIGN KEY (id_user) REFERENCES utilisateur (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plan CHANGE priorite priorite VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post ADD like_count INT DEFAULT 0 NOT NULL, ADD dislike_count INT DEFAULT 0 NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE profil DROP INDEX IDX_E6D6B297A76ED395, ADD UNIQUE INDEX UNIQ_E6D6B297A76ED395 (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT FK_42C849558B13D439 FOREIGN KEY (id_evenement) REFERENCES evenement (id_evenement)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD CONSTRAINT FK_42C84955E173B1B8 FOREIGN KEY (id_client) REFERENCES utilisateur (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service CHANGE date_ajout date_ajout DATETIME NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_1D1C63B3E7927C74 ON utilisateur (email)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B84103C75F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B878B0A977
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B84103C75F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B878B0A977
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT fk_offre FOREIGN KEY (id_offre) REFERENCES offreemploi (id_offre) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT fk_offre_candidature FOREIGN KEY (id_offre) REFERENCES offreemploi (id_offre) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT fk_prestataire FOREIGN KEY (id_prestataire) REFERENCES utilisateur (id) ON UPDATE CASCADE ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_e33bd3b878b0a977 ON candidature
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fk_prestataire ON candidature (id_prestataire)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_e33bd3b84103c75f ON candidature
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fk_offre_candidature ON candidature (id_offre)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B84103C75F FOREIGN KEY (id_offre) REFERENCES offreemploi (id_offre) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B878B0A977 FOREIGN KEY (id_prestataire) REFERENCES utilisateur (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE code_promos DROP FOREIGN KEY FK_4E57AFA7ED5CA9E6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE code_promos DROP FOREIGN KEY FK_4E57AFA7ED5CA9E6
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_4e57afa7ed5ca9e6 ON code_promos
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX fk_codepromos_service ON code_promos (service_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE code_promos ADD CONSTRAINT FK_4E57AFA7ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id_service) ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE evenement DROP FOREIGN KEY FK_B26681E68161836
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offreemploi DROP FOREIGN KEY FK_A9B2BBFD6B3CA4B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offreemploi CHANGE description description TEXT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_a9b2bbfd6b3ca4b ON offreemploi
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX id_organisateur ON offreemploi (id_user)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offreemploi ADD CONSTRAINT FK_A9B2BBFD6B3CA4B FOREIGN KEY (id_user) REFERENCES utilisateur (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plan CHANGE priorite priorite VARCHAR(10) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post DROP like_count, DROP dislike_count
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE profil DROP INDEX UNIQ_E6D6B297A76ED395, ADD INDEX IDX_E6D6B297A76ED395 (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP FOREIGN KEY FK_42C849558B13D439
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955E173B1B8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service CHANGE date_ajout date_ajout DATE NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_1D1C63B3E7927C74 ON utilisateur
        SQL);
    }
}
