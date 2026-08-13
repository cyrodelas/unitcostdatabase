# Backup and Recovery

## Backup

Run backups from a trusted host and write them outside the web root:

```powershell
$env:UCD_DB_HOST='127.0.0.1'
$env:UCD_DB_PORT='3306'
$env:UCD_DB_USER='ucd_backup'
$env:UCD_DB_PASSWORD='use-a-secret-store-value'
$env:UCD_DB_NAME='nexus_ucd'
.\database\backup_nexus_ucd.ps1 -BackupDirectory 'D:\NexusUCD_Backups'
```

The script uses a transactional `mysqldump`, includes routines/triggers/events, and writes a SHA-256 checksum. Encrypt backups, restrict access, copy them off-host, and apply the organization's retention policy. Never store production credentials in this repository.

## Verification

Verify the checksum and perform scheduled restore drills into an isolated database. Confirm row counts, foreign keys, the two stored procedures, application login, `/health`, and core read-only pages.

## Recovery

1. Stop application writes and preserve the failed database for investigation.
2. Provision an empty isolated database with the required MariaDB/MySQL version and utf8mb4 settings.
3. Verify the selected backup checksum.
4. Restore with the native MySQL client using a privileged recovery account.
5. Point a non-production Project Nexus UCD instance to the restored database and execute `scripts/release_check.ps1` plus functional smoke tests.
6. Only after validation, schedule the production cutover and record the recovery event.

Restoration overwrites/creates database state and must never be run against production without explicit authorization and a confirmed target.
