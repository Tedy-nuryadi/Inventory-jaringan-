@echo off
setlocal

cd /d "%~dp0"

echo.
echo Menjalankan backup Inventory Jaringan...
echo.

powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\backup.ps1"

if errorlevel 1 (
    echo.
    echo [ERROR] Backup gagal.
    echo.
    pause
    exit /b 1
)

echo.
echo [OK] Backup selesai.
echo.
pause

endlocal