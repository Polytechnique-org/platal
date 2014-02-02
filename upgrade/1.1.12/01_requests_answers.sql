ALTER TABLE requests_answers MODIFY COLUMN category ENUM('account', 'address', 'alias', 'broken', 'bulkaccounts', 'community-letter', 'entreprise', 'evts', 'gapps-unsuspend', 'homonyme', 'liste', 'marketing', 'medal', 'nl', 'orange', 'paiements', 'photo', 'surveys', 'usage') NOT NULL DEFAULT 'alias';

-- vim:set syntax=mysql:
