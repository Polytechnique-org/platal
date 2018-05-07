Email routing tables
====================

The email routing tables of plat/al are somewhat complicated.


Virtual domains
---------------

Postfix needs to know which domains are 'virtual', i.e only redirections to
other addresses.

That list is kept under `email_virtual_domains`.
In order to fetch the list, postfix only looks at the `name` column.


"Source" emails
---------------

Plat/al handles various types of "source" emails (source means "going into
our system"):

- User accounts (in `email_source_accounts`)
- Homonyms (`email_source_other` - ambiguous email in one of our top-level domains)
- Honeypots (`email_source_other` - should feed directly to the "spam" queue in bogo)
- Partner or admin emails (`email_virtual`, types 'admin' & 'partner')
- Group aliases (`email_virtual`, type 'alias')
- Group lists (`email_virtual`, type 'list')
- Events aliases (`email_virtual`, type 'event')

The main fields of those tables are:

- `email_virtual`: `email` (the *mbox* part), `domain` (Foreign Key to `email_virtual_domains.id`),
  `redirect` (the real destination email)
- `email_source_other`: `email` (the *mbox* part), `domain` (FK to `email_virtual_domains.id`),
  `hrmid` (the internal ID for that email - *Human Readable Milter IDentifier*)


The handling of the messages depends on the table they are defined in:

- For an email defined in `email_virtual`, pretty simple: lookup the mbox in `email_virtual.email`
  and the domain in the directly related `email_virtual_domains.name`, and send it to
  `email_virtual.redirect`:

  .. code-block:: sql

        SELECT  ev.redirect
          FROM  email_virtual AS ev
          JOIN  email_virtual_domains AS evd ON (ev.domain = evd.id)
         WHERE  ev.email = {mbox} AND evd.name = {domain}


- For an email defined in `email_source_other` or `email_source_accounts`, we need to include
  our domain aliasing policy: `louis.vaneau@m4x.org` is actually an alias for
  `louis.vaneau.1829@polytechnique.org`.

  With those emails, the domain referenced in `email_source_accounts.domain` / `email_source_other.domain`
  is the id of the **canonical** domain for that email - and thus actually references the `aliasing` column
  in `email_virtual_domains`.

  In order to find the canonical email for such lines, we take two steps:

  1. Find a line in `email_source_accounts` where the `mbox` matches and references **any**
     line in `email_virtual_domains` with the same `aliasing` as the canonical domain for that `mbox`:

  .. code-block:: sql

        SELECT  esa.uid
          FROM  email_source_accounts AS esa
                                                -- Note the lookup on **evd.aliasing**
          JOIN  email_virtual_domains AS evd ON (esa.domain = evd.aliasing)
         WHERE  esa.email = {mbox} AND evd.name = {domain}


  2. Compute the canonical version of the email:

  .. code-block:: sql

        SELECT  CONCAT(esa.email, '@', evd.name)
          FROM  email_source_accounts AS esa
                                             -- Note: forward lookup => use .id
          JOIN  email_virtual_domains AS evd ON (esa.domain = evd.id)
         WHERE  esa.uid = {uid}


Filling `email_virtual_domains`
-------------------------------

How should one handle the `email_virtual_domains` table?

- For group domains, pretty simply: insert a new line, and set `aliasing` to be the same as the `id` (it could also be NULL)
- For user domains, it's more complicated:

  1. Add a line for the **canonical** domain for those users (e.g. `masters.p.org`), with `aliasing` matching `id`
  2. For each domain where aliases for those users should be provided, add a new line, with `aliasing` matching the
     `id` of the canonical domain.

.. warning:: When looking up the ID of the **canonical** version of a domain, narrow your search with `id = aliasing`.
             Otherwise, you might select a line used to describe that same domain as an alias for another canonical domain;
             e.g ``alumni.polytechnique.org`` is both an *alias* for ``masters.polytechnique.org`` **AND** the *canonical*
             domain for bachelors.
