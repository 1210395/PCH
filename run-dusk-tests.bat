@echo off
echo ============================================
echo  Palestine Creative Hub - Dusk Test Runner
echo ============================================
echo.

REM Check if ChromeDriver is running
tasklist /FI "IMAGENAME eq chromedriver.exe" 2>NUL | find /I "chromedriver.exe" >NUL
if %ERRORLEVEL% NEQ 0 (
    echo Starting ChromeDriver...
    start /B chromedriver --port=9515
    timeout /t 2 >NUL
) else (
    echo ChromeDriver is already running.
)

echo.
echo Running Dusk Tests...
echo.

REM Run all tests or a specific test file
if "%1"=="" (
    php artisan dusk --env=dusk.production
) else (
    php artisan dusk --env=dusk.production --filter=%1
)

echo.
echo ============================================
echo  Tests Complete! Screenshots in:
echo  tests/Browser/screenshots/
echo ============================================
pause
