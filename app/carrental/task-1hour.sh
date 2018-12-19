#!/bin/bash

YII_PATH="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
TASK_LOGPATH=${YII_PATH}/console/runtime/corntablogs

if [ ! -d "$TASK_LOGPATH" ]; then
    mkdir "$TASK_LOGPATH"
fi

php ${YII_PATH}/yii order/check-violation >>${TASK_LOGPATH}/check-violation.log 2>&1
