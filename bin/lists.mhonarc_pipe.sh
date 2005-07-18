#! /bin/sh

SPOOL=/var/spool/platal/archives/
OUTDIR="${SPOOL}/$1/`date +'%Y/%m/'`"
RCFILE=../install.d/lists/platal.mrc

[ -d "$OUTDIR" ] || mkdir -p "$OUTDIR"
exec mhonarc -add -outdir "$OUTDIR" -rcfile "$RCFILE"
