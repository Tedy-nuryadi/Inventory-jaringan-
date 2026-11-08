$ErrorActionPreference = 'Stop'

# ==========================================================================
# INVENTORY JARINGAN
# APACHE VIRTUAL HOST HARDENING
# ==========================================================================

$VhostsFile = 'C:\xampp\apache\conf\extra\httpd-vhosts.conf'
$ApacheExe  = 'C:\xampp\apache\bin\httpd.exe'

$Timestamp = Get-Date -Format 'yyyyMMdd_HHmmss'
$BackupFile = $VhostsFile + '.before-hardening_' + $Timestamp


Write-Host ''
Write-Host '============================================================'
Write-Host ' INVENTORY JARINGAN - APACHE HARDENING'
Write-Host '============================================================'
Write-Host ''


# ==========================================================================
# VALIDASI
# ==========================================================================

if (-not (Test-Path -LiteralPath $VhostsFile)) {
    throw "File VirtualHost tidak ditemukan: $VhostsFile"
}

if (-not (Test-Path -LiteralPath $ApacheExe)) {
    throw "Apache tidak ditemukan: $ApacheExe"
}


# ==========================================================================
# 1/5 BACKUP
# ==========================================================================

Write-Host '[1/5] Backup konfigurasi VirtualHost...'

Copy-Item `
    -LiteralPath $VhostsFile `
    -Destination $BackupFile `
    -Force

Write-Host "[OK] Backup: $BackupFile"
Write-Host ''


# ==========================================================================
# 2/5 CONFIG BARU
# ==========================================================================

Write-Host '[2/5] Membuat konfigurasi VirtualHost aman...'


$Config = @'
# ==========================================================================
# XAMPP VIRTUAL HOSTS - INVENTORY JARINGAN
# ==========================================================================


# ==========================================================================
# DEFAULT / UNKNOWN HOST
#
# Semua request yang hostname-nya tidak dikenal akan ditolak.
#
# Ini mencegah request dari Internet jatuh ke:
#
#     C:/xampp/htdocs
#
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
# LOCALHOST XAMPP
#
# XAMPP localhost tetap tersedia untuk komputer server.
# Akses dibatasi hanya dari komputer lokal.
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

    # Memungkinkan akses langsung melalui IP LAN
    ServerAlias 192.168.90.2

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


# ==========================================================================
# 3/5 SIMPAN
# ==========================================================================

Write-Host '[3/5] Menyimpan konfigurasi...'


$Utf8NoBom =
    New-Object System.Text.UTF8Encoding($false)


[System.IO.File]::WriteAllText(
    $VhostsFile,
    $Config,
    $Utf8NoBom
)


Write-Host '[OK] Konfigurasi disimpan.'
Write-Host ''


# ==========================================================================
# 4/5 APACHE SYNTAX CHECK
# ==========================================================================

Write-Host '[4/5] Memeriksa syntax Apache...'
Write-Host ''


& $ApacheExe -t


if ($LASTEXITCODE -ne 0) {

    Write-Host ''
    Write-Host '[ERROR] Konfigurasi Apache tidak valid.'
    Write-Host '[INFO] Mengembalikan konfigurasi sebelumnya...'

    Copy-Item `
        -LiteralPath $BackupFile `
        -Destination $VhostsFile `
        -Force

    throw 'Hardening dibatalkan. Konfigurasi lama telah dikembalikan.'
}


Write-Host ''
Write-Host '[OK] Syntax Apache valid.'
Write-Host ''


# ==========================================================================
# 5/5 DUMP VHOST
# ==========================================================================

Write-Host '[5/5] Verifikasi VirtualHost...'
Write-Host ''


& $ApacheExe -S


if ($LASTEXITCODE -ne 0) {

    Write-Host ''
    Write-Host '[ERROR] Apache VirtualHost verification gagal.'
    Write-Host '[INFO] Mengembalikan konfigurasi sebelumnya...'

    Copy-Item `
        -LiteralPath $BackupFile `
        -Destination $VhostsFile `
        -Force

    throw 'Hardening dibatalkan.'
}


Write-Host ''
Write-Host '============================================================'
Write-Host '[SUCCESS] APACHE HARDENING CONFIG BERHASIL'
Write-Host '============================================================'
Write-Host ''
Write-Host 'Unknown Host : DENY'
Write-Host 'localhost    : LOCAL ONLY'
Write-Host 'App Host     : inventory-jaringan.local'
Write-Host 'LAN IP       : 192.168.90.2'
Write-Host ''
Write-Host "Backup       : $BackupFile"
Write-Host ''
Write-Host 'PENTING:'
Write-Host 'Apache harus direstart melalui XAMPP Control Panel.'
Write-Host 'Public Internet belum diuji.'
Write-Host ''