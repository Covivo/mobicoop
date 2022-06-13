#!/bin/bash

########################################
#         Symfony log rotation         #
#  Must be launched in root crontab    #
#  as gzip doesn't allow setgid's :    #
#  we need to chmod 774 first...       #
########################################

SCRIPT_PATH="$( cd "$(dirname "$0")" >/dev/null 2>&1 ; pwd -P )"

# API path
API_PATH="$SCRIPT_PATH/../api/var/log"

# Bundle path
BUNDLE_PATH="$SCRIPT_PATH/../client/var/log"

# Client path
CLIENT_PATH="$SCRIPT_PATH/../../var/log"

# Date and time
DATE=$(date +"%Y-%m-%d")

# Retention days (files older than retention days are removed)
RETENTION=30

logRegexToday="[a-z]*-$DATE.log$"
logRegexOther="[a-z]*-[0-9]{4}-[0-9]{2}-[0-9]{2}.log$"

# Gz log files for api
if [ -d "$API_PATH" ]; then
  for entry in "$API_PATH"/*
    do
      if [[ $entry =~ $logRegexToday ]]
      then
        # Don't gz today's log
        continue
      elif [[ $entry =~ $logRegexOther ]]
      then
        # Gz log
        chmod 774 "$entry"
        gzip -9 "$entry"
      fi
    done
  # Delete old files
  find $API_PATH/*.log.gz -mtime +$RETENTION -delete
fi

# Gz log files for bundle
if [ -d "$BUNDLE_PATH" ]; then
  for entry in "$BUNDLE_PATH"/*
    do
      if [[ $entry =~ $logRegexToday ]]
      then
        # Don't gz today's log
        continue
      elif [[ $entry =~ $logRegexOther ]]
      then
        # Gz log
        chmod 774 "$entry"
        gzip -9 "$entry"
      fi
    done
  # Delete old files
  find $BUNDLE_PATH/*.log.gz -mtime +$RETENTION -delete
fi

# Gz log files for client
if [ -d "$CLIENT_PATH" ]; then
  for entry in "$CLIENT_PATH"/*
    do
      if [[ $entry =~ $logRegexToday ]]
      then
        # Don't gz today's log
        continue
      elif [[ $entry =~ $logRegexOther ]]
      then
        # Gz log
        chmod 774 "$entry"
        gzip -9 "$entry"
      fi
    done
  # Delete old files
  find $CLIENT_PATH/*.log.gz -mtime +$RETENTION -delete
fi
