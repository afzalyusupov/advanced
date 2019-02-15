<?php

namespace backend\models;

use Yii;
use common\models\User;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "role".
 *
 * @property int $id
 * @property string $role_name
 * @property int $role_value
 */
class Role extends ActiveRecord
{

    public static function tableName()
    {
        return 'role';
    }


    public function rules()
    {
        return [
            [['role_name', 'role_value'], 'required'],
            [['role_value'], 'integer'],
            [['role_name'], 'string', 'max' => 45],
        ];
    }


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'role_name' => 'Role Name',
            'role_value' => 'Role Value',
        ];
    }

    public function getUsers()
    {
        return $this->hasMany(User::className(), ['role_id' => 'role_value']);
    }
}
