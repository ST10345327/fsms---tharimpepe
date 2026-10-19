Param(
    [string]$Host = '127.0.0.1',
    [int]$Port = 3306,
    [string]$User = 'root',
    [string]$Password = '',
    [string]$Database = 'fsms'
)

$scriptRoot = Split-Path -Parent $MyInvocation.MyCommand.Definition
$schemaPath = Join-Path $scriptRoot "..\sql\schema.sql" | Resolve-Path -ErrorAction Stop

# Locate mysql CLI
$mysqlPaths = @(
    'C:\xampp\mysql\bin\mysql.exe',
    'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe',
    'C:\Program Files\MySQL\MySQL Server 5.7\bin\mysql.exe'
)
$mysqlExe = $null
foreach ($p in $mysqlPaths) {
    if (Test-Path $p) { $mysqlExe = $p; break }
}
if (-not $mysqlExe) {
    $cmd = Get-Command mysql -ErrorAction SilentlyContinue
    if ($cmd) { $mysqlExe = $cmd.Source }
}

if (-not $mysqlExe) {
    Write-Error "mysql CLI not found. Install MySQL client or ensure mysql is on PATH, or import sql/schema.sql via phpMyAdmin."
    exit 1
}

Write-Host "Using mysql: $mysqlExe"
Write-Host "Creating database '$Database' on $Host:$Port as $User"

# Build auth args
$authArgs = "-u$User"
if ($Password -ne '') { $authArgs += " -p$Password" }

# Create database
$createCmd = "`"$mysqlExe`" -h $Host -P $Port $authArgs -e \"CREATE DATABASE IF NOT EXISTS \`$Database\`;\""
iex $createCmd

# Import schema
$importCmd = "`"$mysqlExe`" -h $Host -P $Port $authArgs $Database < `"$schemaPath`""
iex $importCmd

Write-Host "Database '$Database' initialized (schema imported)."

exit 0
