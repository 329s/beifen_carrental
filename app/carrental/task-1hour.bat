@echo off

rem -------------------------------------------------------------
rem  Yii command line bootstrap script for Windows.
rem
rem  @author Qiang Xue <qiang.xue@gmail.com>
rem  @link http://www.yiiframework.com/
rem  @copyright Copyright (c) 2008 Yii Software LLC
rem  @license http://www.yiiframework.com/license/
rem -------------------------------------------------------------

@setlocal

set YII_PATH=%~dp0
set TASK_LOGPATH=%YII_PATH%console\runtime\corntablogs

if "%PHP_COMMAND%" == "" set PHP_COMMAND=D:\php-5.5.17-nts-x64\php.exe
if not exist %TASK_LOGPATH% (
    md %TASK_LOGPATH%
)

"%PHP_COMMAND%" "%YII_PATH%yii" order/check-violation >>%TASK_LOGPATH%\check-violation.log 2>&1

@endlocal
