#!/bin/bash
ln -sf ../../../banana/{img,spool} .

pushd include &> /dev/null
ln -sf ../../../../banana/include/{encoding,groups,NetNNTP,post,spool,wrapper}.inc.php .
popd &> /dev/null
