#!/bin/bash
ln -sf ../../../banana/{img,locales,spool} .

pushd include &> /dev/null
ln -sf ../../../../banana/include/{encoding,groups,NetNNTP,post,spool,wrapper} .
popd &> /dev/null

chmod a+w spool
