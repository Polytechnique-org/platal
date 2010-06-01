DELETE FROM profile_section_enum WHERE text = '';
ALTER TABLE profile_section_enum ADD UNIQUE KEY (text);

# vim:set syntax=mysql:
