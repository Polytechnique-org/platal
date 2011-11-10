#!/bin/bash

. ../inc/pervasive.sh

###########################################################
[ "$DATABASE" != "x4dat" ] || die "Cannot target x4dat"

confirm "* Running database upgrade scripts"
mysql_run_directory .
