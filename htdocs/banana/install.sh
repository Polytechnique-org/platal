#!/bin/bash
ln -sf ../../../banana/{img,spool} .

pushd include &> /dev/null
ln -sf ../../../../banana/include/{groups,NetNNTP,post,spool,banana}.inc.php .
popd &> /dev/null

ln -sf ../../../banana/xface.php .
