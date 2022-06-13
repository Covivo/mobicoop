#!/bin/bash

########################################
#         Symfony log rotation         #
#  Must be launched in root crontab    #
#  as gzip doesn't allow setgid's :    #
#  we need to chmod 774 first...       #
########################################

SCRIPT_PATH=$(dirname $(realpath "$0"))

# API path
API_PATH="$SCRIPT_PATH/../api/var/log"

# Bundle path
BUNDLE_PATH="$SCRIPT_PATH/../client/var/log"

# Client path
CLIENT_PATH="$SCRIPT_PATH/../../var/log"

# Date of the current day
TODAY=$(date +"%Y-%m-%d")

for log_path in "${API_PATH}" "${BUNDLE_PATH}" "${CLIENT_PATH}"
do
  if [ -d "${log_path}" ]
  then
    for entry in "${log_path}"/*
    do
      if [[ $entry =~ [a-z]*-${TODAY}.log$ ]]
      then
        # Don't gz today's log
        continue
      elif [[ $entry =~ [a-z]*-[0-9]{4}-[0-9]{2}-[0-9]{2}.log$ ]]
      then
        # Gz log
        chmod 774 "$entry"
        gzip -9 "$entry"
      fi
    done
    # Delete old files
    find "${log_path}"/*.log.gz -mtime "+30" -delete
  fi
done
