@echo off
echo ========================================================
echo           MediVerse - Smart Healthcare Platform
echo ========================================================
echo.

where php >nul 2>nul
if %errorlevel% equ 0 (
    set PHP_CMD=php
) else (
    set PHP_CMD="C:\Users\LENOVO\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"
)

echo Starting PHP Server on http://localhost:8000 ...
echo Press Ctrl+C anytime to stop the server.
echo.
start http://localhost:8000
%PHP_CMD% -S localhost:8000 -c "%~dp0php.ini" -t "%~dp0htdocs"
pause
