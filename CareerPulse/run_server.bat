@echo off
title CareerPulse Job Board Web Server
color 0b

echo ========================================================
echo          CareerPulse - Job Board ^& Career Portal
echo ========================================================
echo.

set PHP_BIN=C:\xampp\php\php.exe

if not exist "%PHP_BIN%" (
    where php >nul 2>&1
    if %errorlevel% equ 0 (
        set PHP_BIN=php
    ) else (
        echo [ERROR] PHP was not found in C:\xampp\php\php.exe or in system PATH.
        echo Please ensure XAMPP is installed or add PHP to your environment variables.
        pause
        exit /b 1
    )
)

echo [1/3] Using PHP: "%PHP_BIN%"
echo.

echo [2/3] Checking and initializing database...
"%PHP_BIN%" database\setup.php
echo.

echo [3/3] Starting web server at http://127.0.0.1:8000 ...
start "" "http://127.0.0.1:8000"

echo.
echo ========================================================
echo Server running at: http://localhost:8000
echo Press Ctrl+C in this window to stop the server.
echo ========================================================
echo.

"%PHP_BIN%" -S 127.0.0.1:8000 -t .
