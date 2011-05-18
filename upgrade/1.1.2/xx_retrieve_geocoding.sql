    UPDATE  profile_addresses     AS pa
INNER JOIN  tmp_profile_addresses AS ta ON (pa.pid = ta.pid AND pa.jobid = ta.jobid AND pa.groupid = ta.groupid AND pa.type = ta.type AND pa.text = ta.text)
       SET  pa.postalText = ta.postalText, pa.formatted_address = ta.formatted_address, pa.types = ta.types,
            pa.latitude = ta.latitude, pa.longitude = ta.longitude, pa.southwest_latitude = ta.southwest_latitude,
            pa.southwest_longitude = ta.southwest_longitude, pa.northeast_latitude = ta.northeast_latitude, pa.northeast_longitude = ta.northeast_longitude,
            pa.location_type = ta.location_type, pa.partial_match = ta.partial_match,
            pa.geocoding_date = ta.geocoding_date, pa.geocoding_calls = ta.geocoding_calls;

DROP TABLE tmp_profile_addresses;

-- vim:set syntax=mysql:
