@echo off
setlocal

cd /d "%~dp0"

echo.
echo ============================================================
echo  INVENTORY JARINGAN - VERIFY RESTORE
echo ============================================================
echo.
echo Backup akan diuji pada database sementara.
echo Database utama inventory_db tidak akan direstore.
echo.

powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\verify-restore.ps1"

if errorlevel 1 (
    echo.
    echo [ERROR] Restore verification gagal.
    echo.
    pause
    exit /b 1
)

echo.
echo [OK] Restore verification selesai.
echo.
pause

endlocal