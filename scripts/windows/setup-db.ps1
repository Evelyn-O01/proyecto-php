# Crea la base de datos, aplica migraciones y carga datos de ejemplo.
param(
    [switch]$SkipSeed
)

. (Join-Path $PSScriptRoot '_common.ps1')

$root = Initialize-ProjectEnvironment
$psql = Resolve-PsqlBinary

$dbHost = Get-EnvOrDefault -Name 'DB_HOST' -Default '127.0.0.1'
$dbPort = Get-EnvOrDefault -Name 'DB_PORT' -Default '5432'
$dbName = Get-EnvOrDefault -Name 'DB_NAME' -Default 'tienda_ropa_db'
$dbUser = Get-EnvOrDefault -Name 'DB_USER' -Default 'postgres'
$dbPass = Get-EnvOrDefault -Name 'DB_PASS' -Default ''

if ($dbName -notmatch '^[A-Za-z0-9_]+$') {
    throw "Nombre de base de datos invalido: $dbName"
}

if ([string]::IsNullOrEmpty($dbPass)) {
    Remove-Item Env:PGPASSWORD -ErrorAction SilentlyContinue
} else {
    $env:PGPASSWORD = $dbPass
}

# Evita que los NOTICE de PostgreSQL (ya existe, omitiendo...) se traten como errores.
$env:PGOPTIONS = '-c client_min_messages=warning'

Write-Host "psql:     $psql" -ForegroundColor DarkGray
Write-Host "Servidor: ${dbHost}:${dbPort}" -ForegroundColor DarkGray
Write-Host "Base:     $dbName" -ForegroundColor DarkGray
Write-Host ''

# Verificar conexion al servidor.
& $psql -h $dbHost -p $dbPort -U $dbUser -d postgres -tAc 'SELECT 1' | Out-Null
if ($LASTEXITCODE -ne 0) {
    throw 'No se pudo conectar a PostgreSQL. Verifica DB_HOST, DB_PORT, DB_USER y DB_PASS en .env.'
}

$exists = (& $psql -h $dbHost -p $dbPort -U $dbUser -d postgres -tAc "SELECT 1 FROM pg_database WHERE datname = '$dbName'" | Out-String).Trim()

if ($exists -ne '1') {
    Write-Host "Creando base de datos '$dbName'..." -ForegroundColor Yellow
    & $psql -h $dbHost -p $dbPort -U $dbUser -d postgres -v ON_ERROR_STOP=1 -c "CREATE DATABASE $dbName WITH ENCODING 'UTF8'"
    if ($LASTEXITCODE -ne 0) {
        throw "No se pudo crear la base de datos '$dbName'."
    }
} else {
    Write-Host "La base de datos '$dbName' ya existe." -ForegroundColor DarkGray
}

$migrations = Join-Path $root 'backend\database\migrations.sql'
$seed = Join-Path $root 'backend\database\seed.sql'

Write-Host 'Aplicando migraciones...' -ForegroundColor Yellow
& $psql -h $dbHost -p $dbPort -U $dbUser -d $dbName -v ON_ERROR_STOP=1 -f $migrations
if ($LASTEXITCODE -ne 0) {
    throw 'Fallo la ejecucion de migrations.sql.'
}

if (-not $SkipSeed) {
    Write-Host 'Cargando datos de ejemplo (seed)...' -ForegroundColor Yellow
    & $psql -h $dbHost -p $dbPort -U $dbUser -d $dbName -v ON_ERROR_STOP=1 -f $seed
    if ($LASTEXITCODE -ne 0) {
        throw 'Fallo la ejecucion de seed.sql.'
    }
}

Write-Host ''
Write-Host 'Base de datos lista.' -ForegroundColor Green
& $psql -h $dbHost -p $dbPort -U $dbUser -d $dbName -tAc "SELECT 'usuarios=' || count(*) FROM users UNION ALL SELECT 'productos=' || count(*) FROM products UNION ALL SELECT 'pedidos=' || count(*) FROM orders"

Write-Host ''
Write-Host 'Credenciales de la aplicacion:' -ForegroundColor Yellow
Write-Host '  admin@tiendaropa.com    / admin123'
Write-Host '  vendedor@tiendaropa.com / seller123'
Write-Host '  cliente@tiendaropa.com  / client123'
