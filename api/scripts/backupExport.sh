#!/bin/bash

############################################
# Export the backups on an external volume #
############################################

# We will export all the backups with the current date
DATE=$(date +"%Y%m%d")

# Instance export dir destination
#INSTANCE_EXPORT_DIR="/mnt/sdb1/backup/instance"

# Mariadb export dir destination
MARIADB_EXPORT_DIR="/mnt/sdb1/backup/mariadb"

# Instance backup dir 
#INSTANCE_BACKUP_DIR="/backup/instance"

# Mariadb backup dir 
MARIADB_BACKUP_DIR="/backup/mariadb"

# Export mariadb backups
cp -R $MARIADB_BACKUP_DIR/"$DATE"* $MARIADB_EXPORT_DIR