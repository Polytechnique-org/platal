ALTER TABLE group_events ADD COLUMN subscription_notification ENUM('nobody', 'creator', 'animator', 'both') NOT NULL DEFAULT 'nobody';

-- vim:set syntax=mysql:
