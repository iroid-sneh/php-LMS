@echo off
echo Starting PHP Leave Management System...
echo.

echo Starting PHP development server for backend...
start "Backend Server" cmd /k "cd /d %~dp0php-LMS\backend && php -S localhost:8000"

timeout /t 2 /nobreak > nul

echo Starting PHP development server for frontend...
start "Frontend Server" cmd /k "cd /d %~dp0php-LMS\frontend && php -S localhost:3000"

echo.
echo Servers started successfully!
echo.
echo Backend API: http://localhost:8000
echo Frontend: http://localhost:3000
echo.
echo Default credentials:
echo HR Admin: admin@company.com / password
echo Employee: john@company.com / password
echo.
echo Press any key to exit...
pause > nul
