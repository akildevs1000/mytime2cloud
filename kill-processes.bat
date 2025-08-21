@echo off
echo Stopping services...

taskkill /f /im nginx.exe
taskkill /f /im php.exe
taskkill /f /im java.exe
taskkill /f /im dotnet.exe

echo All specified processes have been terminated.
pause
