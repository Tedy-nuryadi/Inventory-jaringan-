
[CmdletBinding()]
param(
    [ValidateSet('Public', 'LAN', 'Both')]
    [string]$Mode
)

$ErrorActionPreference = 'Stop'

# ============================================================
# INVENTORY JARINGAN - MULTI ENVIRONMENT CONFIGURATION
# ============================================================

$ProjectRoot = 'C:\xampp\htdocs\inventory-jaringan'
$EnvFile = Join-Path $ProjectRoot '.env'
$PhpExe = 'C:\xampp\php\php.exe'

# ============================================================
# VALIDASI PROJECT
# ============================================================

if (-not (Test-Path -LiteralPath $EnvFile -PathType Leaf)) {
    throw ".env tidak ditemukan: $EnvFile"
}

if (-not (Test-Path -LiteralPath $PhpExe -PathType Leaf)) {
    throw "PHP tidak ditemukan: $PhpExe"
}

if (-not (Test-Path -LiteralPath (Join-Path $ProjectRoot 'artisan'))) {
    throw 'File artisan tidak ditemukan.'
}

# ============================================================
# PILIH MODE
# ============================================================

if ([string]::IsNullOrWhiteSpace($Mode)) {

    Write-Host ''
    Write-Host '============================================================'
    Write-Host ' INVENTORY JARINGAN - PILIH ENVIRONMENT'
    Write-Host '============================================================'
    Write-Host ''

    Write-Host '1. Server Publik HTTPS'
    Write-Host '2. Server LAN HTTP'
    Write-Host '3. Publik + LAN melalui HTTPS (RECOMMENDED)'
    Write-Host ''

    do {

        $Choice = Read-Host 'Pilih konfigurasi (1/2/3)'

        switch ($Choice) {

            '1' {
                $Mode = 'Public'
            }

            '2' {
                $Mode = 'LAN'
            }

            '3' {
                $Mode = 'Both'
            }

            default {
                Write-Host '[ERROR] Pilihan tidak valid.' -ForegroundColor Red
            }
        }

    } while ([string]::IsNullOrWhiteSpace($Mode))
}

# ============================================================
# PENGATURAN BERDASARKAN MODE
# ============================================================

if ($Mode -eq 'LAN') {

    $AppUrl = 'http://inventory-jaringan.local'
    $SecureCookie = 'false'
    $ModeLabel = 'SERVER LAN HTTP'

}
else {

    $AppUrl = 'https://inventoryjaringan69.duckdns.org'
    $SecureCookie = 'true'

    if ($Mode -eq 'Both') {
        $ModeLabel = 'SERVER PUBLIK + LAN HTTPS'
    }
    else {
        $ModeLabel = 'SERVER PUBLIK HTTPS'
    }
}

# ============================================================
# SETTING LARAVEL
# ============================================================

$Settings = [ordered]@{

    'APP_NAME'              = '"Inventory Jaringan"'
    'APP_ENV'               = 'production'
    'APP_DEBUG'             = 'false'
    'APP_URL'               = $AppUrl

    'APP_TIMEZONE'          = 'Asia/Jakarta'

    'APP_LOCALE'            = 'id'
    'APP_FALLBACK_LOCALE'   = 'id'
    'APP_FAKER_LOCALE'      = 'id_ID'

    'LOG_LEVEL'             = 'info'

    'SESSION_SECURE_COOKIE' = $SecureCookie
}

# ============================================================
# BACKUP .ENV SEBELUM PERUBAHAN
# ============================================================

$Timestamp = Get-Date -Format 'yyyyMMdd_HHmmss'

$BackupFile = Join-Path $ProjectRoot (
    '.env.before-env-switch_' + $Timestamp
)

Write-Host ''
Write-Host '============================================================'
Write-Host ' INVENTORY JARINGAN - ENVIRONMENT CONFIGURATION'
Write-Host '============================================================'
Write-Host ''

Write-Host "[MODE] $ModeLabel"
Write-Host "[URL]  $AppUrl"
Write-Host "[COOKIE SECURE] $SecureCookie"
Write-Host ''

Copy-Item `
    -LiteralPath $EnvFile `
    -Destination $BackupFile `
    -Force

Write-Host "[OK] Backup .env dibuat: $BackupFile"

$EnvUpdated = $false

try {

    # ========================================================
    # BACA .ENV
    # ========================================================

    Write-Host ''
    Write-Host '[1/4] Membaca konfigurasi .env...'

    $Lines = [System.Collections.ArrayList]::new()

    foreach ($Line in Get-Content -LiteralPath $EnvFile) {
        [void]$Lines.Add([string]$Line)
    }

    # ========================================================
    # UPDATE SETTING
    # Menghindari duplikasi key
    # ========================================================

    Write-Host '[2/4] Memperbarui konfigurasi...'

    foreach ($Key in $Settings.Keys) {

        $Value = $Settings[$Key]

        $Pattern = '^\s*' + [regex]::Escape($Key) + '\s*='

        $MatchIndexes = New-Object 'System.Collections.Generic.List[int]'

        for ($i = 0; $i -lt $Lines.Count; $i++) {

            if ([string]$Lines[$i] -match $Pattern) {

                [void]$MatchIndexes.Add($i)
            }
        }

        if ($MatchIndexes.Count -gt 0) {

            $FirstIndex = $MatchIndexes[0]

            $Lines[$FirstIndex] = "$Key=$Value"

            # Hapus duplikasi key dari belakang
            for ($j = $MatchIndexes.Count - 1; $j -ge 1; $j--) {

                $Lines.RemoveAt($MatchIndexes[$j])
            }
        }
        else {

            [void]$Lines.Add("$Key=$Value")
        }
    }

    # ========================================================
    # SIMPAN .ENV
    # ========================================================

    $Utf8NoBom = New-Object System.Text.UTF8Encoding($false)

    [System.IO.File]::WriteAllLines(
        $EnvFile,
        [string[]]$Lines,
        $Utf8NoBom
    )

    $EnvUpdated = $true

    Write-Host '[OK] .env berhasil diperbarui.'

    # ========================================================
    # CLEAR CACHE
    # ========================================================

    Write-Host ''
    Write-Host '[3/4] Membersihkan cache Laravel...'

    Push-Location $ProjectRoot

    try {

        & $PhpExe artisan optimize:clear

        if ($LASTEXITCODE -ne 0) {
            throw 'php artisan optimize:clear gagal.'
        }

    }
    finally {

        Pop-Location
    }

    Write-Host '[OK] Cache berhasil dibersihkan.'

    # ========================================================
    # VERIFIKASI
    # ========================================================

    Write-Host ''
    Write-Host '[4/4] Memverifikasi konfigurasi Laravel...'

    Push-Location $ProjectRoot

    try {

        & $PhpExe artisan about

        if ($LASTEXITCODE -ne 0) {
            throw 'php artisan about gagal.'
        }

    }
    finally {

        Pop-Location
    }

    # ========================================================
    # HASIL AKHIR
    # ========================================================

    Write-Host ''
    Write-Host '============================================================'
    Write-Host '[SUCCESS] ENVIRONMENT BERHASIL DIATUR'
    Write-Host '============================================================'
    Write-Host ''

    Write-Host "MODE          : $ModeLabel"
    Write-Host "APP_URL       : $AppUrl"
    Write-Host "APP_ENV       : production"
    Write-Host "APP_DEBUG     : false"
    Write-Host "TIMEZONE      : Asia/Jakarta"
    Write-Host "SECURE COOKIE : $SecureCookie"

    Write-Host ''
    Write-Host 'APP_KEY       : TIDAK DIUBAH'
    Write-Host 'DB CONFIG     : TIDAK DIUBAH'
    Write-Host 'DB PASSWORD   : TIDAK DIUBAH'

    Write-Host ''
    Write-Host "BACKUP .ENV   : $BackupFile"
    Write-Host ''

}
catch {

    Write-Host ''
    Write-Host '[ERROR] Konfigurasi gagal diterapkan.' -ForegroundColor Red
    Write-Host $_.Exception.Message -ForegroundColor Red

    # ========================================================
    # ROLLBACK .ENV JIKA TERJADI KEGAGALAN
    # ========================================================

    if ($EnvUpdated -and (Test-Path -LiteralPath $BackupFile)) {

        Write-Host ''
        Write-Host '[ROLLBACK] Mengembalikan .env sebelumnya...'

        Copy-Item `
            -LiteralPath $BackupFile `
            -Destination $EnvFile `
            -Force

        Write-Host '[OK] .env sebelumnya berhasil dikembalikan.'

        Push-Location $ProjectRoot

        try {

            & $PhpExe artisan optimize:clear

        }
        catch {

            Write-Host '[WARNING] Cache perlu diperiksa kembali.'
        }
        finally {

            Pop-Location
        }
    }

    throw
}
