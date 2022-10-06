@REM start php\php.exe backend/artisan serve
@REM cd frontend
@REM start npm run dev

@ECHO OFF

@REM for backend
@set PATH=backend\php;%PATH%
start php backend/artisan serve

@REM for frontend   
@cd frontend
@set PATH=nodejs;%PATH%

start npm run dev
start http://localhost:3000