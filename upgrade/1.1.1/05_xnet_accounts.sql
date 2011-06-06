-- Disables non-activated xnet accounts.

   UPDATE  accounts              AS a
LEFT JOIN  register_pending_xnet AS r ON (r.uid = a.uid)
      SET  a.state = 'disabled'
    WHERE  a.password IS NULL AND a.type = 'xnet' AND r.hash IS NULL;

-- vim:set syntax=mysql:
