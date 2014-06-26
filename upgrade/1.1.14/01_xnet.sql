ALTER TABLE group_members MODIFY COLUMN position ENUM('Président', 'Vice-président', 'Trésorier', 'Vice-trésorier', 'Secrétaire', 'Directeur', 'Délégué général', 'Secrétaire général', 'Membre du bureau', 'Cotisant', 'Membre du CA') DEFAULT NULL;

-- vim:set syntax=mysql:
