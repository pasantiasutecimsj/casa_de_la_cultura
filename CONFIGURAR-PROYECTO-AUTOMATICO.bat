@echo off
setlocal EnableExtensions
chcp 65001 >nul
cd /d "%~dp0"

set "SETUP_PHP=php"
where php >nul 2>nul
if errorlevel 1 (
    if exist "%~dp0..\..\php\php.exe" (
        set "SETUP_PHP=%~dp0..\..\php\php.exe"
    ) else (
        echo [ERROR] PHP no esta disponible en PATH ni en la instalacion de XAMPP.
        goto :error
    )
)

set "SETUP_COMPOSER_BIN="
for /f "delims=" %%I in ('where.exe composer.bat 2^>nul') do if not defined SETUP_COMPOSER_BIN set "SETUP_COMPOSER_BIN=%%I"
if not defined SETUP_COMPOSER_BIN (
    echo [ERROR] Composer no esta disponible en PATH.
    goto :error
)

set "SETUP_NPM_BIN="
for /f "delims=" %%I in ('where.exe npm.cmd 2^>nul') do if not defined SETUP_NPM_BIN set "SETUP_NPM_BIN=%%I"
if not defined SETUP_NPM_BIN (
    echo [ERROR] npm no esta disponible en PATH.
    goto :error
)

"%SETUP_PHP%" "%~dp0scripts\configurar-proyecto.php" %*
set "SETUP_EXIT_CODE=%ERRORLEVEL%"

if not "%SETUP_EXIT_CODE%"=="0" goto :error_code

echo.
echo Finalizado correctamente.
echo Presione cualquier tecla para salir...
pause >nul
exit /b 0

:error
set "SETUP_EXIT_CODE=1"

:error_code
echo.
echo La configuracion no pudo completarse.
echo Presione cualquier tecla para salir...
pause >nul
exit /b %SETUP_EXIT_CODE%
