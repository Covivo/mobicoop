#!/bin/bash

########################################
#         Symfony log rotation         #
#  Must be launched in root crontab    #
#  as gzip doesn't allow setgid's :    #
#  we need to chmod 774 first...       #
########################################

SCRIPT_PATH=$(dirname $(realpath "$0"))

# API path
API_PATH="${SCRIPT_PATH}/../api/var/log"

# Bundle path
BUNDLE_PATH="${SCRIPT_PATH}/../client/var/log"

# Client path
CLIENT_PATH="${SCRIPT_PATH}/../../var/log"

# Date of the current day
TODAY=$(date +"%Y-%m-%d")

for log_path in "${API_PATH}" "${BUNDLE_PATH}" "${CLIENT_PATH}"
do
    if [ -d "${log_path}" ]
    then
        cd "${log_path}"
        # gzip the logs except those of the current day
        find . -maxdepth 1 \
               -regextype posix-extended \
               -regex "\./[a-z]+-[0-9]{4}-[0-9]{2}-[0-9]{2}\.log" \
               -and -not -regex "\./[a-z]+-${TODAY}\.log" \
               -exec chmod 774 '{}' \; -exec gzip -9 '{}' \;
        # Delete files older than 10 days
        find . -maxdepth 1 -name '*.log.gz' -and -mtime "+10" -delete
        cd -
    fi
done
