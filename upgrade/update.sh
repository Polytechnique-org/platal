#!/usr/bin/env bash

spoolroot="$(readlink -f $(dirname $0)/..)"
dbversionfile="$spoolroot/spool/tmp/db_version"

currentversion="$(grep 'VERSION' $spoolroot/ChangeLog | cut -d ' ' -f 2 | head -n 1)"
previousversion="$(cat $dbversionfile 2> /dev/null)"

function die() {
  echo "$1" 1>&2
  exit 1
}

if [ "$currentversion" != "$previousversion" ]; then
  cd $spoolroot/upgrade/$currentversion/
  ./update.sh "$@" || die "Upgrade to $currentversion failed"
  echo "$currentversion" > $dbversionfile
else
  echo "Already at version $currentversion"
fi
