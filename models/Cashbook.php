<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\web\UploadedFile;

class Cashbook extends \yii\db\ActiveRecord
{
    public $file;
    public $filename;
    /**
     * @var string
     */
    private $attachment;
    /**
     * @var float|int
     */
    private $value;
    /**
     * @var mixed|null
     */
    private $type_id;
    /**
     * @var mixed|null
     */
    private $user_id;

    public function beforeSave($insert): bool
    {
        // transform value into negative number, case Expense criteries
        if ($this->isNewRecord && $this->type_id === 2) {
            $this->value *= (-1);
        }

        if ($this->type_id === 2 && $this->value > 0) {
            $this->value *= (-1);
        }
        if ($this->type_id === 2 && $this->value < 0) {
            $this->value = $this->value;
        }
        if ($this->type_id === 1) {
            $this->value = abs($this->value);
        }

        return parent::beforeSave($insert);
    }


    public static function tableName(): string
    {
        return 'cashbook';
    }

    public function rules()
    {
        return [
            [['category_id', 'type_id', 'value', 'date'], 'required'],
            [['category_id', 'type_id', 'user_id', 'is_pending'], 'integer'],
            [['value'], 'number'],
            [['file'], 'file', 'extensions' => 'jpg, png, pdf', 'maxSize' => 1024 * 1024 * 2],
            [['date', 'attachment', 'file', 'filename', 'inc_datetime', 'edit_datetime'], 'safe'],
            [['description'], 'string', 'max' => 100],
            [['attachment'], 'string', 'max' => 255],
        ];
    }

    public function getImageFile(): ?string
    {
        return isset($this->attachment) ? Yii::$app->params['uploadPath'] . $this->user_id . "/" . $this->attachment : null;
    }

    public function getImageUrl(): string
    {
        // return a default image placeholder if your source attachment is not found
        $attachment = $this->attachment ?? 'default-attachment.png';
        return Yii::$app->params['uploadUrl'] . $attachment;
    }

    /**
     * @throws Exception
     */
    public function uploadImage()
    {
        // get the uploaded file instance. for multiple file uploads
        // the following data will return an array (you may need to use
        // getInstances method)
        $file = UploadedFile::getInstance($this, 'file');

        // if no image was uploaded abort the upload
        if ($file === null) {
            return false;
        }

        // store the source file name
        $this->filename = $file->name;
        $array = explode(".", $file->name);
        $ext = end($array);

        // generate a unique file name
        $this->attachment = Yii::$app->security->generateRandomString() . ".$ext";

        // the uploaded image instance
        return $file;
    }

    public function deleteImage(): bool
    {
        $file = $this->getImageFile();

        // check if file exists on server
        if (empty($file) || !file_exists($file)) {
            return false;
        }

        // check if uploaded file can be deleted on server
        if (!unlink($file)) {
            return false;
        }

        // if deletion successful, reset your file attributes
        $this->attachment = null;
        $this->filename = null;

        return true;
    }

    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'category_id' => Yii::t('app', 'Category'),
            'type_id' => Yii::t('app', 'Type'),
            'value' => Yii::t('app', 'Value'),
            'description' => Yii::t('app', 'Description'),
            'date' => Yii::t('app', 'Date'),
            'is_pending' => Yii::t('app', 'Pending'),
            'attachment' => Yii::t('app', 'Attach'),
            'inc_datetime' => Yii::t('app', 'Created'),
            'edit_datetime' => Yii::t('app', 'Changed'),
            'file' => Yii::t('app', 'File'),
            'filename' => Yii::t('app', 'Filename'),
        ];
    }

    public function getType(): ActiveQuery
    {
        return $this->hasOne(Type::className(), ['id_type' => 'type_id']);
    }

    public function getCategory(): ActiveQuery
    {
        return $this->hasOne(Category::className(), ['id_category' => 'category_id']);
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @throws InvalidConfigException
     */
    public static function pageTotal($provider, $value): string
    {
        $total = 0;
        foreach ($provider as $item) {
            $total += $item[$value];
        }
        //return Yii::t('app', '$')." ".$total;
        return Yii::$app->formatter->asCurrency(str_replace(',', '', $total));
    }
}