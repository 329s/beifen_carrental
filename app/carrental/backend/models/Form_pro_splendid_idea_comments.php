<?php

namespace backend\models;

/**
 * This is the active form model class for table "pro_splendid_idea_comments".
 */
class Form_pro_splendid_idea_comments extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $main_id;
    public $comment_to;
    public $content;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['main_id'], 'required'],
            [['id', 'main_id', 'comment_to'], 'integer'],
            [['content'], 'string', 'max' => 512],
        ];
    }

    public function getActiveRecordModel() {
        $model = new \common\models\Pro_splendid_idea_comments();
        return $model;
    }

}
