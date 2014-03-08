UPDATE profiles as p
LEFT JOIN account_profiles AS ap ON p.pid = ap.pid
LEFT JOIN accounts AS a ON a.uid = ap.uid
SET p.birthdate = p.birthdate_ref
WHERE p.birthdate_ref AND NOT p.birthdate AND a.state = 'active';
