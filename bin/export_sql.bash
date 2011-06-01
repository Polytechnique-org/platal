#!/bin/bash

# This script is used to export the MySQL structure of the plat/al MySQL
# databases.


# Developers: list 'public' tables here.
SHARED_TABLES="account_types \
forums \
geoloc_countries \
geoloc_languages \
log_actions \
newsletter_cat \
profile_binet_enum \
profile_corps_enum \
profile_corps_rank_enum \
profile_education_degree \
profile_education_degree_enum \
profile_education_enum \
profile_education_field_enum \
profile_job_entreprise_term \
profile_job_enum \
profile_job_term_enum \
profile_job_term_relation \
profile_langskill_enum \
profile_medal_enum \
profile_medal_grade_enum \
profile_networking_enum \
profile_section_enum \
profile_skill_enum \
reminder_type \
skins"

usage()
{
cat << EOF
usage: $0 OPTIONS

Will dump the necessary data from MySQL.
Note that all options should have sane defaults if you have correctly configured
your .my.cnf file.

OPTIONS:
-h          Show this message
-H HOST     MySQL host to connect to (default: none)
-P PORT     MySQL port to connect to (default: none)
-u USER     User for MySQL (default: none)
-p PASS     MySQL password to use (default: none)
-d SQL_DB   Database to read from (default: x5dat)
-n          Dry run, don't actually write anything
-o FILE     Write to file instead of stdout (- for stdout as well)

EOF
}

HOST=
PASS=
PORT=
USER=
SQL_DB="x5dat"
FILE="-"
DRY_RUN=0

while getopts "hns:u:p:o:" OPTION
do
    case $OPTION in
      h)
        usage
        exit 1
        ;;
      n)
        DRY_RUN=1
        ;;
      o)
        FILE=$OPTARG
        ;;
      p)
        PASS=$OPTARG
        ;;
      H)
        HOST=$OPTARG
        ;;
      P)
        PORT=$OPTARG
        ;;
      u)
        USER=$OPTARG
        ;;
      d)
        SQL_DB=$OPTARG
        ;;
      ?)
        usage
        exit
        ;;
    esac
done

FILTER="sed -r s/AUTO_INCREMENT=[1-9]+/AUTO_INCREMENT=1/"
DUMPER="mysqldump --add-drop-table --default-character-set=utf8 --force"

if [ -n "$USER" ]; then
    DUMPER="$DUMPER --user=$USER"
fi

if [ -n "$PASS" ]; then
    DUMPER="$DUMPER --password=$PASS"
fi

if [ -n "$HOST" ]; then
    DUMPER="$DUMPER --host=$HOST"
fi

if [ -n "$PORT" ]; then
    DUMPER="$DUMPER --port=$PORT"
fi

if [ "$FILE" == "-" ]; then
    FILE=""
else
    if [ $DRY_RUN -ne 1 ]; then
        # Blank the file
        echo "" > $FILE
    fi
fi

dump () {
    local command=$1

    if [ $DRY_RUN -eq 1 ]; then
        if [ -n "$FILE" ]; then
            echo "$command | $FILTER >> $FILE"
        else
            echo "$command | $FILTER"
        fi
    else
        if [ -n "$FILE" ]; then
            $command | $FILTER >> $FILE
        else
            $command | $FILTER
        fi
    fi
}

# Dump structure
STRUCT_DUMPER="$DUMPER --no-data $SQL_DB"
dump "$STRUCT_DUMPER"

SHARED_DUMPER="$DUMPER $SQL_DB $SHARED_TABLES"
dump "$SHARED_DUMPER"
