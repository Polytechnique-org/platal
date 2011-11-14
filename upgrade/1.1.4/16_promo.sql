-- Updates promo for oranjes who only stated their orajisation to AX.
-- WARNING: this does not affect the display of their promotion anywhere. It will just allow them to attend events to both promotions.
-- Also they will not need to require a validation if they want to change promo on the site.

UPDATE  profile_education
   SET  grad_year = grad_year + 1
   WHERE  FIND_IN_SET('primary', flags) AND pid IN (41342, 41581, 41670, 42220, 43438, 43529, 43699);

-- Same as before but double orajisation.
UPDATE  profile_education
   SET  grad_year = grad_year + 2
 WHERE  FIND_IN_SET('primary', flags) AND pid IN (41441, 42307);

-- vim:set syntax=mysql:;
