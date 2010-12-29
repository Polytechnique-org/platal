ALTER TABLE geoloc_subadministrativeareas ADD COLUMN administrativearea VARCHAR(255) DEFAULT NULL;

    UPDATE  geoloc_subadministrativeareas AS gs
INNER JOIN  profile_addresses             AS pa ON (gs.id = pa.subAdministrativeAreaId)
       SET  gs.administrativearea = pa.administrativeAreaId;

-- vim:set syntax=mysql:
