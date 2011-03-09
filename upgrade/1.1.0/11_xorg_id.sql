ALTER TABLE profiles DROP KEY xorg_id;
ALTER TABLE profiles ADD KEY xorg_id (xorg_id);

-- vim:set syntax=mysql:
