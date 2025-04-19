SET FOREIGN_KEY_CHECKS=0;
ALTER TABLE signalement_commentaire DROP FOREIGN KEY IF EXISTS fk_signalement_commentaire_commentaire;
ALTER TABLE signalement_commentaire DROP FOREIGN KEY IF EXISTS fk_signalement_commentaire_utilisateur;
ALTER TABLE signalement_post DROP FOREIGN KEY IF EXISTS fk_signalement_post_post;
ALTER TABLE signalement_post DROP FOREIGN KEY IF EXISTS fk_signalement_post_utilisateur;
SET FOREIGN_KEY_CHECKS=1; 