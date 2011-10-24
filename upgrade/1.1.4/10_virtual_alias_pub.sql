UPDATE profiles SET alias_pub = 'hidden' WHERE alias_pub = 'private';
UPDATE profiles SET alias_pub = 'private' WHERE alias_pub = 'public';

-- vim:set syntax=mysql:
