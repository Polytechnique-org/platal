#!/bin/bash
ln -sf ../../../banana/{img,locales,scripts} .

pushd include &> /dev/null
ln -sf ../../../../banana/include/*.php .
popd &> /dev/null

chmod a+w spool
