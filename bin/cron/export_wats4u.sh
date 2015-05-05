#!/bin/bash
set -e  # Fail on errors

# Hardcoded paths
DUMP_SCRIPT=../dump_wats4u.php
SSH_PASSWORD_FILE=/etc/xorg/wats4u.sftp.pass
SSH_USER=$(head -n 1 /etc/xorg/wats4u.sftp.user)
SSH_PORT=$(head -n 1 /etc/xorg/wats4u.sftp.port || echo "22")
SSH_HOST=$(head -n 1 /etc/xorg/wats4u.sftp.host)

# Local directories
TMPDIR=$(mktemp -d)
DATE=$(date +'%Y-%m-%d')

# Computed paths
CSVFILE="${TMPDIR}/extract_polytechnique_${DATE}.csv"
MD5FILE="${TMPDIR}/extract_polytechnique_${DATE}.csv.md5"

# Generate the dump
$DUMP_SCRIPT > ${CSVFILE}
# Compute md5
md5sum "${CSVFILE}" | sed 's/ .*//' > "${MD5FILE}"
# Upload files (we need to use login/password).
sshpass "-f${SSH_PASSWORD_FILE}" scp -P "${SSH_PORT}" "${CSVFILE}" "${MD5FILE}" "${SSH_USER}@${SSH_HOST}:"

rm "${CSVFILE}" "${MD5FILE}"
