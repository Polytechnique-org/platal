#!/bin/bash

. ../inc/pervasive.sh

###########################################################
[ "$DATABASE" != "x4dat" ] || die "Cannot target x4dat"
copy_db

confirm "* Running database upgrade scripts"
mysql_run_directory .

confirm "* Running upgrade scripts"
script_run ./phone.php
script_run ./xnet_directory_name.php
script_run ./sectors_as_terms.php
script_run ./positions_as_terms.php
script_run ./tokenize_job_terms.php

confirm "* Running post-PHP database upgrade script"
mysql_run ./99_jobs.sql.postphp

confirm "* Upgrading search table (reindex user names for quick search)"
pushd ../../bin
./search.rebuild_db.php
popd
