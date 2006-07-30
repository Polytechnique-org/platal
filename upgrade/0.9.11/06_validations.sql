CREATE TABLE `requests_answers` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`category` ENUM( 'alias', 'list', 'nomusage', 'photo', 'evt', 'marketing', 'orange', 'homonyme', 'nl', 'paiement' ) NOT NULL ,
`title` VARCHAR( 50 ) NOT NULL ,
`answer` TEXT NOT NULL
);
