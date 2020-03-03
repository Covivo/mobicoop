#!/bin/bash

################################
# Backup the platform api data #
################################

# Date and time
DATE=$(date +"%Y%m%d%H%M%S")

# Backup dir destination
BACKUP_DIR="/backup/instance"

# Base api dir
BASE_DIR="/var/www/instance/mobicoop-platform/api/public/upload/"

# Backup filename
FILENAME=backup_$DATE.tgz

# Retention days (backups older than retention days are removed)
RETENTION=5

# Make the backup
tar --create --gzip --file=$BACKUP_DIR/$FILENAME $BASE_DIR

# Delete old backups
find $BACKUP_DIR/* -mtime +$RETENTION -delete