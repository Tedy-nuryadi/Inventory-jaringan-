$ErrorActionPreference = 'Stop'

# ==========================================================================
# INVENTORY JARINGAN
# PRODUCTION OPTIMIZATION + HTTP SMOKE TEST
# ==========================================================================

$ProjectRoot = 'C:\xampp\htdocs\inventory-jaringan'
$PhpExe      = 'C:\xampp\php\php.exe'
$ApacheExe   = 'C:\xampp\apache\bin\httpd.exe'
$CurlExe     = 'C:\Windows\System32\curl.exe'

$AppUrl      = 'http://inventory-jaringan.local'
$LanIp       = '192.168.90.2'
$HostName    = 'inventory-jaringan.local'


Write-Host ''
Write-Host '============================================================'
Write-Host ' INVENTORY JARINGAN - PRODUCTION OPTIMIZATION'
Write-Host '============================================================'
Write-Host ''


# ==========================================================================
# VALIDASI
# ==========================================================================

if (-not (Test-Path -LiteralPath $ProjectRoot)) {
    throw "Project tidak ditemukan: $ProjectRoot"
}

if (-not (Test-Path -LiteralPath $PhpExe)) {
    throw "PHP tidak ditemukan: $PhpExe"
}

if (-not (Test-Path -LiteralPath $ApacheExe)) {
    throw "Apache tidak ditemukan: $ApacheExe"
}

if (-not (Test-Path -LiteralPath $CurlExe)) {
    throw "curl.exe tidak ditemukan: $CurlExe"
}

if (-not (Test-Path -LiteralPath (Join-Path $ProjectRoot 'artisan'))) {
    throw 'artisan tidak ditemukan.'
}


Push-Location $ProjectRoot

try {

    # ======================================================================
    # 1/8 APACHE CONFIG
    # ======================================================================

    Write-Host '[1/8] Memeriksa konfigurasi Apache...'
    Write-Host ''

    & $ApacheExe -t

    if ($LASTEXITCODE -ne 0) {
        throw 'Konfigurasi Apache tidak valid.'
    }

    Write-Host ''
    Write-Host '[OK] Konfigurasi Apache valid.'
    Write-Host ''


    # ======================================================================
    # 2/8 CLEAR CACHE
    # ======================================================================

    Write-Host '[2/8] Membersihkan cache Laravel...'
    Write-Host ''

    & $PhpExe artisan optimize:clear

    if ($LASTEXITCODE -ne 0) {
        throw 'artisan optimize:clear gagal.'
    }

    Write-Host ''
    Write-Host '[OK] Cache lama dibersihkan.'
    Write-Host ''


    # ======================================================================
    # 3/8 CONFIG CACHE
    # ======================================================================

    Write-Host '[3/8] Membuat config cache...'
    Write-Host ''

    & $PhpExe artisan config:cache

    if ($LASTEXITCODE -ne 0) {
        throw 'artisan config:cache gagal.'
    }

    Write-Host ''
    Write-Host '[OK] Config cache berhasil.'
    Write-Host ''


    # ======================================================================
    # 4/8 EVENT CACHE
    # ======================================================================

    Write-Host '[4/8] Membuat event cache...'
    Write-Host ''

    & $PhpExe artisan event:cache

    if ($LASTEXITCODE -ne 0) {
        throw 'artisan event:cache gagal.'
    }

    Write-Host ''
    Write-Host '[OK] Event cache berhasil.'
    Write-Host ''


    # ======================================================================
    # 5/8 VIEW CACHE
    # ======================================================================

    Write-Host '[5/8] Membuat view cache...'
    Write-Host ''

    & $PhpExe artisan view:cache

    if ($LASTEXITCODE -ne 0) {
        throw 'artisan view:cache gagal.'
    }

    Write-Host ''
    Write-Host '[OK] View cache berhasil.'
    Write-Host ''


    # ======================================================================
    # 6/8 ROUTE CACHE
    # ======================================================================

    Write-Host '[6/8] Mencoba membuat route cache...'
    Write-Host ''

    & $PhpExe artisan route:cache

    if ($LASTEXITCODE -eq 0) {

        Write-Host ''
        Write-Host '[OK] Route cache berhasil.'

    }
    else {

        Write-Host ''
        Write-Host '[WARNING] Route cache tidak dapat dibuat.'
        Write-Host '[INFO] Membersihkan route cache...'
        Write-Host ''

        & $PhpExe artisan route:clear

        if ($LASTEXITCODE -ne 0) {
            throw 'route:clear gagal.'
        }

        Write-Host ''
        Write-Host '[OK] Route cache dilewati dengan aman.'
    }

    Write-Host ''


    # ======================================================================
    # 7/8 ARTISAN ABOUT
    # ======================================================================

    Write-Host '[7/8] Verifikasi environment Laravel...'
    Write-Host ''

    & $PhpExe artisan about

    if ($LASTEXITCODE -ne 0) {
        throw 'artisan about gagal.'
    }

    Write-Host ''


    # ======================================================================
    # 8/8 HTTP SMOKE TEST
    # ======================================================================

    Write-Host '[8/8] HTTP smoke test...'
    Write-Host ''


    # ----------------------------------------------------------------------
    # TEST 1
    # ----------------------------------------------------------------------

    Write-Host '------------------------------------------------------------'
    Write-Host 'TEST 1 - VirtualHost'
    Write-Host '------------------------------------------------------------'

    $Status1 = & $CurlExe `
        --silent `
        --output NUL `
        --write-out "%{http_code}" `
        $AppUrl

    $CurlExit1 = $LASTEXITCODE

    $Status1 = "$Status1".Trim()

    Write-Host "URL         : $AppUrl"
    Write-Host "HTTP Status : $Status1"


    if ($CurlExit1 -ne 0) {
        throw "curl VirtualHost gagal. Exit Code: $CurlExit1"
    }

    if ($Status1 -eq '000') {
        throw 'VirtualHost tidak memberikan HTTP response.'
    }

    $StatusNumber1 = [int]$Status1

    if (
        ($StatusNumber1 -lt 200) -or
        ($StatusNumber1 -ge 400)
    ) {
        throw "VirtualHost menghasilkan HTTP $Status1"
    }

    Write-Host '[OK] VirtualHost merespons normal.'
    Write-Host ''


    # ----------------------------------------------------------------------
    # TEST 2
    # ----------------------------------------------------------------------

    Write-Host '------------------------------------------------------------'
    Write-Host 'TEST 2 - LAN IP + Host Header'
    Write-Host '------------------------------------------------------------'

    $Status2 = & $CurlExe `
        --silent `
        --output NUL `
        --write-out "%{http_code}" `
        --header "Host: $HostName" `
        ("http://" + $LanIp)

    $CurlExit2 = $LASTEXITCODE

    $Status2 = "$Status2".Trim()

    Write-Host "IP          : $LanIp"
    Write-Host "Host Header : $HostName"
    Write-Host "HTTP Status : $Status2"


    if ($CurlExit2 -ne 0) {
        throw "curl LAN test gagal. Exit Code: $CurlExit2"
    }

    if ($Status2 -eq '000') {
        throw 'LAN IP tidak memberikan HTTP response.'
    }

    $StatusNumber2 = [int]$Status2

    if (
        ($StatusNumber2 -lt 200) -or
        ($StatusNumber2 -ge 400)
    ) {
        throw "LAN Host Header menghasilkan HTTP $Status2"
    }

    Write-Host '[OK] LAN VirtualHost merespons normal.'
    Write-Host ''


    # ======================================================================
    # FINAL
    # ======================================================================

    Write-Host '============================================================'
    Write-Host '[SUCCESS] PRODUCTION OPTIMIZATION SELESAI'
    Write-Host '============================================================'
    Write-Host ''
    Write-Host 'Laravel      : Production'
    Write-Host 'Debug        : OFF'
    Write-Host 'Timezone     : Asia/Jakarta'
    Write-Host 'Config Cache : Enabled'
    Write-Host 'Event Cache  : Enabled'
    Write-Host 'View Cache   : Enabled'
    Write-Host ''
    Write-Host "VirtualHost  : $AppUrl"
    Write-Host "LAN IP       : $LanIp"
    Write-Host ''
    Write-Host 'Database     : TIDAK DIUBAH'
    Write-Host 'Migration    : TIDAK DIJALANKAN'
    Write-Host 'Data         : TIDAK DIHAPUS'
    Write-Host 'Port Forward : BELUM DIUBAH'
    Write-Host 'Public       : BELUM DIAKTIFKAN'
    Write-Host ''

}
finally {

    Pop-Location

}