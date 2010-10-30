UPDATE email_redirect_account AS e,
       (SELECT IF( SUM(IF(type!='imap',1,0))=0, 'imap_only', 'normal' ) AS status, uid
            FROM email_redirect_account
           WHERE flags = 'active'
        GROUP BY uid) AS sub
    SET e.action='imap_and_bounce'
  WHERE sub.status='imap_only'
    AND sub.uid = e.uid
    AND type='imap';

