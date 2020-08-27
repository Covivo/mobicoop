#!/bin/bash

################################
# Backup the platform api data #
#    To be scheduled in cron   #
################################

for i in "$@"
do
case $i in
    --backup-dir=*)
    BACKUP_DIR="${i#*=}"
    shift
    ;;
    --base-dir=*)
    BASE_DIR="${i#*=}"
    shift
    ;;
esac
done

# Date and time
DATE=$(date +"%Y%m%d%H%M%S")

# Backup filename
FILENAME=backup_$DATE.tgz

# Retention days (backups older than retention days are removed)
RETENTION=5

# Make the backup
tar --create --gzip --file=$BACKUP_DIR/$FILENAME $BASE_DIR

# Delete old backups
find $BACKUP_DIR/* -mtime +$RETENTION -delete