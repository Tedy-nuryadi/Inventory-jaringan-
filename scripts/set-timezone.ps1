$ErrorActionPreference = 'Stop'

# ==========================================================================
# INVENTORY JARINGAN - SET LARAVEL TIMEZONE
# ==========================================================================

$ProjectRoot = 'C:\xampp\htdocs\inventory-jaringan'
$ConfigFile  = Join-Path $ProjectRoot 'config\app.php'
$PhpExe      = 'C:\xampp\php\php.exe'

$Timestamp = Get-Date -Format 'yyyyMMdd_HHmmss'
$BackupFile = Join-Path $ProjectRoot ("config\app.php.before-timezone_$Timestamp")

Write-Host ''
Write-Host '============================================================'
Write-Host ' INVENTORY JARINGAN - SET TIMEZONE'
Write-Host '============================================================'
Write-Host ''


# ==========================================================================
# VALIDASI
# ==========================================================================

if (-not (Test-Path -LiteralPath $ConfigFile)) {
    throw "config\app.php tidak ditemukan: $ConfigFile"
}

if (-not (Test-Path -LiteralPath $PhpExe)) {
    throw "PHP tidak ditemukan: $PhpExe"
}


# ==========================================================================
# 1/5 BACKUP CONFIG
# ==========================================================================

Write-Host '[1/5] Backup config\app.php...'

Copy-Item `
    -LiteralPath $ConfigFile `
    -Destination $BackupFile `
    -Force

Write-Host "[OK] Backup dibuat: $BackupFile"


# ==========================================================================
# 2/5 CARI DAN UBAH TIMEZONE
# ==========================================================================

Write-Host '[2/5] Mengatur timezone Laravel...'

$Lines = [System.Collections.ArrayList]::new()

foreach ($line in Get-Content -LiteralPath $ConfigFile) {
    [void]$Lines.Add($line)
}

$FoundTimezone = $false

for ($i = 0; $i -lt $Lines.Count; $i++) {

    $line = [string]$Lines[$i]

    if ($line -match "^\s*'timezone'\s*=>") {

        $FoundTimezone = $true

        $match = [regex]::Match(
            $line,
            '^(\s*)'
        )

        $indent = $match.Groups[1].Value

        $Lines[$i] =
            $indent + "'timezone' => env('APP_TIMEZONE', 'UTC'),"

        break
    }
}

if (-not $FoundTimezone) {
    throw "Baris 'timezone' tidak ditemukan di config\app.php."
}

Write-Host "[OK] config\app.php sekarang membaca APP_TIMEZONE dari .env."


# ==========================================================================
# 3/5 SIMPAN FILE
# ==========================================================================

Write-Host '[3/5] Menyimpan config\app.php...'

$Utf8NoBom =
    New-Object System.Text.UTF8Encoding($false)

[System.IO.File]::WriteAllLines(
    $ConfigFile,
    [string[]]$Lines,
    $Utf8NoBom
)

Write-Host '[OK] config\app.php disimpan.'


# ==========================================================================
# 4/5 PHP SYNTAX CHECK
# ==========================================================================

Write-Host '[4/5] Memeriksa syntax PHP...'

& $PhpExe `
    -l `
    $ConfigFile

if ($LASTEXITCODE -ne 0) {

    Write-Host ''
    Write-Host '[ERROR] Syntax PHP bermasalah.'
    Write-Host '[INFO] Mengembalikan config dari backup...'

    Copy-Item `
        -LiteralPath $BackupFile `
        -Destination $ConfigFile `
        -Force

    throw 'Perubahan dibatalkan karena syntax PHP tidak valid.'
}

Write-Host '[OK] Syntax PHP valid.'


# ==========================================================================
# 5/5 CLEAR CACHE + VERIFY
# ==========================================================================

Write-Host '[5/5] Refresh konfigurasi Laravel...'
Write-Host ''

Push-Location $ProjectRoot

try {

    & $PhpExe artisan optimize:clear

    if ($LASTEXITCODE -ne 0) {
        throw 'artisan optimize:clear gagal.'
    }

    Write-Host ''
    Write-Host '============================================================'
    Write-Host ' VERIFIKASI'
    Write-Host '============================================================'
    Write-Host ''

    & $PhpExe artisan about

    if ($LASTEXITCODE -ne 0) {
        throw 'artisan about gagal.'
    }

}
finally {

    Pop-Location
}


Write-Host ''
Write-Host '============================================================'
Write-Host '[SUCCESS] TIMEZONE CONFIG SELESAI'
Write-Host '============================================================'
Write-Host ''
Write-Host "Backup : $BackupFile"
Write-Host 'Target : Asia/Jakarta'
Write-Host ''