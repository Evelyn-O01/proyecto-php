# Utilidades compartidas para los scripts de Windows.
$ErrorActionPreference = 'Stop'

function Get-ProjectRoot {
    $root = Join-Path $PSScriptRoot '..\..'
    $resolved = (Resolve-Path -LiteralPath $root).ProviderPath
    return [System.IO.Path]::GetFullPath($resolved)
}

function Import-DotEnv {
    param(
        [Parameter(Mandatory = $true)]
        [string]$Path
    )

    if (-not (Test-Path -LiteralPath $Path)) {
        return
    }

    Get-Content -LiteralPath $Path -Encoding UTF8 | ForEach-Object {
        $line = $_.Trim()

        if ($line -eq '' -or $line.StartsWith('#')) {
            return
        }

        if ($line.StartsWith('export ')) {
            $line = $line.Substring(7).Trim()
        }

        $separator = $line.IndexOf('=')
        if ($separator -lt 1) {
            return
        }

        $key = $line.Substring(0, $separator).Trim()
        $value = $line.Substring($separator + 1).Trim()

        if ($value.Length -ge 2) {
            $first = $value.Substring(0, 1)
            $last = $value.Substring($value.Length - 1, 1)
            if (($first -eq '"' -and $last -eq '"') -or ($first -eq "'" -and $last -eq "'")) {
                $value = $value.Substring(1, $value.Length - 2)
            }
        }

        if (-not [string]::IsNullOrWhiteSpace($key)) {
            Set-Item -Path "Env:$key" -Value $value
        }
    }
}

function Initialize-ProjectEnvironment {
    $root = Get-ProjectRoot
    $envFile = Join-Path $root '.env'
    $envExample = Join-Path $root '.env.example'

    if (-not (Test-Path -LiteralPath $envFile) -and (Test-Path -LiteralPath $envExample)) {
        Copy-Item -LiteralPath $envExample -Destination $envFile
        Write-Host 'Se creo .env a partir de .env.example' -ForegroundColor Yellow
    }

    Import-DotEnv -Path $envFile

    return $root
}

function Get-EnvOrDefault {
    param(
        [Parameter(Mandatory = $true)]
        [string]$Name,
        [Parameter(Mandatory = $true)]
        [AllowEmptyString()]
        [string]$Default
    )

    $value = [Environment]::GetEnvironmentVariable($Name, 'Process')
    if ([string]::IsNullOrWhiteSpace($value)) {
        return $Default
    }

    return $value
}

function Resolve-PhpBinary {
    if (-not [string]::IsNullOrWhiteSpace($env:PHP_BIN)) {
        if (Test-Path -LiteralPath $env:PHP_BIN) {
            return (Resolve-Path -LiteralPath $env:PHP_BIN).ProviderPath
        }
        Write-Host "Aviso: PHP_BIN no existe ($env:PHP_BIN). Se buscara php.exe automaticamente." -ForegroundColor Yellow
    }

    $command = Get-Command php -ErrorAction SilentlyContinue
    if ($command) {
        return $command.Source
    }

    $candidates = @(
        'C:\xampp\php\php.exe',
        'C:\laragon\bin\php\php.exe',
        'C:\php\php.exe',
        (Join-Path $env:ProgramFiles 'PHP\php.exe')
    )

    foreach ($candidate in $candidates) {
        if ($candidate -and (Test-Path -LiteralPath $candidate)) {
            return (Resolve-Path -LiteralPath $candidate).ProviderPath
        }
    }

    throw 'No se encontro php.exe. Define PHP_BIN en .env o agrega PHP al PATH.'
}

function Resolve-PsqlBinary {
    if (-not [string]::IsNullOrWhiteSpace($env:PSQL_BIN)) {
        if (Test-Path -LiteralPath $env:PSQL_BIN) {
            return (Resolve-Path -LiteralPath $env:PSQL_BIN).ProviderPath
        }
        Write-Host "Aviso: PSQL_BIN no existe ($env:PSQL_BIN). Se buscara psql.exe automaticamente." -ForegroundColor Yellow
    }

    $command = Get-Command psql -ErrorAction SilentlyContinue
    if ($command) {
        return $command.Source
    }

    $postgresRoot = Join-Path $env:ProgramFiles 'PostgreSQL'
    if (Test-Path -LiteralPath $postgresRoot) {
        $versions = Get-ChildItem -LiteralPath $postgresRoot -Directory | Sort-Object Name -Descending
        foreach ($version in $versions) {
            $candidate = Join-Path $version.FullName 'bin\psql.exe'
            if (Test-Path -LiteralPath $candidate) {
                return (Resolve-Path -LiteralPath $candidate).ProviderPath
            }
        }
    }

    throw 'No se encontro psql.exe. Define PSQL_BIN en .env o agrega PostgreSQL al PATH.'
}

function Resolve-ComposerPhar {
    if (-not [string]::IsNullOrWhiteSpace($env:COMPOSER_PHAR)) {
        if (Test-Path -LiteralPath $env:COMPOSER_PHAR) {
            return (Resolve-Path -LiteralPath $env:COMPOSER_PHAR).ProviderPath
        }
        Write-Host "Aviso: COMPOSER_PHAR no existe ($env:COMPOSER_PHAR). Se buscara composer.phar automaticamente." -ForegroundColor Yellow
    }

    $candidates = @(
        'C:\ProgramData\ComposerSetup\bin\composer.phar',
        (Join-Path $env:USERPROFILE 'composer.phar'),
        'C:\xampp\php\composer.phar'
    )

    foreach ($candidate in $candidates) {
        if ($candidate -and (Test-Path -LiteralPath $candidate)) {
            return (Resolve-Path -LiteralPath $candidate).ProviderPath
        }
    }

    throw 'No se encontro composer.phar. Define COMPOSER_PHAR en .env.'
}
