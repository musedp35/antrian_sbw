@echo off
echo ========================================
echo Setup antrian_sbw.test - Sistem Antrian SBW
echo ========================================
echo.

:: Tambahkan entry ke hosts file (jika belum ada)
findstr /C:"antrian_sbw.test" %WINDIR%\System32\drivers\etc\hosts >nul 2>&1
if %errorlevel% neq 0 (
    echo Adding antrian_sbw.test to hosts file...
    echo 127.0.0.1 antrian_sbw.test >> %WINDIR%\System32\drivers\etc\hosts
    echo Done!
) else (
    echo antrian_sbw.test already exists in hosts file.
)

echo.
echo ========================================
echo Setup completed!
echo ========================================
echo Buka browser dan akses: http://antrian_sbw.test
echo.
pause
