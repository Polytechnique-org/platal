ALTER TABLE `requests_answers` MODIFY `category` enum('alias','liste','usage','photo','evts', 'gapps-unsuspend', 'marketing','orange','homonyme','nl','paiements','medal','broken','surveys') NOT NULL default 'alias';

# vim:set syntax=mysql:
