
$ErrorActionPreference = 'Stop'

# ==========================================================================
# INVENTORY JARINGAN - VERIFY RESTORE
# Windows PowerShell 5.1
# ==========================================================================

# FITUR:
# - Membaca konfigurasi database dari .env
# - Mencari backup ZIP terbaru
# - Extract backup ke folder sementara
# - Memeriksa keamanan SQL
# - Membuat database sementara dengan nama unik
# - Import SQL hanya ke database sementara
# - Memverifikasi 15 tabel
# - Menghitung jumlah data setiap tabel
# - Menghapus database sementara
# - Mengembalikan MYSQL_PWD
#
# PENTING:
# Database produksi tidak boleh menjadi target import.
# Script ini tidak mengubah database inventory_db.
# ==========================================================================


# ==========================================================================
# 1. KONFIGURASI
# ==========================================================================

$ProjectRoot = (
    Resolve-Path (Join-Path $PSScriptRoot '..')
).Path

$EnvFile = Join-Path $ProjectRoot '.env'

$MySql = 'C:\xampp\mysql\bin\mysql.exe'

$Documents = [Environment]::GetFolderPath('MyDocuments')

$BackupRoot = Join-Path $Documents 'InventoryJaringanBackups'

$RequiredTables = @(
    'users',
    'kategoris',
    'barangs',
    'barang_masuks',
    'barang_keluars',
    'teknisis',
    'activity_logs',
    'migrations',
    'cache',
    'cache_locks',
    'failed_jobs',
    'job_batches',
    'jobs',
    'password_reset_tokens',
    'sessions'
)


# ==========================================================================
# 2. HEADER
# ==========================================================================

Write-Host ''
Write-Host '============================================================'
Write-Host ' INVENTORY JARINGAN - VERIFY RESTORE'
Write-Host '============================================================'
Write-Host ''


# ==========================================================================
# 3. VALIDASI FILE
# ==========================================================================

if (-not (Test-Path -LiteralPath $EnvFile -PathType Leaf)) {
    throw ".env tidak ditemukan: $EnvFile"
}

if (-not (Test-Path -LiteralPath $MySql -PathType Leaf)) {
    throw "mysql.exe tidak ditemukan: $MySql"
}

if (-not (Test-Path -LiteralPath $BackupRoot -PathType Container)) {
    throw "Folder backup tidak ditemukan: $BackupRoot"
}


# ==========================================================================
# 4. FUNCTION MEMBACA .ENV
# ==========================================================================

function Get-EnvValue {

    param(
        [Parameter(Mandatory = $true)]
        [string]$Key
    )

    $Pattern = '^\s*' + [regex]::Escape($Key) + '\s*='

    $Line = Get-Content -LiteralPath $EnvFile |
        Where-Object {
            $_ -match $Pattern
        } |
        Select-Object -First 1

    if ($null -eq $Line) {
        return $null
    }

    $Parts = $Line -split '=', 2

    if ($Parts.Count -lt 2) {
        return $null
    }

    $Value = $Parts[1].Trim()

    if (
        ($Value.StartsWith('"') -and $Value.EndsWith('"')) -or
        ($Value.StartsWith("'") -and $Value.EndsWith("'"))
    ) {
        $Value = $Value.Substring(1, $Value.Length - 2)
    }

    return $Value
}


# ==========================================================================
# 5. FUNCTION MENJALANKAN MYSQL
# ==========================================================================

function Invoke-MySql {

    param(
        [Parameter(Mandatory = $true)]
        [string[]]$Arguments,

        [string]$InputFile
    )

    $StartInfo = New-Object System.Diagnostics.ProcessStartInfo

    $StartInfo.FileName = $MySql

    $QuotedArguments = foreach ($Argument in $Arguments) {
        '"' + ([string]$Argument).Replace('"', '\"') + '"'
    }

    $StartInfo.Arguments = $QuotedArguments -join ' '

    $StartInfo.UseShellExecute = $false
    $StartInfo.CreateNoWindow = $true

    $StartInfo.RedirectStandardInput = $true
    $StartInfo.RedirectStandardOutput = $true
    $StartInfo.RedirectStandardError = $true

    $Process = New-Object System.Diagnostics.Process

    $Process.StartInfo = $StartInfo

    if (-not $Process.Start()) {
        throw 'Gagal menjalankan mysql.exe.'
    }

    $OutputTask = $Process.StandardOutput.ReadToEndAsync()
    $ErrorTask = $Process.StandardError.ReadToEndAsync()

    $StreamError = $null

    try {

        if (-not [string]::IsNullOrWhiteSpace($InputFile)) {

            $FileStream = [System.IO.File]::OpenRead($InputFile)

            try {

                $FileStream.CopyTo(
                    $Process.StandardInput.BaseStream
                )

                $Process.StandardInput.BaseStream.Flush()

            }
            finally {

                $FileStream.Dispose()
            }
        }

    }
    catch {

        $StreamError = $_.Exception.Message

        try {
            $Process.Kill()
        }
        catch {
        }

    }
    finally {

        try {
            $Process.StandardInput.Close()
        }
        catch {
        }
    }

    $Process.WaitForExit()

    $ExitCode = $Process.ExitCode

    $Output = $OutputTask.Result
    $ErrorOutput = $ErrorTask.Result

    $Process.Dispose()

    if ($null -ne $StreamError) {
        throw "Gagal mengirim file SQL ke MySQL: $StreamError"
    }

    if ($ExitCode -ne 0) {

        $Details = $ErrorOutput

        if ([string]::IsNullOrWhiteSpace($Details)) {
            $Details = $Output
        }

        throw "mysql.exe gagal (Exit Code: $ExitCode). $Details"
    }

    return ([string]$Output).Trim()
}


# ==========================================================================
# 6. DATABASE CONFIGURATION
# ==========================================================================

$DbConnection = Get-EnvValue 'DB_CONNECTION'
$DbHost = Get-EnvValue 'DB_HOST'
$DbPort = Get-EnvValue 'DB_PORT'
$DbDatabase = Get-EnvValue 'DB_DATABASE'
$DbUsername = Get-EnvValue 'DB_USERNAME'
$DbPassword = Get-EnvValue 'DB_PASSWORD'


if ($DbConnection -ne 'mysql') {
    throw 'DB_CONNECTION harus mysql.'
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

if ($null -eq $DbPassword) {
    $DbPassword = ''
}


Write-Host '[OK] Konfigurasi database berhasil dibaca.'
Write-Host "[INFO] Database produksi: $DbDatabase"


# ==========================================================================
# 7. MENCARI BACKUP TERBARU
# ==========================================================================

$BackupFile = Get-ChildItem `
    -LiteralPath $BackupRoot `
    -Filter 'inventory_backup_*.zip' `
    -File |
    Sort-Object LastWriteTime -Descending |
    Select-Object -First 1


if ($null -eq $BackupFile) {
    throw 'Tidak ditemukan file inventory_backup_*.zip.'
}

Write-Host "[INFO] Backup ditemukan: $($BackupFile.FullName)"


# ==========================================================================
# 8. MEMBUAT IDENTITAS DATABASE SEMENTARA
# ==========================================================================

$Timestamp = Get-Date -Format 'yyyyMMdd_HHmmss_fff'

$RandomSuffix = [guid]::NewGuid().ToString('N').Substring(0, 8)

$VerifyDatabase = "inventory_restore_verify_${Timestamp}_$RandomSuffix"

if ($VerifyDatabase -eq $DbDatabase) {
    throw 'SAFETY ERROR: database sementara sama dengan database utama.'
}

if ($VerifyDatabase -notmatch '^[A-Za-z0-9_]+$') {
    throw 'SAFETY ERROR: nama database sementara tidak valid.'
}

$ExtractDir = Join-Path $env:TEMP $VerifyDatabase

if (Test-Path -LiteralPath $ExtractDir) {
    throw 'Folder temporary sudah ada. Proses dibatalkan.'
}


# ==========================================================================
# 9. SIMPAN MYSQL_PWD
# ==========================================================================

$OldMysqlPwd = [Environment]::GetEnvironmentVariable(
    'MYSQL_PWD',
    'Process'
)

$CommonArguments = @(
    "--host=$DbHost",
    "--port=$DbPort",
    "--user=$DbUsername",
    '--protocol=TCP',
    '--connect-timeout=10',
    '--default-character-set=utf8mb4'
)


# ==========================================================================
# 10. STATUS PROSES
# ==========================================================================

$DatabaseCreated = $false

$VerificationSucceeded = $false

$FailureMessage = $null

$CleanupErrors = @()


# ==========================================================================
# 11. PROSES UTAMA
# ==========================================================================

try {

    # ======================================================================
    # MYSQL PASSWORD HANYA UNTUK PROSES INI
    # ======================================================================

    $env:MYSQL_PWD = $DbPassword


    # ======================================================================
    # TAHAP 1: EXTRACT BACKUP
    # ======================================================================

    Write-Host ''
    Write-Host '[1/5] Extract backup...'

    New-Item `
        -ItemType Directory `
        -Path $ExtractDir `
        -Force |
        Out-Null

    Expand-Archive `
        -LiteralPath $BackupFile.FullName `
        -DestinationPath $ExtractDir `
        -Force

    $SqlFile = Join-Path $ExtractDir 'inventory_db.sql'

    if (-not (Test-Path -LiteralPath $SqlFile -PathType Leaf)) {
        throw 'inventory_db.sql tidak ditemukan dalam backup.'
    }

    $SqlLength = (Get-Item -LiteralPath $SqlFile).Length

    if ($SqlLength -le 0) {
        throw 'inventory_db.sql kosong.'
    }

    Write-Host "[OK] SQL ditemukan: $SqlLength bytes"


    # ======================================================================
    # TAHAP 2: PEMERIKSAAN KEAMANAN SQL
    # ======================================================================

    Write-Host ''
    Write-Host '[SAFETY] Memeriksa isi SQL backup...'

    $QualifiedDatabasePattern =
        '(?i)(?<![A-Za-z0-9_$])`?' +
        [regex]::Escape($DbDatabase) +
        '`?\s*\.'

    $UnsafeSqlPatterns = @(

        # USE DATABASE
        '(?i)^\s*(?:/\*!\d{1,6}\s*)?USE\s+',

        # CREATE / DROP / ALTER DATABASE
        '(?i)^\s*(?:/\*!\d{1,6}\s*)?(?:CREATE|DROP|ALTER)\s+(?:DATABASE|SCHEMA)\b',

        # GRANT / REVOKE
        '(?i)^\s*(?:/\*!\d{1,6}\s*)?(?:GRANT|REVOKE)\b',

        # CREATE / ALTER / DROP USER
        '(?i)^\s*(?:/\*!\d{1,6}\s*)?(?:CREATE|ALTER|DROP)\s+USER\b',

        # SET GLOBAL
        '(?i)^\s*(?:/\*!\d{1,6}\s*)?SET\s+(?:@@GLOBAL|GLOBAL)\b',

        # DATABASE PRODUKSI SECARA LANGSUNG
        $QualifiedDatabasePattern
    )

    $UnsafeSqlMatches = @(
        Select-String `
            -LiteralPath $SqlFile `
            -Pattern $UnsafeSqlPatterns `
            -ErrorAction Stop
    )

    if ($UnsafeSqlMatches.Count -gt 0) {

        $UnsafeLines = (
            $UnsafeSqlMatches |
            Select-Object -ExpandProperty LineNumber -Unique
        ) -join ', '

        throw (
            'SAFETY ERROR: SQL mengandung perintah atau referensi ' +
            'database yang tidak diizinkan. Import dibatalkan. ' +
            "Baris: $UnsafeLines"
        )
    }

    Write-Host '[OK] Pemeriksaan keamanan SQL berhasil.'


    # ======================================================================
    # TAHAP 3: PERIKSA DATABASE SEMENTARA
    # ======================================================================

    Write-Host ''
    Write-Host '[2/5] Memeriksa nama database sementara...'

    $ExistsSql = (
        "SELECT COUNT(*) FROM information_schema.schemata " +
        "WHERE schema_name='$VerifyDatabase';"
    )

    $ExistingDatabase = Invoke-MySql `
        -Arguments (
            $CommonArguments + @(
                '--batch',
                '--skip-column-names',
                "--execute=$ExistsSql"
            )
        )

    if ($ExistingDatabase.Trim() -ne '0') {
        throw 'Database sementara sudah ada. Proses dibatalkan.'
    }


    # ======================================================================
    # BUAT DATABASE SEMENTARA
    # ======================================================================

    $CreateSql = (
        "CREATE DATABASE $VerifyDatabase " +
        "CHARACTER SET utf8mb4 " +
        "COLLATE utf8mb4_unicode_ci;"
    )

    $null = Invoke-MySql `
        -Arguments (
            $CommonArguments + @(
                "--execute=$CreateSql"
            )
        )

    $DatabaseCreated = $true

    Write-Host "[OK] Database sementara dibuat: $VerifyDatabase"


    # ======================================================================
    # TAHAP 4: IMPORT SQL KE DATABASE SEMENTARA
    # ======================================================================

    Write-Host ''
    Write-Host '[3/5] Import SQL ke database sementara...'

    $ImportArguments = $CommonArguments + @(
        '--binary-mode=1',
        "--database=$VerifyDatabase"
    )

    $null = Invoke-MySql `
        -Arguments $ImportArguments `
        -InputFile $SqlFile

    Write-Host '[OK] Import database berhasil.'


    # ======================================================================
    # TAHAP 5: VERIFIKASI 15 TABEL
    # ======================================================================

    Write-Host ''
    Write-Host '[4/5] Memverifikasi 15 tabel database...'
    Write-Host ''

    foreach ($Table in $RequiredTables) {

        # ==============================================================
        # CEK KEBERADAAN TABEL
        # ==============================================================

        $CheckSql = (
            "SELECT COUNT(*) FROM information_schema.tables " +
            "WHERE table_schema='$VerifyDatabase' " +
            "AND table_name='$Table';"
        )

        $ExistsText = Invoke-MySql `
            -Arguments (
                $CommonArguments + @(
                    '--batch',
                    '--skip-column-names',
                    "--execute=$CheckSql"
                )
            )

        if ($ExistsText.Trim() -ne '1') {
            throw "Tabel wajib tidak ditemukan: $Table"
        }


        # ==============================================================
        # HITUNG JUMLAH DATA
        # ==============================================================

        $CountSql = "SELECT COUNT(*) FROM $Table;"

        $CountText = Invoke-MySql `
            -Arguments (
                $CommonArguments + @(
                    '--batch',
                    '--skip-column-names',
                    "--database=$VerifyDatabase",
                    "--execute=$CountSql"
                )
            )

        if ($CountText -notmatch '^\d+$') {
            throw "Hasil perhitungan tabel tidak valid: $Table"
        }

        Write-Host "[OK] $Table : $CountText row"
    }


    # ======================================================================
    # SAFETY CHECK DATABASE PRODUKSI
    # ======================================================================

    Write-Host ''

    if ($VerifyDatabase -eq $DbDatabase) {
        throw 'SAFETY ERROR: database utama terdeteksi sebagai target.'
    }

    Write-Host "[OK] Database produksi tetap: $DbDatabase"

    Write-Host "[OK] Database pengujian: $VerifyDatabase"

    $VerificationSucceeded = $true

}
catch {

    $FailureMessage = $_.Exception.Message

    Write-Host ''
    Write-Host '============================================================'
    Write-Host '[ERROR] RESTORE VERIFICATION GAGAL'
    Write-Host '============================================================'
    Write-Host ''

    Write-Host $FailureMessage
}


# ==========================================================================
# 12. CLEANUP
# ==========================================================================

finally {

    Write-Host ''
    Write-Host '============================================================'
    Write-Host '[CLEANUP] MEMULAI PEMBERSIHAN'
    Write-Host '============================================================'


    # ======================================================================
    # HAPUS DATABASE SEMENTARA
    # ======================================================================

    if ($DatabaseCreated) {

        Write-Host ''
        Write-Host '[CLEANUP] Menghapus database sementara...'

        try {

            $DropSql = "DROP DATABASE IF EXISTS $VerifyDatabase;"

            $null = Invoke-MySql `
                -Arguments (
                    $CommonArguments + @(
                        "--execute=$DropSql"
                    )
                )

            Write-Host "[OK] Database sementara dihapus: $VerifyDatabase"

        }
        catch {

            $CleanupErrors += $_.Exception.Message

            Write-Host '[WARNING] Database sementara gagal dihapus.'

            Write-Host "Nama database: $VerifyDatabase"
        }
    }


    # ======================================================================
    # HAPUS FOLDER TEMPORARY
    # ======================================================================

    if (Test-Path -LiteralPath $ExtractDir) {

        try {

            Remove-Item `
                -LiteralPath $ExtractDir `
                -Recurse `
                -Force `
                -ErrorAction Stop

            Write-Host '[OK] Folder temporary dihapus.'

        }
        catch {

            $CleanupErrors += $_.Exception.Message

            Write-Host '[WARNING] Folder temporary gagal dihapus.'
        }
    }


    # ======================================================================
    # KEMBALIKAN MYSQL_PWD
    # ======================================================================

    if ($null -eq $OldMysqlPwd) {

        Remove-Item `
            Env:MYSQL_PWD `
            -ErrorAction SilentlyContinue
    }
    else {

        $env:MYSQL_PWD = $OldMysqlPwd
    }

    Write-Host '[OK] MYSQL_PWD dikembalikan.'

}


# ==========================================================================
# 13. HASIL AKHIR
# ==========================================================================

Write-Host ''

if (-not $VerificationSucceeded) {

    if ($CleanupErrors.Count -gt 0) {

        Write-Host '[WARNING] Ada masalah saat cleanup:'

        $CleanupErrors | ForEach-Object {
            Write-Host $_
        }
    }

    throw $FailureMessage
}

if ($CleanupErrors.Count -gt 0) {

    Write-Host '[WARNING] Verifikasi berhasil, tetapi cleanup belum tuntas.'

    $CleanupErrors | ForEach-Object {
        Write-Host $_
    }

    throw 'Restore verification belum dinyatakan selesai karena cleanup gagal.'
}


Write-Host '============================================================'
Write-Host '[SUCCESS] RESTORE VERIFICATION BERHASIL'
Write-Host '============================================================'
Write-Host ''

Write-Host "Backup          : $($BackupFile.Name)"
Write-Host "Database utama  : $DbDatabase"
Write-Host "SQL size        : $SqlLength bytes"
Write-Host "Jumlah tabel    : $($RequiredTables.Count)"
Write-Host 'Database utama  : TIDAK DIUBAH'
Write-Host 'Database test   : SUDAH DIHAPUS'
Write-Host 'Temporary files : SUDAH DIBERSIHKAN'

Write-Host ''
Write-Host '============================================================'
Write-Host '[OK] VERIFY RESTORE SELESAI'
Write-Host '============================================================'
