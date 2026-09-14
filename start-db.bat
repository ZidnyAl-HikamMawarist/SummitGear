@echo off
echo Memeriksa dan menjalankan PostgreSQL...
if exist "C:\laragon\data\postgresql\postmaster.pid" (
    echo Menghapus postmaster.pid lama yang tersangkut...
    del /f /q "C:\laragon\data\postgresql\postmaster.pid"
)
"C:\laragon\bin\postgresql\postgresql\bin\pg_ctl.exe" start -D "C:\laragon\data\postgresql" -l "C:\laragon\data\pg.log"
echo PostgreSQL siap digunakan!
