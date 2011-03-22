#!/bin/bash

. ../inc/pervasive.sh

###########################################################
[ "$DATABASE" != "x4dat" ] || die "Cannot target x4dat"
copy_db

confirm "* Running database upgrade scripts"
mysql_run_directory .

confirm "* Running upgrade scripts"
script_run ./best_domain.php
script_run ./languages.php
