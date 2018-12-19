<?php

namespace common\helpers;

/**
 * 上传文件类
 * 
 * @package UploadFileHelper
 */
define('PUBLIC_DIR_ROOT', dirname(dirname(dirname(dirname(dirname(__FILE__))))));

class UploadFileHelper extends \yii\base\Component {
    
    public static function getUploadRootDir() {
        return PUBLIC_DIR_ROOT;
    }

    static public function upload($fieldName, $relativeDir, $timestampe) {
        $upload = \yii\web\UploadedFile::getInstanceByName($fieldName);
        if ($upload) {
            if ($upload->getHasError()) {
                return ['code'=> $upload->error, 'msg' => self::fileErrorText($upload->error)];
            }
            // 获取文件后辍名
            $ext = explode('.', $upload->name);
            $ext = array_pop($ext);

            // 过滤不是.swf、.png、.gif、.jpg、.jpeg结尾的文件
            //if (!in_array($ext, array('swf', 'png', 'jpg', 'jpeg', 'gif'))) {
            //    $uploadedFullPath = '';
            //}

            // 文件名
            if (empty($relativeDir)) {
                $relativeDir = date("Ymd");
            }
            $dirName = "/public/upload/{$relativeDir}/{$timestampe}.{$ext}";
            $basePath = \Yii::$app->basePath;
            $uploadedFullPath = PUBLIC_DIR_ROOT . $dirName;
            self::mkdirs($uploadedFullPath);
            if ($upload->saveAs($uploadedFullPath)) {
                return ['code' => $upload->error, 'path' => $dirName, 'msg' => self::fileErrorText($upload->error)];
            }
            
            return ['code' => UPLOAD_ERR_CANT_WRITE, 'path' => $dirName, 'msg' => self::fileErrorText($upload->error)];
        }
        else {
            return ['code' => UPLOAD_ERR_NO_FILE, 'msg' => self::fileErrorText(UPLOAD_ERR_NO_FILE)];
        }
    }

    public static function mkdirs($path, $mode = 0777) {
        if (strpos($path,"\\") !== false){ $path = str_replace("\\",'/',$path); }
        if (strpos($path,"//") !== false){ $path = str_replace("//",'/',$path); }
        $dirs = explode('/',$path);
        $pos = strrpos($path, ".");
        if ($pos === false) { // note: three equal signs
            // not found, means path ends in a dir not file
            $subamount=0;
        }
        else {
            $subamount=1;
        }
        for ($c=0;$c < count($dirs) - $subamount; $c++) {
            $thispath="";
            for ($cc=0; $cc <= $c; $cc++) {
                $thispath.=$dirs[$cc].'/';
            }
            if (!file_exists($thispath)) {
                if (!@mkdir($thispath,$mode))return false;
            }
        }
        return true;
    }

    public static function fileErrorText($fileError) {
        if ($fileError == UPLOAD_ERR_OK) {
            return \Yii::t('locale', 'Success');
        }
        elseif ($fileError == UPLOAD_ERR_INI_SIZE) {
            return \Yii::t('locale', 'Uploaded file size over limit of upload_max_filesize in php configuration.');
        }
        elseif ($fileError == UPLOAD_ERR_FORM_SIZE) {
            return \Yii::t('locale', 'Uploaded file size over limit of MAX_FILE_SIZE by HTML.');
        }
        elseif ($fileError == UPLOAD_ERR_PARTIAL) {
            return \Yii::t('locale', 'Uploaded file not transmit completely.');
        }
        elseif ($fileError == UPLOAD_ERR_NO_FILE) {
            return \Yii::t('locale', 'No file uploaded.');
        }
        elseif ($fileError == UPLOAD_ERR_NO_TMP_DIR) {
            return \Yii::t('locale', 'No temporary directory.');
        }
        elseif ($fileError == UPLOAD_ERR_CANT_WRITE) {
            return \Yii::t('locale', 'Cannot write the uploaded file.');
        }
        elseif ($fileError == UPLOAD_ERR_EXTENSION) {
            return \Yii::t('locale', 'File extension invalid.');
        }
        return \Yii::t('locale', 'Unknown error.');
    }

}
