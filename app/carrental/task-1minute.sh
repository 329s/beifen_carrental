#!/bin/bash

YII_PATH="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
TASK_LOGPATH=${YII_PATH}/console/runtime/corntablogs

if [ ! -d "$TASK_LOGPATH" ]; then
    mkdir "$TASK_LOGPATH"
fi

php ${YII_PATH}/yii order/check-timeups >>${TASK_LOGPATH}/check-timeups.log 2>&1
php ${YII_PATH}/yii order/update-vehicle-maintenance-check-point >>${TASK_LOGPATH}/update-vehicle-maintenance-check-point.log 2>&1
