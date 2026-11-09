# Arranca el backend, el frontend o ambos.
param(
    [ValidateSet('all', 'backend', 'frontend')]
    [string]$Role = 'all'
)

. (Join-Path $PSScriptRoot '_common.ps1')

$root = Initialize-ProjectEnvironment
$php = Resolve-PhpBinary

$backendHost = Get-EnvOrDefault -Name 'BACKEND_HOST' -Default '127.0.0.1'
$backendPort = Get-EnvOrDefault -Name 'BACKEND_PORT' -Default '8081'
$frontendHost = Get-EnvOrDefault -Name 'FRONTEND_HOST' -Default '127.0.0.1'
$frontendPort = Get-EnvOrDefault -Name 'FRONTEND_PORT' -Default '8000'

if ($Role -eq 'backend') {
    & (Join-Path $PSScriptRoot '_serve-backend.ps1')
    exit $LASTEXITCODE
}

if ($Role -eq 'frontend') {
    & (Join-Path $PSScriptRoot '_serve-frontend.ps1')
    exit $LASTEXITCODE
}

$conflicts = @()
foreach ($port in @([int]$backendPort, [int]$frontendPort)) {
    $listener = Get-NetTCPConnection -LocalPort $port -State Listen -ErrorAction SilentlyContinue
    if ($listener) {
        $conflicts += $port
    }
}

if ($conflicts.Count -gt 0) {
    Write-Host "Puertos ocupados: $($conflicts -join ', ')" -ForegroundColor Red
    Write-Host 'Cambia BACKEND_PORT / FRONTEND_PORT en .env o deten esos procesos.' -ForegroundColor Yellow
    exit 1
}

$backendScript = Join-Path $PSScriptRoot '_serve-backend.ps1'
$frontendScript = Join-Path $PSScriptRoot '_serve-frontend.ps1'

$systemDir = $env:SystemDrive + '\'
Start-Process -FilePath 'powershell.exe' -WorkingDirectory $systemDir -ArgumentList "-NoProfile -NoExit -ExecutionPolicy Bypass -File `"$backendScript`""
Start-Process -FilePath 'powershell.exe' -WorkingDirectory $systemDir -ArgumentList "-NoProfile -NoExit -ExecutionPolicy Bypass -File `"$frontendScript`""

Write-Host ''
Write-Host '==========================================================' -ForegroundColor Green
Write-Host ' AURA Atelier iniciado en dos ventanas de PowerShell' -ForegroundColor Green
Write-Host '==========================================================' -ForegroundColor Green
Write-Host " Frontend: http://${frontendHost}:${frontendPort}" -ForegroundColor Cyan
Write-Host " Backend:  http://${backendHost}:${backendPort}" -ForegroundColor Cyan
Write-Host " Reportes: http://${backendHost}:${backendPort}/reports/sales" -ForegroundColor Cyan
Write-Host ''
Write-Host ' Credenciales de prueba:' -ForegroundColor Yellow
Write-Host '   admin@tiendaropa.com    / admin123'
Write-Host '   vendedor@tiendaropa.com / seller123'
Write-Host '   cliente@tiendaropa.com  / client123'
Write-Host ''
Write-Host ' Para detener: cierra las dos ventanas o presiona Ctrl+C en cada una.' -ForegroundColor DarkGray
