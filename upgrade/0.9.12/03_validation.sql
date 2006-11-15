ALTER TABLE requests_answers CHANGE category category enum('alias','liste','usage','photo','evts','marketing','orange','homonyme','nl','paiements','medal') NOT NULL DEFAULT 'alias';
