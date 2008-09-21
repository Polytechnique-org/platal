ALTER TABLE applis_ins MODIFY type enum('Corps','Ingénieur','MBA','ME','MS','PhD','Diplôme','DEA','MiF','MPA', 'MIA');

ALTER TABLE applis_def MODIFY type set('Corps','Ingénieur','MBA','ME','MS','PhD','Diplôme','DEA','MiF','MPA', 'MIA')

# vim:set syntax=mysql:

