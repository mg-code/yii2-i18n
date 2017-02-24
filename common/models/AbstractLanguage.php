<?php

namespace mgcode\i18n\common\models;

use Yii;

/**
 * This is the model class for table "language".
 *
 * @property string $iso_code
 * @property string $title
 * @property integer $sort
 * @property integer $is_active
 * @property string $created_at
 * @property string $updated_at
 */
abstract class AbstractLanguage extends \yii\db\ActiveRecord
{
    /** @inheritdoc */
    public static function tableName()
    {
        return 'language';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['iso_code', 'title', 'sort', 'updated_at'], 'required'],
            [['sort', 'is_active'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['iso_code'], 'string', 'max' => 2],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'iso_code' => 'Iso Code',
            'title' => 'Title',
            'sort' => 'Sort',
            'is_active' => 'Is Active',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @inheritdoc
     * @return \mgcode\i18n\common\models\queries\LanguageQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \mgcode\i18n\common\models\queries\LanguageQuery(get_called_class());
    }
}
