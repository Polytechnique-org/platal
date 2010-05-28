#!/bin/bash

CRONDIR=$(dirname $0)
PWD=$(pwd)
PLATAL_ROOT=${CRONDIR}/../..

# Executes a command and displays its output only if retcode != 0
# @param dir Folder from which the command should be called
# @param cmd The command to execute
# After execution, the pwd is returned to its initial value.
function print_if_err {
    local dir cmd retcode tmpfile
    dir=$1
    cmd=$2

    echo "* Running ${cmd}..."
    # Capture output and return code
    tmpfile=$(mktemp pl_run_tests.XXXXXX)
    cd ${dir} && ${cmd} 2>&1 > ${tmpfile}
    retcode=$?

    if [ "x${retcode}" != "x0" ]; then
        echo "** Error running command ${cmd} (code ${retcode})"
        cat ${tmpfile}
        rm -f ${tmpfile}
        exit ${retcode}
    fi

    # Cleanup
    rm -f ${tmpfile}
    cd ${PWD}
}

print_if_err ${PLATAL_ROOT} "make test"
