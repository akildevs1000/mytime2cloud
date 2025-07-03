@echo off
cd backend && @set PATH=php;%PATH% && php artisan task:sync_off 1 ask