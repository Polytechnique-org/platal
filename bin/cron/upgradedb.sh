#!/bin/bash

XDB=x5dat

CRONDIR=$(dirname $0)
SCRIPTPWD=$(pwd)
PLATAL_ROOT=${CRONDIR}/../..

VERSION=$(grep VERSION ${PLATAL_ROOT}/ChangeLog | head -1 | sed -e "s/VERSION //;s/ .*//");

UPDATEDIR=${PLATAL_ROOT}/upgrade/${VERSION}
UPDATESCRIPT=${UPDATEDIR}/update.sh

echo "Running update script for version ${VERSION}";

if [[ -x ${UPDATESCRIPT} ]]; then
    cd ${UPDATEDIR} && NO_CONFIRM=1 DATABASE=${XDB} ./update.sh -u admin
else
    echo "The update script ${UPDATESCRIPT} doesn't exist, aborting."
fi

cd ${SCRIPTPWD}
${CRONDIR}/run_tests.sh
