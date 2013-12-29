--
-- Add a field for every community letters
-- ax_letter is deprecated but keep it for now
--
ALTER TABLE register_pending CHANGE COLUMN services services SET('ax_letter', 'com_letters', 'imap', 'ml_promo', 'nl') NOT NULL DEFAULT '';

-- vim:set syntax=mysql:
