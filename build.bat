@echo off
setlocal
pushd "%~dp0"
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\windows\build.ps1" %*
set "EXITCODE=%ERRORLEVEL%"
popd
endlocal & exit /b %EXITCODE%
