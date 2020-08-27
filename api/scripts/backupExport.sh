#!/bin/bash

############################################
# Export the backups on an external volume #
#           To be scheduled in cron        #
############################################

for i in "$@"
do
case $i in
    --source-dir=*)
    SOURCE_DIR="${i#*=}"
    shift
    ;;
    --prefix=*)
    PREFIX="${i#*=}"
    shift
    ;;
    --export-dir=*)
    EXPORT_DIR="${i#*=}"
    shift
    ;;
esac
done

# We will export all the backups with the current date
DATE=$(date +"%Y%m%d")

# Retention days (backups older than retention days are removed)
RETENTION=5

# Create backup dir if not exist
mkdir -p $EXPORT_DIR

# Export backups
cp -R $SOURCE_DIR/"$PREFIX$DATE"* $EXPORT_DIR

# Delete old backups on the export dirs
find $EXPORT_DIR/* -mtime +$RETENTION -delete