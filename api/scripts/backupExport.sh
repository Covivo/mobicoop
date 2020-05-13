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

# Coviride2MobicoopPlatform export dir destination
C2MP_EXPORT_DIR="/mnt/sdb1/backup/coviride2mobicoopplatform"

# Instance backup dir 
#INSTANCE_BACKUP_DIR="/backup/instance"

# Mariadb backup dir 
MARIADB_BACKUP_DIR="/home/ubuntu/backup/mariadb"

# Coviride2MobicoopPlaform backup dir
C2MP_BACKUP_DIR="/home/ubuntu/backup/coviride2mobicoopplatform"

# Export mariadb backups
cp -R $MARIADB_BACKUP_DIR/"$DATE"* $MARIADB_EXPORT_DIR

# Export Coviride2MobicoopPlaform backups
cp -R $C2MP_BACKUP_DIR $C2MP_EXPORT_DIR