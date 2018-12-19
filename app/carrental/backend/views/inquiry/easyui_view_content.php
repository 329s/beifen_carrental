<?php

use common\helpers\CMyHtml;


$columnFields = ['id','errorcontent'];

echo CMyHtml::datagrid('错误内容', // $title
    new \common\models\Pro_violation_inquiry(),    // $model
    $columnFields,            // $columns
    $logArr,            // $dataArray
    '100%', '100%',     // $width, $height
    [],            // $htmlsOptions,
    [], [],   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);