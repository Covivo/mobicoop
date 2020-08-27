#!/bin/bash

############################################
# Export the backups on an external volume #
#           To be scheduled in cron        #
############################################

for i in "$@"
do
case $i in
    --backup-dir=*)
    INSTANCE_BACKUP_DIR="${i#*=}"
    shift
    ;;
    --export-dir=*)
    INSTANCE_EXPORT_DIR="${i#*=}"
    shift
    ;;
esac
done

# We will export all the backups with the current date
DATE=$(date +"%Y%m%d")

# Retention days (backups older than retention days are removed)
RETENTION=5

# Export backups
cp -R $BACKUP_DIR/"$DATE"* $EXPORT_DIR

# Delete old backups on the export dirs
find $EXPORT_DIR/* -mtime +$RETENTION -delete