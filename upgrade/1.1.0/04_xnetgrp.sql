ALTER TABLE groups ADD COLUMN axDate DATE DEFAULT NULL;
ALTER TABLE group_members ADD COLUMN position ENUM('Président', 'Vice-président', 'Trésorier', 'Vice-trésorier', 'Secrétaire', 'Directeur', 'Délégué général', 'Secrétaire général', 'Membre du bureau') DEFAULT NULL;
ALTER TABLE groups ADD COLUMN welcome_msg MEDIUMTEXT DEFAULT NULL;

-- vim:set syntax=mysql:
