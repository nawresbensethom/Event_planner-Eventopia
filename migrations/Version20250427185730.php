<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250427185730 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', expires_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B84103C75F FOREIGN KEY (id_offre) REFERENCES offreemploi (id_offre) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B878B0A977 FOREIGN KEY (id_prestataire) REFERENCES utilisateur (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E33BD3B84103C75F ON candidature (id_offre)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E33BD3B878B0A977 ON candidature (id_prestataire)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE code_promos ADD CONSTRAINT FK_4E57AFA7ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id_service) ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4E57AFA7ED5CA9E6 ON code_promos (service_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCD1AA708F FOREIGN KEY (id_post) REFERENCES post (id_post) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC50EAE44 FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_67F068BCD1AA708F ON commentaire (id_post)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_67F068BC50EAE44 ON commentaire (id_utilisateur)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE evenement CHANGE date_evenement date_evenement DATETIME DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE evenement ADD CONSTRAINT FK_B26681E68161836 FOREIGN KEY (id_organisateur) REFERENCES utilisateur (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_B26681E68161836 ON evenement (id_organisateur)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offreemploi CHANGE salaire salaire NUMERIC(10, 2) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plan CHANGE priorite priorite VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post DROP FOREIGN KEY post_ibfk_1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post CHANGE date_publication date_publication DATETIME NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D50EAE44 FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD service_ids JSON NOT NULL COMMENT '(DC2Type:json)', DROP services_ids
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service CHANGE tarif tarif NUMERIC(10, 2) NOT NULL, CHANGE date_ajout date_ajout DATETIME NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_commentaire DROP FOREIGN KEY FK_2CA6DCCE7FE2A54B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_commentaire DROP FOREIGN KEY FK_2CA6DCCE50EAE44
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_commentaire ADD CONSTRAINT FK_2CA6DCCE7FE2A54B FOREIGN KEY (id_commentaire) REFERENCES commentaire (id_commentaire) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_commentaire ADD CONSTRAINT FK_2CA6DCCE50EAE44 FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_post DROP FOREIGN KEY FK_523B5D5150EAE44
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_post DROP FOREIGN KEY FK_523B5D51D1AA708F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_post ADD statut VARCHAR(20) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_post ADD CONSTRAINT FK_523B5D5150EAE44 FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_post ADD CONSTRAINT FK_523B5D51D1AA708F FOREIGN KEY (id_post) REFERENCES post (id_post) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, headers LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, queue_name VARCHAR(190) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E016BA31DB (delivered_at), INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reset_password_request
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B84103C75F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B878B0A977
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_E33BD3B84103C75F ON candidature
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_E33BD3B878B0A977 ON candidature
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE code_promos DROP FOREIGN KEY FK_4E57AFA7ED5CA9E6
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_4E57AFA7ED5CA9E6 ON code_promos
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCD1AA708F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC50EAE44
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_67F068BCD1AA708F ON commentaire
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_67F068BC50EAE44 ON commentaire
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE evenement DROP FOREIGN KEY FK_B26681E68161836
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_B26681E68161836 ON evenement
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE evenement CHANGE date_evenement date_evenement DATETIME NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offreemploi CHANGE salaire salaire NUMERIC(10, 0) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE plan CHANGE priorite priorite VARCHAR(10) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D50EAE44
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post CHANGE date_publication date_publication DATETIME DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post ADD CONSTRAINT post_ibfk_1 FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation ADD services_ids LONGTEXT DEFAULT NULL, DROP service_ids
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE service CHANGE tarif tarif NUMERIC(10, 0) NOT NULL, CHANGE date_ajout date_ajout DATE NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_commentaire DROP FOREIGN KEY FK_2CA6DCCE7FE2A54B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_commentaire DROP FOREIGN KEY FK_2CA6DCCE50EAE44
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_commentaire ADD CONSTRAINT FK_2CA6DCCE7FE2A54B FOREIGN KEY (id_commentaire) REFERENCES commentaire (id_commentaire)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_commentaire ADD CONSTRAINT FK_2CA6DCCE50EAE44 FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_post DROP FOREIGN KEY FK_523B5D51D1AA708F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_post DROP FOREIGN KEY FK_523B5D5150EAE44
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_post DROP statut
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_post ADD CONSTRAINT FK_523B5D51D1AA708F FOREIGN KEY (id_post) REFERENCES post (id_post)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE signalement_post ADD CONSTRAINT FK_523B5D5150EAE44 FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id)
        SQL);
    }
}
