-- Query to check if all the nationalities are now rocognized
-- If the result of this query is not empty, 04_nationalities.sql nedds to be updated
SELECT DISTINCT  Code_nationalite
           FROM  fusionax_anciens AS f
          WHERE  NOT EXISTS (SELECT  *
                               FROM  geoloc_pays AS g
                              WHERE  g.license_plate = f.Code_nationalite);


-- vim:set syntax=mysql:
