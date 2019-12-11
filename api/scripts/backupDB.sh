#!/bin/bash
# Date and time
DATE=$(date +"%Y%m%d%H%M%S")

# Backup dir
BACKUP_DIR="/backup/mariadb"

# MySQL username/password
MYSQL_USER="user"
MYSQL_PASSWORD="password"

# Mysql commands
MYSQL=/usr/bin/mysql
MYSQLDUMP=/usr/bin/mysqldump

# Databases to ignore in the backup
SKIPDATABASES="information_schema|performance_schema|mysql"

# Retention days (backups older than retention days are removed)
RETENTION=5

# Creation of a new dir with the date
mkdir -p $BACKUP_DIR/$DATE

# Get all databases names
databases=`$MYSQL -u$MYSQL_USER -p$MYSQL_PASSWORD -e "SHOW DATABASES;" | grep -Ev "($SKIPDATABASES)"`

# Copy each database and gzip
for db in $databases; do
echo $db
$MYSQLDUMP --force --opt --user=$MYSQL_USER -p$MYSQL_PASSWORD --skip-lock-tables --events --databases $db | gzip > "$BACKUP_DIR/$DATE/$db.sql.gz"
done

# Delete old backups
find $BACKUP_DIR/* -mtime +$RETENTION -delete