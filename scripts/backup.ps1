$ErrorActionPreference = 'Stop'

# ==========================================================================
# INVENTORY JARINGAN - BACKUP
# ==========================================================================

$ProjectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$EnvFile = Join-Path $ProjectRoot '.env'
$MySqlDump = 'C:\xampp\mysql\bin\mysqldump.exe'

$Documents = [Environment]::GetFolderPath('MyDocuments')
$BackupRoot = Join-Path $Documents 'InventoryJaringanBackups'

$Timestamp = Get-Date -Format 'yyyyMMdd_HHmmss'
$BackupName = "inventory_backup_$Timestamp"
$WorkDir = Join-Path $BackupRoot $BackupName
$ZipFile = Join-Path $BackupRoot ($BackupName + '.zip')


Write-Host ''
Write-Host '============================================================'
Write-Host ' INVENTORY JARINGAN - BACKUP'
Write-Host '============================================================'
Write-Host ''


# ==========================================================================
# VALIDASI DASAR
# ==========================================================================

if (-not (Test-Path -LiteralPath $EnvFile)) {
    throw ".env tidak ditemukan: $EnvFile"
}

if (-not (Test-Path -LiteralPath $MySqlDump)) {
    throw "mysqldump tidak ditemukan: $MySqlDump"
}


# ==========================================================================
# BACA NILAI DARI .ENV
# ==========================================================================

function Get-EnvValue {
    param(
        [Parameter(Mandatory = $true)]
        [string]$Key
    )

    $pattern = '^\s*' + [regex]::Escape($Key) + '\s*='

    $line = Get-Content -LiteralPath $EnvFile |
        Where-Object { $_ -match $pattern } |
        Select-Object -First 1

    if ($null -eq $line) {
        return $null
    }

    $parts = $line -split '=', 2

    if ($parts.Count -lt 2) {
        return $null
    }

    $value = $parts[1].Trim()

    if ($value.Length -ge 2) {
        $firstChar = [int][char]$value[0]
        $lastChar = [int][char]$value[$value.Length - 1]

        $isDoubleQuoted = ($firstChar -eq 34 -and $lastChar -eq 34)
        $isSingleQuoted = ($firstChar -eq 39 -and $lastChar -eq 39)

        if ($isDoubleQuoted -or $isSingleQuoted) {
            $value = $value.Substring(1, $value.Length - 2)
        }
    }

    return $value
}


# ==========================================================================
# DATABASE CONFIG
# ==========================================================================

$DbConnection = Get-EnvValue 'DB_CONNECTION'
$DbHost = Get-EnvValue 'DB_HOST'
$DbPort = Get-EnvValue 'DB_PORT'
$DbDatabase = Get-EnvValue 'DB_DATABASE'
$DbUsername = Get-EnvValue 'DB_USERNAME'
$DbPassword = Get-EnvValue 'DB_PASSWORD'


if ($DbConnection -ne 'mysql') {
    throw "DB_CONNECTION harus mysql. Nilai sekarang: $DbConnection"
}

if ([string]::IsNullOrWhiteSpace($DbHost)) {
    $DbHost = '127.0.0.1'
}

if ([string]::IsNullOrWhiteSpace($DbPort)) {
    $DbPort = '3306'
}

if ([string]::IsNullOrWhiteSpace($DbDatabase)) {
    throw 'DB_DATABASE kosong.'
}

if ([string]::IsNullOrWhiteSpace($DbUsername)) {
    throw 'DB_USERNAME kosong.'
}


# ==========================================================================
# SIAPKAN FOLDER BACKUP
# ==========================================================================

New-Item -ItemType Directory -Path $BackupRoot -Force | Out-Null

if (Test-Path -LiteralPath $WorkDir) {
    Remove-Item -LiteralPath $WorkDir -Recurse -Force
}

New-Item -ItemType Directory -Path $WorkDir -Force | Out-Null


Write-Host "[INFO] Database : $DbDatabase"
Write-Host "[INFO] Host     : $DbHost`:$DbPort"
Write-Host "[INFO] User     : $DbUsername"
Write-Host "[INFO] Backup   : $ZipFile"
Write-Host ''


# ==========================================================================
# SIMPAN MYSQL_PWD LAMA
# ==========================================================================

$OldMysqlPwd = [Environment]::GetEnvironmentVariable(
    'MYSQL_PWD',
    'Process'
)


try {

    # ======================================================================
    # PASSWORD MYSQL HANYA UNTUK PROCESS INI
    # ======================================================================

    if ($null -eq $DbPassword) {
        $DbPassword = ''
    }

    $env:MYSQL_PWD = $DbPassword


    # ======================================================================
    # 1/5 BACKUP DATABASE
    # ======================================================================

    Write-Host '[1/5] Backup database...'


    $SqlFile = Join-Path $WorkDir 'inventory_db.sql'
    $DumpErrorFile = Join-Path $WorkDir 'mysqldump-error.txt'


    $DumpArguments = @(
        "--host=$DbHost"
        "--port=$DbPort"
        "--user=$DbUsername"
        '--single-transaction'
        '--quick'
        '--routines'
        '--triggers'
        '--events'
        '--hex-blob'
        '--default-character-set=utf8mb4'
        '--add-drop-table'
        "--result-file=$SqlFile"
        $DbDatabase
    )


    $process = Start-Process `
        -FilePath $MySqlDump `
        -ArgumentList $DumpArguments `
        -RedirectStandardError $DumpErrorFile `
        -NoNewWindow `
        -Wait `
        -PassThru


    if ($process.ExitCode -ne 0) {
        $dumpError = ''

        if (Test-Path -LiteralPath $DumpErrorFile) {
            $dumpError = Get-Content -LiteralPath $DumpErrorFile -Raw
        }

        throw "mysqldump gagal. $dumpError"
    }


    if (-not (Test-Path -LiteralPath $SqlFile)) {
        throw 'inventory_db.sql tidak berhasil dibuat.'
    }


    $SqlLength = (Get-Item -LiteralPath $SqlFile).Length

    if ($SqlLength -le 0) {
        throw 'inventory_db.sql kosong.'
    }


    Remove-Item `
        -LiteralPath $DumpErrorFile `
        -Force `
        -ErrorAction SilentlyContinue


    Write-Host "[OK] Database berhasil dibackup ($SqlLength bytes)."


    # ======================================================================
    # 2/5 BACKUP .ENV
    # ======================================================================

    Write-Host '[2/5] Backup konfigurasi .env...'


    $ConfigDir = Join-Path $WorkDir 'config'

    New-Item `
        -ItemType Directory `
        -Path $ConfigDir `
        -Force |
        Out-Null


    Copy-Item `
        -LiteralPath $EnvFile `
        -Destination (Join-Path $ConfigDir '.env') `
        -Force


    Write-Host '[OK] config\.env berhasil dibackup.'


    # ======================================================================
    # 3/5 BACKUP STORAGE
    # ======================================================================

    Write-Host '[3/5] Backup storage aplikasi...'


    $StorageSource = Join-Path $ProjectRoot 'storage\app'
    $StorageDestination = Join-Path $WorkDir 'storage'


    if (Test-Path -LiteralPath $StorageSource) {

        New-Item `
            -ItemType Directory `
            -Path $StorageDestination `
            -Force |
            Out-Null


        Copy-Item `
            -LiteralPath $StorageSource `
            -Destination $StorageDestination `
            -Recurse `
            -Force


        Write-Host '[OK] storage\app berhasil dibackup.'
    }
    else {
        Write-Host '[WARNING] storage\app tidak ditemukan.'
    }


    # ======================================================================
    # BACKUP INFO
    # ======================================================================

    $ManifestFile = Join-Path $WorkDir 'BACKUP_INFO.txt'


    $Manifest = @"
INVENTORY JARINGAN BACKUP
=========================

Tanggal Backup : $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')
Project        : $ProjectRoot
Database       : $DbDatabase
DB Host        : $DbHost
DB Port        : $DbPort
DB User        : $DbUsername

Database Dump  : inventory_db.sql
Database Size  : $SqlLength bytes

Konfigurasi    : config\.env
Storage        : storage\app

Catatan:
- Database aktif tidak dihapus atau diubah.
- File aplikasi aktif tidak dihapus.
- Password database tidak ditulis ke BACKUP_INFO.txt.
- Backup disimpan di luar folder htdocs.
- public\storage dapat dibuat kembali dengan:
  php artisan storage:link
"@


    Set-Content `
        -LiteralPath $ManifestFile `
        -Value $Manifest `
        -Encoding UTF8


    # ======================================================================
    # 4/5 BUAT ZIP
    # ======================================================================

    Write-Host '[4/5] Membuat ZIP backup...'


    if (Test-Path -LiteralPath $ZipFile) {
        Remove-Item -LiteralPath $ZipFile -Force
    }


    Compress-Archive `
        -Path (Join-Path $WorkDir '*') `
        -DestinationPath $ZipFile `
        -CompressionLevel Optimal


    if (-not (Test-Path -LiteralPath $ZipFile)) {
        throw 'ZIP backup tidak berhasil dibuat.'
    }


    $ZipLength = (Get-Item -LiteralPath $ZipFile).Length

    if ($ZipLength -le 0) {
        throw 'ZIP backup kosong.'
    }


    # ======================================================================
    # 5/5 VERIFIKASI ZIP
    # ======================================================================

    Write-Host '[5/5] Verifikasi isi ZIP...'


    Add-Type -AssemblyName System.IO.Compression.FileSystem


    $zip = [System.IO.Compression.ZipFile]::OpenRead(
        $ZipFile
    )


    try {

        $sqlEntry = $null
        $envEntry = $null
        $storageEntry = $null
        $manifestEntry = $null


        foreach ($entry in $zip.Entries) {

            $name = $entry.FullName

            $name = $name.Replace(
                [char]92,
                [char]47
            )


            if ($name -eq 'inventory_db.sql') {
                $sqlEntry = $entry
            }


            if ($name -eq 'config/.env') {
                $envEntry = $entry
            }


            if ($name.StartsWith('storage/app/')) {
                $storageEntry = $entry
            }


            if ($name -eq 'BACKUP_INFO.txt') {
                $manifestEntry = $entry
            }
        }


        if ($null -eq $sqlEntry) {
            throw 'Verifikasi gagal: inventory_db.sql tidak ditemukan.'
        }


        if ($sqlEntry.Length -le 0) {
            throw 'Verifikasi gagal: inventory_db.sql kosong.'
        }


        if ($null -eq $envEntry) {
            throw 'Verifikasi gagal: config\.env tidak ditemukan.'
        }


        if ($envEntry.Length -le 0) {
            throw 'Verifikasi gagal: config\.env kosong.'
        }


        if ($null -eq $storageEntry) {
            throw 'Verifikasi gagal: storage\app tidak ditemukan.'
        }


        if ($null -eq $manifestEntry) {
            throw 'Verifikasi gagal: BACKUP_INFO.txt tidak ditemukan.'
        }


        Write-Host '[OK] inventory_db.sql terverifikasi.'
        Write-Host '[OK] config\.env terverifikasi.'
        Write-Host '[OK] storage\app terverifikasi.'
        Write-Host '[OK] BACKUP_INFO.txt terverifikasi.'
    }
    finally {
        $zip.Dispose()
    }


    # ======================================================================
    # BERSIHKAN WORKDIR
    # ======================================================================

    Remove-Item `
        -LiteralPath $WorkDir `
        -Recurse `
        -Force


    # ======================================================================
    # SUCCESS
    # ======================================================================

    $ZipSizeMB = [math]::Round(
        $ZipLength / 1MB,
        2
    )


    Write-Host ''
    Write-Host '============================================================'
    Write-Host '[SUCCESS] BACKUP BERHASIL DAN TERVERIFIKASI'
    Write-Host '============================================================'
    Write-Host ''
    Write-Host "File : $ZipFile"
    Write-Host "Size : $ZipSizeMB MB"
    Write-Host ''
}
catch {

    Write-Host ''
    Write-Host '============================================================'
    Write-Host '[ERROR] BACKUP GAGAL'
    Write-Host '============================================================'
    Write-Host ''
    Write-Host $_.Exception.Message
    Write-Host ''


    if (Test-Path -LiteralPath $ZipFile) {
        Remove-Item `
            -LiteralPath $ZipFile `
            -Force `
            -ErrorAction SilentlyContinue
    }


    throw
}
finally {

    # ======================================================================
    # KEMBALIKAN MYSQL_PWD SEPERTI SEMULA
    # ======================================================================

    if ($null -eq $OldMysqlPwd) {
        Remove-Item Env:MYSQL_PWD -ErrorAction SilentlyContinue
    }
    else {
        $env:MYSQL_PWD = $OldMysqlPwd
    }


    # ======================================================================
    # BERSIHKAN WORKDIR JIKA GAGAL
    # ======================================================================

    if (Test-Path -LiteralPath $WorkDir) {
        Remove-Item `
            -LiteralPath $WorkDir `
            -Recurse `
            -Force `
            -ErrorAction SilentlyContinue
    }
}