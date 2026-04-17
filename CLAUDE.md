CLAUDE.md
=========

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

Commands
--------

``` bash
make check          # PHP syntax check across all .php files
make test           # Run unit tests (delegates to core submodule)
make build          # Full build: core, conf, static assets, banana, wiki, jquery, maps, raven
make conf           # Create spool directories and compile config files only
make doc            # Generate Doxygen documentation
```

The mailing list RPC server (needed for list management features):

``` bash
make start-listrpc  # Start bin/lists.rpc.py as 'list' user (background)
make stop-listrpc
make restart-listrpc
```

Architecture overview
---------------------

Platal is a custom PHP MVC framework for an alumni community platform (Polytechnique.org). There are two entry points in `htdocs/`: `xorg.php` (main site) and `xnet.php` (groups/networks site). Both set a few constants then call `core/`'s `run.inc.php`, which drives the request lifecycle.

**Routing:** Apache rewrites all URLs to `?n=<path>`. The core dispatcher matches the path against handler tables defined in each module. A module is a class extending `PLModule` that implements `handlers()`, returning a map of URL patterns to `make_hook()` calls. Each hook specifies the handler method, the minimum auth level (`AUTH_PUBLIC`, `AUTH_COOKIE`, `AUTH_PASSWD`, `NO_AUTH`), and optional permission flags (e.g. `'admin'`).

Example:

``` php
class AdminModule extends PLModule {
    function handlers() {
        return array(
            'admin/user' => $this->make_hook('user', AUTH_PASSWD, 'admin'),
        );
    }
    function handler_user($page, ...) { ... }
}
```

Modules live in `modules/`. The active module list is configured in `configs/platal.ini`.

**Autoloading:** `include/common.inc.php` registers a custom autoloader that handles several naming conventions: `ufc_*`/`ufo_*` for user filter classes, `pfc_*`/`pfo_*` for profile filter classes, `de_*` for directory enumerations, `*validate*` for validation classes, and the general `$cls.inc.php` fallback.

**Key classes:**

-   `classes/User.php` -- account + session logic
-   `classes/Profile.php` -- profile data access
-   `classes/UserFilter.php` -- composable user/profile search (via `ufc_*` filter components)
-   `classes/Visibility.php` -- privacy/visibility level system for profile fields
-   `classes/Group.php` + `classes/Group/Direnum.php` -- group and directory enumeration
-   `classes/PlatalLogger.php` -- logging

**Database:** MySQL. The `accounts` table holds authentication data and `hruid`. The `profiles` table holds profile data (birthdate, etc.). They are linked through `account_profiles` (with a `perms` field; filter on `FIND_IN_SET('owner', perms)` for the primary account). Incremental migrations live in `upgrade/<version>/`.

**Templates:** Smarty, with custom plugins in `plugins/`. Template files are in `templates/<module>/`. Compiled templates go to `spool/templates_c/` (generated, not committed).

**Configuration:** `configs/platal.ini` is the committed base config. `configs/platal.conf` is a local override file (gitignored). The generated PHP globals class is `classes/platalglobals.php` (built from `classes/PlatalGlobals.php.in`).

**Git submodules:** `core/` (framework core), `banana/` (forum system), `include/raven/` (error tracking).

Database conventions
--------------------

The main join pattern linking an account to its profile:

``` sql
JOIN account_profiles AS ap ON ap.uid = a.uid AND FIND_IN_SET('owner', ap.perms)
JOIN profiles AS p ON p.pid = ap.pid
```

Database migrations are plain SQL files dropped in `upgrade/<version>/` and run in order. There is no migration runner in this repo -- migrations are applied manually or by deployment scripts.
