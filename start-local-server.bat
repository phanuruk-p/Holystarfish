@echo off
setlocal

set "PHP_EXE=C:\xampp\php\php.exe"

if not exist "%PHP_EXE%" (
    echo ไม่พบ PHP ที่ %PHP_EXE%
    echo กรุณาติดตั้ง XAMPP หรือแก้ตำแหน่ง PHP ในไฟล์นี้
    pause
    exit /b 1
)

echo กำลังเปิดเว็บ Holystarfish ที่ http://localhost:8000
start "" http://localhost:8000
"%PHP_EXE%" -S localhost:8000 router.php

echo.
echo เซิร์ฟเวอร์หยุดทำงานแล้ว
pause
