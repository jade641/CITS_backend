@echo off
REM =========================================
REM Login Test Script - Windows Batch
REM =========================================

echo.
echo =========================================
echo   TESTING LOGIN AUTHENTICATION
echo =========================================
echo.

REM Run the authentication test
php test-auth.php

echo.
echo =========================================
echo   TEST COMPLETE
echo =========================================
echo.
echo If all tests passed, you can now deploy!
echo.
echo Next Steps:
echo   1. Update Render environment variables
echo   2. git add .
echo   3. git commit -m "Fix: Login configuration"
echo   4. git push origin main
echo   5. Deploy on Render
echo.
pause
