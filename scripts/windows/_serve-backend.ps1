# Levanta el backend (API REST + reportes) en la consola actual.
. (Join-Path $PSScriptRoot '_common.ps1')

$root = Initialize-ProjectEnvironment
$php = Resolve-PhpBinary
$hostName = Get-EnvOrDefault -Name 'BACKEND_HOST' -Default '127.0.0.1'
$port = Get-EnvOrDefault -Name 'BACKEND_PORT' -Default '8081'

$listener = Get-NetTCPConnection -LocalPort ([int]$port) -State Listen -ErrorAction SilentlyContinue
if ($listener) {
    Write-Host "El puerto $port ya esta en uso. Cambia BACKEND_PORT en .env o deten el proceso que lo ocupa." -ForegroundColor Red
    exit 1
}

$docRoot = Join-Path $root 'backend\public'
$router = Join-Path $docRoot 'index.php'

Write-Host "PHP:      $php" -ForegroundColor DarkGray
Write-Host "Backend:  http://${hostName}:${port}" -ForegroundColor Cyan
Write-Host 'Presiona Ctrl+C para detener el servidor.' -ForegroundColor DarkGray
Write-Host ''

Set-Location -LiteralPath $root
& $php -S "${hostName}:${port}" -t $docRoot $router
