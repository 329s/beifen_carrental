<?php

use common\helpers\CMyHtml;


$columnFields = ['fielname','Viewlog'];

echo CMyHtml::datagrid('违章日志列表', // $title
    new \common\models\Pro_violation_inquiry(),    // $model
    $columnFields,            // $columns
    $fileInfo,            // $dataArray
    '100%', '100%',     // $width, $height
    [],            // $htmlsOptions,
    [], [],   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);