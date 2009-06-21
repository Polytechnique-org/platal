DROP TABLE IF EXISTS axletter_subsets;

CREATE TABLE IF NOT EXISTS axletter_subsets (
    letter_id INT(11) UNSIGNED NOT NULL,
    uid INT(11) NOT NULL,
    email VARCHAR(255) NOT NULL
)

ALTER TABLE axletter ADD subset smallint(1) NOT NULL DEFAULT 0 AFTER promo_max

-- vim:set syntax=mysql:

