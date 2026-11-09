# Levanta el frontend (tienda web) en la consola actual.
. (Join-Path $PSScriptRoot '_common.ps1')

$root = Initialize-ProjectEnvironment
$php = Resolve-PhpBinary
$hostName = Get-EnvOrDefault -Name 'FRONTEND_HOST' -Default '127.0.0.1'
$port = Get-EnvOrDefault -Name 'FRONTEND_PORT' -Default '8000'

$listener = Get-NetTCPConnection -LocalPort ([int]$port) -State Listen -ErrorAction SilentlyContinue
if ($listener) {
    Write-Host "El puerto $port ya esta en uso. Cambia FRONTEND_PORT en .env o deten el proceso que lo ocupa." -ForegroundColor Red
    exit 1
}

$docRoot = Join-Path $root 'frontend\public'
$router = Join-Path $docRoot 'index.php'

Write-Host "PHP:       $php" -ForegroundColor DarkGray
Write-Host "Frontend:  http://${hostName}:${port}" -ForegroundColor Cyan
Write-Host 'Presiona Ctrl+C para detener el servidor.' -ForegroundColor DarkGray
Write-Host ''

Set-Location -LiteralPath $root
& $php -S "${hostName}:${port}" -t $docRoot $router
