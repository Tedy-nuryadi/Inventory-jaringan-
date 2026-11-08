$ErrorActionPreference = 'Stop'

# ==========================================================================
# INVENTORY JARINGAN
# ENABLE PUBLIC IP
# ==========================================================================

$ProjectRoot = 'C:\xampp\htdocs\inventory-jaringan'

$VhostsFile = 'C:\xampp\apache\conf\extra\httpd-vhosts.conf'
$EnvFile    = Join-Path $ProjectRoot '.env'

$ApacheExe = 'C:\xampp\apache\bin\httpd.exe'
$PhpExe    = 'C:\xampp\php\php.exe'

$LanIp    = '192.168.90.2'
$PublicIp = '49.0.1.2'
$LocalHostName = 'inventory-jaringan.local'

$Timestamp = Get-Date -Format 'yyyyMMdd_HHmmss'

$VhostBackup =
    $VhostsFile + '.before-public-ip_' + $Timestamp

$EnvBackup =
    Join-Path $ProjectRoot (
        '.env.before-public-ip_' + $Timestamp
    )


Write-Host ''
Write-Host '============================================================'
Write-Host ' INVENTORY JARINGAN - ENABLE PUBLIC IP'
Write-Host '============================================================'
Write-Host ''


# ==========================================================================
# VALIDASI
# ==========================================================================

if (-not (Test-Path -LiteralPath $VhostsFile)) {
    throw "VirtualHost file tidak ditemukan: $VhostsFile"
}

if (-not (Test-Path -LiteralPath $EnvFile)) {
    throw ".env tidak ditemukan: $EnvFile"
}

if (-not (Test-Path -LiteralPath $ApacheExe)) {
    throw "Apache tidak ditemukan: $ApacheExe"
}

if (-not (Test-Path -LiteralPath $PhpExe)) {
    throw "PHP tidak ditemukan: $PhpExe"
}

if (-not (Test-Path -LiteralPath (Join-Path $ProjectRoot 'artisan'))) {
    throw 'artisan tidak ditemukan.'
}


# ==========================================================================
# 1/7 BACKUP
# ==========================================================================

Write-Host '[1/7] Backup konfigurasi...'

Copy-Item `
    -LiteralPath $VhostsFile `
    -Destination $VhostBackup `
    -Force

Copy-Item `
    -LiteralPath $EnvFile `
    -Destination $EnvBackup `
    -Force

Write-Host "[OK] VHost backup : $VhostBackup"
Write-Host "[OK] .env backup  : $EnvBackup"
Write-Host ''


# ==========================================================================
# 2/7 APACHE VHOST
# ==========================================================================

Write-Host '[2/7] Mengaktifkan IP publik pada VirtualHost...'


$Config = @'
# ==========================================================================
# XAMPP VIRTUAL HOSTS - INVENTORY JARINGAN
# ==========================================================================


# ==========================================================================
# DEFAULT DENY
#
# Host/IP yang tidak dikenal tetap ditolak.
# ==========================================================================

<VirtualHost *:80>

    ServerName default-deny.invalid

    DocumentRoot "C:/xampp/htdocs"

    <Directory "C:/xampp/htdocs">
        AllowOverride None
        Require all denied
    </Directory>

    ErrorLog "logs/default-deny-error.log"
    CustomLog "logs/default-deny-access.log" common

</VirtualHost>


# ==========================================================================
# LOCALHOST
#
# Root XAMPP hanya dapat diakses dari komputer server.
# ==========================================================================

<VirtualHost *:80>

    ServerName localhost

    DocumentRoot "C:/xampp/htdocs"

    <Directory "C:/xampp/htdocs">

        Options Indexes FollowSymLinks Includes ExecCGI

        AllowOverride All

        Require local

    </Directory>

    ErrorLog "logs/localhost-error.log"
    CustomLog "logs/localhost-access.log" common

</VirtualHost>


# ==========================================================================
# INVENTORY JARINGAN
# ==========================================================================

<VirtualHost *:80>

    ServerName inventory-jaringan.local

    ServerAlias 192.168.90.2
    ServerAlias 49.0.1.2

    DocumentRoot "C:/xampp/htdocs/inventory-jaringan/public"

    <Directory "C:/xampp/htdocs/inventory-jaringan/public">

        Options FollowSymLinks

        AllowOverride All

        Require all granted

    </Directory>

    ErrorLog "logs/inventory-jaringan-error.log"
    CustomLog "logs/inventory-jaringan-access.log" common

</VirtualHost>
'@


$Utf8NoBom =
    New-Object System.Text.UTF8Encoding($false)


[System.IO.File]::WriteAllText(
    $VhostsFile,
    $Config,
    $Utf8NoBom
)


Write-Host '[OK] Public IP ditambahkan ke VirtualHost.'
Write-Host ''


# ==========================================================================
# 3/7 APACHE SYNTAX CHECK
# ==========================================================================

Write-Host '[3/7] Memeriksa syntax Apache...'
Write-Host ''

& $ApacheExe -t

if ($LASTEXITCODE -ne 0) {

    Write-Host ''
    Write-Host '[ERROR] Syntax Apache gagal.'
    Write-Host '[INFO] Mengembalikan konfigurasi sebelumnya...'

    Copy-Item `
        -LiteralPath $VhostBackup `
        -Destination $VhostsFile `
        -Force

    throw 'Apache configuration rollback dilakukan.'
}

Write-Host ''
Write-Host '[OK] Syntax Apache valid.'
Write-Host ''


# ==========================================================================
# 4/7 UPDATE APP_URL
# ==========================================================================

Write-Host '[4/7] Mengatur APP_URL ke public IP...'


$Lines = [System.Collections.ArrayList]::new()

foreach ($line in Get-Content -LiteralPath $EnvFile) {
    [void]$Lines.Add($line)
}


$Settings = [ordered]@{

    'APP_URL' =
        'http://49.0.1.2'

    'SESSION_SECURE_COOKIE' =
        'false'
}


foreach ($Key in $Settings.Keys) {

    $Value = $Settings[$Key]

    $Pattern =
        '^\s*' +
        [regex]::Escape($Key) +
        '\s*='

    $Found = $false


    for ($i = 0; $i -lt $Lines.Count; $i++) {

        if ($Lines[$i] -match $Pattern) {

            $Lines[$i] =
                $Key + '=' + $Value

            $Found = $true

            break
        }
    }


    if (-not $Found) {

        [void]$Lines.Add(
            $Key + '=' + $Value
        )
    }
}


[System.IO.File]::WriteAllLines(
    $EnvFile,
    [string[]]$Lines,
    $Utf8NoBom
)


Write-Host '[OK] APP_URL=http://49.0.1.2'
Write-Host '[OK] SESSION_SECURE_COOKIE=false'
Write-Host ''


# ==========================================================================
# 5/7 REFRESH LARAVEL CONFIG
# ==========================================================================

Write-Host '[5/7] Refresh configuration cache Laravel...'
Write-Host ''


Push-Location $ProjectRoot

try {

    & $PhpExe artisan config:clear

    if ($LASTEXITCODE -ne 0) {
        throw 'artisan config:clear gagal.'
    }


    & $PhpExe artisan config:cache

    if ($LASTEXITCODE -ne 0) {
        throw 'artisan config:cache gagal.'
    }

}
finally {

    Pop-Location
}


Write-Host ''
Write-Host '[OK] Laravel configuration cache diperbarui.'
Write-Host ''


# ==========================================================================
# 6/7 VERIFY VHOST
# ==========================================================================

Write-Host '[6/7] Verifikasi VirtualHost...'
Write-Host ''

& $ApacheExe -S

if ($LASTEXITCODE -ne 0) {
    throw 'Apache VirtualHost verification gagal.'
}


# ==========================================================================
# 7/7 FINAL INFORMATION
# ==========================================================================

Write-Host ''
Write-Host '[7/7] Konfigurasi selesai.'
Write-Host ''

Write-Host '============================================================'
Write-Host '[SUCCESS] PUBLIC IP BERHASIL DIKONFIGURASI'
Write-Host '============================================================'
Write-Host ''
Write-Host "Local Host : http://$LocalHostName"
Write-Host "LAN        : http://$LanIp"
Write-Host "Public     : http://$PublicIp"
Write-Host ''
Write-Host 'Unknown Host : TETAP DENY'
Write-Host 'XAMPP Root   : LOCAL ONLY'
Write-Host ''
Write-Host 'PENTING:'
Write-Host 'Restart Apache dari XAMPP Control Panel.'
Write-Host ''
Write-Host 'PERINGATAN:'
Write-Host 'Public IP saat ini masih HTTP, belum HTTPS.'
Write-Host 'Jangan gunakan akun/password sensitif sebelum HTTPS aktif.'
Write-Host ''