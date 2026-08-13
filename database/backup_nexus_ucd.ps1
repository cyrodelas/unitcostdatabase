param(
    [Parameter(Mandatory = $true)]
    [string]$BackupDirectory,
    [string]$MySqlDumpPath = 'C:\xampp\mysql\bin\mysqldump.exe'
)

$ErrorActionPreference = 'Stop'
$databaseName = if ($env:UCD_DB_NAME) { $env:UCD_DB_NAME } else { 'nexus_ucd' }
$databaseHost = if ($env:UCD_DB_HOST) { $env:UCD_DB_HOST } else { '127.0.0.1' }
$databasePort = if ($env:UCD_DB_PORT) { $env:UCD_DB_PORT } else { '3306' }
$databaseUser = if ($env:UCD_DB_USER) { $env:UCD_DB_USER } else { 'root' }
$databasePassword = if ($null -ne $env:UCD_DB_PASSWORD) { $env:UCD_DB_PASSWORD } else { '' }

if (-not (Test-Path -LiteralPath $MySqlDumpPath -PathType Leaf)) {
    throw "mysqldump was not found at: $MySqlDumpPath"
}

$resolvedBackupDirectory = [System.IO.Path]::GetFullPath($BackupDirectory)
if (-not (Test-Path -LiteralPath $resolvedBackupDirectory)) {
    New-Item -ItemType Directory -Path $resolvedBackupDirectory | Out-Null
}

$timestamp = Get-Date -Format 'yyyyMMdd_HHmmss'
$backupPath = Join-Path $resolvedBackupDirectory "${databaseName}_${timestamp}.sql"
$arguments = @(
    "--host=$databaseHost",
    "--port=$databasePort",
    "--user=$databaseUser",
    '--single-transaction',
    '--routines',
    '--triggers',
    '--events',
    '--hex-blob',
    '--default-character-set=utf8mb4',
    "--result-file=$backupPath",
    $databaseName
)

$previousPassword = $env:MYSQL_PWD
try {
    $env:MYSQL_PWD = $databasePassword
    & $MySqlDumpPath @arguments
    if ($LASTEXITCODE -ne 0) { throw "mysqldump failed with exit code $LASTEXITCODE." }
} finally {
    $env:MYSQL_PWD = $previousPassword
}

$hash = Get-FileHash -Algorithm SHA256 -LiteralPath $backupPath
$checksumPath = "$backupPath.sha256"
Set-Content -LiteralPath $checksumPath -Value "$($hash.Hash.ToLowerInvariant())  $([System.IO.Path]::GetFileName($backupPath))" -Encoding ASCII

Write-Output "Backup: $backupPath"
Write-Output "SHA-256: $($hash.Hash.ToLowerInvariant())"
