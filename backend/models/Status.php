<?php

namespace backend\models;

use Yii;
use common\models\User;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "status".
 *
 * @property int $id
 * @property string $status_name
 * @property int $status_value
 */
class Status extends ActiveRecord
{

    public static function tableName()
    {
        return 'status';
    }


    public function rules()
    {
        return [
            [['status_name', 'status_value'], 'required'],
            [['status_value'], 'integer'],
            [['status_name'], 'string', 'max' => 45],
        ];
    }


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status_name' => 'Status Name',
            'status_value' => 'Status Value',
        ];
    }

    public function getUsers()
    {
        return $this->hasMany(User::className(), ['status_id' => 'status_value']);
    }
}