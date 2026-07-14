@echo off
setlocal EnableDelayedExpansion
title ResourceSpace One-Click Setup
color 0B

:: ============================================================
::  ResourceSpace One-Click Setup for Windows (XAMPP based)
::
::  What this script does:
::    1. Checks for (or downloads + installs) XAMPP
::    2. Copies the resourcespace folder into XAMPP htdocs
::    3. Starts Apache + MySQL
::    4. Creates the "resourcespace" MySQL database
::    5. Opens ResourceSpace in your default web browser
::
::  Just double-click this file. Run it again any time to
::  re-start the services and re-open the browser.
:: ============================================================

echo.
echo  ============================================================
echo   ResourceSpace One-Click Setup
echo  ============================================================
echo.

:: ---------- 0. Elevate to Administrator if needed ----------
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo  Requesting administrator rights...
    powershell -NoProfile -Command "Start-Process -FilePath '%~f0' -Verb RunAs"
    exit /b
)

set "SCRIPT_DIR=%~dp0"
set "XAMPP_DIR=C:\xampp"
set "HTDOCS=%XAMPP_DIR%\htdocs"
set "RS_SRC=%SCRIPT_DIR%resourcespace"
set "RS_DEST=%HTDOCS%\resourcespace"
set "RS_URL=http://localhost/resourcespace"
set "XAMPP_VERSION=8.2.12"
set "XAMPP_INSTALLER=%TEMP%\xampp-installer.exe"
set "XAMPP_URL=https://sourceforge.net/projects/xampp/files/XAMPP%%20Windows/8.2.12/xampp-windows-x64-8.2.12-0-VS16-installer.exe/download"

:: ---------- 1. Verify the resourcespace source folder exists ----------
if not exist "%RS_SRC%\index.php" (
    color 0C
    echo  [ERROR] Could not find the "resourcespace" folder next to this script.
    echo          Make sure you run setup.bat from the root of the downloaded
    echo          repository ^(the folder that contains "resourcespace"^).
    echo.
    pause
    exit /b 1
)

:: ---------- 2. Check for XAMPP / install it if missing ----------
if exist "%XAMPP_DIR%\xampp-control.exe" (
    echo  [OK] XAMPP found at %XAMPP_DIR%
) else (
    echo  [..] XAMPP not found. Downloading XAMPP %XAMPP_VERSION% ^(~150 MB^)...
    echo       This may take a few minutes depending on your connection.
    powershell -NoProfile -Command ^
        "$ProgressPreference='SilentlyContinue';" ^
        "[Net.ServicePointManager]::SecurityProtocol=[Net.SecurityProtocolType]::Tls12;" ^
        "Invoke-WebRequest -Uri '%XAMPP_URL%' -OutFile '%XAMPP_INSTALLER%' -UserAgent 'Mozilla/5.0'"
    if not exist "%XAMPP_INSTALLER%" (
        color 0C
        echo  [ERROR] Download failed. Please install XAMPP manually from
        echo          https://www.apachefriends.org/download.html
        echo          then run this script again.
        pause
        exit /b 1
    )
    echo  [..] Installing XAMPP silently to %XAMPP_DIR% ...
    "%XAMPP_INSTALLER%" --mode unattended --unattendedmodeui none --disable-components xampp_filezilla,xampp_mercury,xampp_tomcat,xampp_perl --installer-language en --prefix "%XAMPP_DIR%"
    if not exist "%XAMPP_DIR%\xampp-control.exe" (
        color 0C
        echo  [ERROR] XAMPP installation failed. Please install it manually from
        echo          https://www.apachefriends.org/download.html
        pause
        exit /b 1
    )
    del /q "%XAMPP_INSTALLER%" >nul 2>&1
    echo  [OK] XAMPP installed.
)

:: ---------- 3. Copy ResourceSpace into htdocs ----------
if exist "%RS_DEST%\include\config.php" (
    echo  [..] Existing installation detected - syncing code changes only
    echo       ^(your config.php and filestore are preserved^)...
    robocopy "%RS_SRC%" "%RS_DEST%" /E /XO /XF config.php /XD filestore /NFL /NDL /NJH /NJS /NC /NS >nul
) else (
    echo  [..] Copying ResourceSpace files to %RS_DEST% ...
    robocopy "%RS_SRC%" "%RS_DEST%" /E /NFL /NDL /NJH /NJS /NC /NS >nul
)
if %errorlevel% geq 8 (
    color 0C
    echo  [ERROR] File copy failed. Close any programs using those files and retry.
    pause
    exit /b 1
)
echo  [OK] Files in place.

:: ---------- 4. Make sure filestore exists and is writable ----------
if not exist "%RS_DEST%\filestore" mkdir "%RS_DEST%\filestore"
icacls "%RS_DEST%\filestore" /grant Everyone:(OI)(CI)F /T /Q >nul 2>&1

:: ---------- 5. Raise PHP limits ResourceSpace needs ----------
set "PHP_INI=%XAMPP_DIR%\php\php.ini"
if exist "%PHP_INI%" (
    powershell -NoProfile -Command ^
        "(Get-Content '%PHP_INI%')" ^
        " -replace '^;?\s*post_max_size\s*=.*','post_max_size = 512M'" ^
        " -replace '^;?\s*upload_max_filesize\s*=.*','upload_max_filesize = 512M'" ^
        " -replace '^;?\s*memory_limit\s*=.*','memory_limit = 512M'" ^
        " -replace '^;?\s*max_execution_time\s*=.*','max_execution_time = 300'" ^
        " -replace '^;extension=gd','extension=gd'" ^
        " -replace '^;extension=intl','extension=intl'" ^
        " -replace '^;extension=exif','extension=exif'" ^
        " | Set-Content '%PHP_INI%'"
    echo  [OK] PHP configured ^(512M uploads, gd/intl/exif enabled^).
)

:: ---------- 6. Stop conflicting MySQL servers on port 3306 ----------
:: A separately installed MySQL (e.g. the "MySQL80" Windows service) on port
:: 3306 would be answered instead of XAMPP's MySQL and causes errors such as
:: "Plugin caching_sha2_password could not be loaded".
for %%S in (MySQL MySQL57 MySQL80 MySQL83 MySQL84 MariaDB) do (
    sc query "%%S" 2>nul | find /I "RUNNING" >nul && (
        echo  [..] Stopping conflicting MySQL Windows service "%%S" ^(port 3306^)...
        net stop "%%S" /y >nul 2>&1
    )
)
:: Kill any mysqld process that is NOT XAMPP's (frees port 3306)
powershell -NoProfile -Command ^
    "Get-Process mysqld -ErrorAction SilentlyContinue | Where-Object { $_.Path -and $_.Path -notlike 'C:\xampp*' } | Stop-Process -Force" >nul 2>&1

:: ---------- 6b. Start Apache and MySQL ----------
echo  [..] Starting Apache and MySQL...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>nul | find /I "mysqld.exe" >nul || start "" /B "%XAMPP_DIR%\mysql\bin\mysqld.exe" --defaults-file="%XAMPP_DIR%\mysql\bin\my.ini" --standalone
tasklist /FI "IMAGENAME eq httpd.exe" 2>nul | find /I "httpd.exe" >nul || start "" /B "%XAMPP_DIR%\apache\bin\httpd.exe"

:: Verify Apache stayed up - if port 80/443 is blocked (error "OS 10013"),
:: httpd exits immediately. In that case move Apache to ports 8080/8443.
timeout /t 3 /nobreak >nul
tasklist /FI "IMAGENAME eq httpd.exe" 2>nul | find /I "httpd.exe" >nul
if %errorlevel% neq 0 (
    echo  [!!] Port 80 or 443 is blocked by another program ^(IIS, Skype, VPN...^).
    echo  [..] Moving Apache to ports 8080/8443 instead...
    powershell -NoProfile -Command ^
        "(Get-Content 'C:\xampp\apache\conf\httpd.conf') -replace '^Listen 80$','Listen 8080' -replace '^ServerName localhost:80$','ServerName localhost:8080' | Set-Content 'C:\xampp\apache\conf\httpd.conf';" ^
        "(Get-Content 'C:\xampp\apache\conf\extra\httpd-ssl.conf') -replace '^Listen 443$','Listen 8443' -replace '<VirtualHost _default_:443>','<VirtualHost _default_:8443>' | Set-Content 'C:\xampp\apache\conf\extra\httpd-ssl.conf'"
    set "RS_URL=http://localhost:8080/resourcespace"
    set "PMA_URL=http://localhost:8080/phpmyadmin"
    start "" /B "%XAMPP_DIR%\apache\bin\httpd.exe"
    timeout /t 3 /nobreak >nul
    tasklist /FI "IMAGENAME eq httpd.exe" 2>nul | find /I "httpd.exe" >nul
    if !errorlevel! neq 0 (
        color 0C
        echo  [ERROR] Apache still could not start. Check
        echo          C:\xampp\apache\logs\error.log for details, or start
        echo          Apache from the XAMPP Control Panel to see the error.
        pause
        exit /b 1
    )
    echo  [OK] Apache is now running on port 8080.
)
if not defined PMA_URL set "PMA_URL=http://localhost/phpmyadmin"

:: Wait for MySQL to accept connections (up to ~30s)
set /a TRIES=0
:wait_mysql
"%XAMPP_DIR%\mysql\bin\mysqladmin.exe" -h 127.0.0.1 -u root ping 2>nul | find /I "alive" >nul
if %errorlevel% equ 0 goto mysql_ready
set /a TRIES+=1
if %TRIES% geq 30 (
    color 0C
    echo  [ERROR] MySQL did not start. Open the XAMPP Control Panel
    echo          ^(%XAMPP_DIR%\xampp-control.exe^) and start MySQL manually,
    echo          then run this script again.
    pause
    exit /b 1
)
timeout /t 1 /nobreak >nul
goto wait_mysql
:mysql_ready
echo  [OK] Apache and MySQL are running.

:: ---------- 7. Create the database if it does not exist ----------
set "MYSQL_PWD_DISPLAY=(leave blank)"
set "DB_OK=0"

:: First attempt: root with NO password (fresh XAMPP default)
"%XAMPP_DIR%\mysql\bin\mysql.exe" -h 127.0.0.1 -u root -e "CREATE DATABASE IF NOT EXISTS resourcespace DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>"%TEMP%\rs_mysql_err.txt"
if %errorlevel% equ 0 set "DB_OK=1"

if "%DB_OK%"=="0" (
    echo  [!!] Could not connect as root with a blank password. MySQL said:
    type "%TEMP%\rs_mysql_err.txt"
    echo.
    echo       Your MySQL root user probably has a password set.
    set /p MYSQL_ROOT_PW=      Enter your MySQL root password ^(or press Enter to skip^): 
    if not "!MYSQL_ROOT_PW!"=="" (
        "%XAMPP_DIR%\mysql\bin\mysql.exe" -h 127.0.0.1 -u root -p"!MYSQL_ROOT_PW!" -e "CREATE DATABASE IF NOT EXISTS resourcespace DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>"%TEMP%\rs_mysql_err.txt"
        if !errorlevel! equ 0 (
            set "DB_OK=1"
            set "MYSQL_PWD_DISPLAY=(the password you just entered)"
        ) else (
            echo  [!!] Still could not connect. MySQL said:
            type "%TEMP%\rs_mysql_err.txt"
        )
    )
)
del /q "%TEMP%\rs_mysql_err.txt" >nul 2>&1

if "%DB_OK%"=="1" (
    echo  [OK] Database "resourcespace" is ready.
) else (
    echo  [!!] Could not create the database automatically. You can create it
    echo       in phpMyAdmin ^(http://localhost/phpmyadmin^) - name it "resourcespace".
)

:: ---------- 8. Open ResourceSpace in the browser ----------
echo.
if exist "%RS_DEST%\include\config.php" (
    echo  [OK] Setup complete! Opening ResourceSpace...
    start "" "%RS_URL%"
) else (
    echo  [OK] Opening the ResourceSpace first-time setup wizard.
    echo.
    echo       On the setup page use these database values:
    echo         MySQL server    : localhost
    echo         MySQL username  : root
    echo         MySQL password  : %MYSQL_PWD_DISPLAY%
    echo         Database name   : resourcespace
    echo         Base URL        : %RS_URL%
    echo.
    start "" "%RS_URL%/pages/setup.php"
)

echo.
echo  ------------------------------------------------------------
echo   ResourceSpace URL : %RS_URL%
echo   phpMyAdmin        : %PMA_URL%
echo   XAMPP Control     : %XAMPP_DIR%\xampp-control.exe
echo   Run this setup.bat again any time to restart everything.
echo  ------------------------------------------------------------
echo.
pause
endlocal
