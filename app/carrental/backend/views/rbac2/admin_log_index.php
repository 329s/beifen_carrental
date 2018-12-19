<?php

use common\helpers\CMyHtml;

$urlsArray = [
    'url' => \yii\helpers\Url::to(['rbac2/admin_log_list']),
];

echo CMyHtml::datagrid('   ', // $title
    new backend\models\Rbac_admin_log(),    // $model
    ['id', 'user_id', 'time', 'url', 'post_data', 'ip', 'operation'],            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    ['data-options' => ['sortName'=>'id', 'sortOrder'=>'desc']],            // $htmlsOptions,
    $urlsArray, [],   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
    );

