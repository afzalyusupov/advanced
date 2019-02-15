<?php

namespace frontend\models;

use Yii;
use yii\db\ActiveRecord;
use yii\debug\models\search\Profile;

/**
 * This is the model class for table "gender".
 *
 * @property int $id
 * @property string $gender_name
 *
 * @property Profile[] $profiles
 */
class Gender extends ActiveRecord
{

    public static function tableName()
    {
        return 'gender';
    }


    public function rules()
    {
        return [
            [['gender_name'], 'required'],
            [['gender_name'], 'string', 'max' => 45],
        ];
    }


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'gender_name' => 'Gender Name',
        ];
    }


    public function getProfiles()
    {
        return $this->hasMany(Profile::className(), ['gender_id' => 'id']);
    }
}
