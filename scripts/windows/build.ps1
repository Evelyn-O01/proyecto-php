# Compila/valida el proyecto desde Windows:
#   1. Revisa la sintaxis de todos los archivos PHP.
#   2. Ejecuta la verificacion funcional de la arquitectura.
#   3. Opcionalmente regenera el autoload optimizado de Composer.
param(
    [switch]$OptimizeAutoload
)

. (Join-Path $PSScriptRoot '_common.ps1')

$root = Initialize-ProjectEnvironment
$php = Resolve-PhpBinary

Write-Host "PHP: $php" -ForegroundColor DarkGray
Write-Host "Proyecto: $root" -ForegroundColor DarkGray
Write-Host ''

Write-Host '[1/2] Revisando sintaxis PHP...' -ForegroundColor Yellow
$files = Get-ChildItem -Path $root -Recurse -Filter *.php -File |
    Where-Object { $_.FullName -notmatch '\\vendor\\' -and $_.FullName -notmatch '\\legacy\\' }

$errors = @()
foreach ($file in $files) {
    $output = & $php -l $file.FullName 2>&1
    if ($LASTEXITCODE -ne 0) {
        $errors += "Archivo: $($file.FullName)`n$($output -join "`n")"
    }
}

if ($errors.Count -gt 0) {
    Write-Host "Se encontraron errores de sintaxis en $($errors.Count) archivo(s):" -ForegroundColor Red
    $errors | ForEach-Object { Write-Host $_ -ForegroundColor Red }
    exit 1
}

Write-Host "OK: $($files.Count) archivos PHP sin errores de sintaxis." -ForegroundColor Green
Write-Host ''

Write-Host '[2/2] Ejecutando verificacion funcional...' -ForegroundColor Yellow
# Si el backend no esta levantado, el cliente cae al bridge interno tras este timeout.
$env:BACKEND_TIMEOUT = '1'
Push-Location -LiteralPath $root
try {
    & $php (Join-Path $root 'tests\verify_architecture.php')
    $verifyExit = $LASTEXITCODE
} finally {
    Pop-Location
}

if ($verifyExit -ne 0) {
    Write-Host 'La verificacion funcional fallo.' -ForegroundColor Red
    exit $verifyExit
}

if ($OptimizeAutoload) {
    Write-Host ''
    Write-Host 'Regenerando autoload optimizado de Composer...' -ForegroundColor Yellow
    $composer = Resolve-ComposerPhar
    $env:COMPOSER_ROOT_VERSION = '1.0.0'

    # cmd + pushd evita el problema de Composer con rutas UNC (\\wsl.localhost\...).
    $command = "pushd `"$root`" && `"$php`" `"$composer`" dump-autoload --optimize --no-interaction --no-scripts && popd"

    $composerExit = 1
    Push-Location -LiteralPath ($env:SystemDrive + '\')
    try {
        cmd /c $command
        $composerExit = $LASTEXITCODE
    } finally {
        Pop-Location
    }

    if ($composerExit -eq 0) {
        Write-Host 'Autoload optimizado generado.' -ForegroundColor Green
    } else {
        Write-Host 'Aviso: Composer no pudo regenerar el autoload. El autoload existente sigue siendo valido.' -ForegroundColor Yellow
    }
}

Write-Host ''
Write-Host 'Compilacion/verificacion completada correctamente.' -ForegroundColor Green
