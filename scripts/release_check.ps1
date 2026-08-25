param([string]$BaseUrl = 'http://localhost/ucd/index.php')

$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
$failures = New-Object System.Collections.Generic.List[string]
$phpFiles = Get-ChildItem -LiteralPath $projectRoot -Recurse -Filter '*.php' -File

foreach ($file in $phpFiles) {
    & php -l $file.FullName *> $null
    if ($LASTEXITCODE -ne 0) { $failures.Add("PHP lint failed: $($file.FullName)") }
}

try {
    $health = Invoke-WebRequest -Uri "$($BaseUrl.TrimEnd('/'))/health" -UseBasicParsing
    if ($health.StatusCode -ne 200 -or $health.Content -notmatch '"status":"ok"') { $failures.Add('Health endpoint did not report OK.') }
    foreach ($header in @('Content-Security-Policy','X-Content-Type-Options','X-Frame-Options','Referrer-Policy','Permissions-Policy')) {
        if (-not $health.Headers[$header]) { $failures.Add("Missing security header: $header") }
    }
} catch {
    $failures.Add("Health request failed: $($_.Exception.Message)")
}

$publicRoot = $BaseUrl -replace '/index\.php/?$',''
foreach ($relativePath in @('docs/PROJECT_CONTEXT.md','database/phase16_boq_schema.sql','database/backup_nexus_ucd.ps1','scripts/release_check.ps1','composer.json')) {
    try {
        $response = Invoke-WebRequest -Uri "$($publicRoot.TrimEnd('/'))/$relativePath" -UseBasicParsing
        $failures.Add("Sensitive file is publicly readable: $relativePath (HTTP $($response.StatusCode))")
    } catch {
        if ([int]$_.Exception.Response.StatusCode -ne 403) { $failures.Add("Unexpected response for protected file ${relativePath}: $([int]$_.Exception.Response.StatusCode)") }
    }
}

Write-Output "PHP files linted: $($phpFiles.Count)"
if ($failures.Count -gt 0) {
    $failures | ForEach-Object { Write-Error $_ }
    exit 1
}
Write-Output 'Release checks passed.'
