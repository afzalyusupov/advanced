<?php
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\IdentityInterface;
use yii\db\Expression;
use yii\helpers\Security;
use backend\models\Role;
use backend\models\Status;
use backend\models\UserType;
use frontend\models\Profile;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property integer $role_id
 * @property integer $status_id
 * @proprty integer $user_type_id
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 */

class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_ACTIVE = 10;


    public static function tableName()
    {
        return 'user';
    }


    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules()
    {
        return [
            ['status_id', 'default', 'value' => self::STATUS_ACTIVE],
            [['status_id'],'in', 'range'=>array_keys($this->getStatusList())],
            ['role_id', 'default', 'value' => 10],
            [['role_id'],'in', 'range'=>array_keys($this->getRoleList())],
            ['user_type_id', 'default', 'value' => 10],
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required'],
            ['username', 'unique'],
            ['username', 'string', 'min' => 2, 'max' => 255],
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'unique'],
        ];
    }


    public function attributeLabels()
    {
        return [
            'roleName' => Yii::t('app', 'Role'),
            'statusName' => Yii::t('app', 'Status'),
            'profileId' => Yii::t('app', 'Profile'),
            'profileLink' => Yii::t('app', 'Profile'),
            'userLink' => Yii::t('app', 'User'),
            'username' => Yii::t('app', 'User'),
            'userTypeName' => Yii::t('app', 'User Type'),
            'userTypeId' => Yii::t('app', 'User Type'),
            'userIdLink' => Yii::t('app', 'ID'),
        ];
    }


    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status_id' => self::STATUS_ACTIVE]);
    }


    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['auth_key' => $token]);
    }


    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status_id' => self::STATUS_ACTIVE]);
    }


    public static function findByPasswordResetToken($token)
    {
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        $parts = explode('_', $token);
        $timestamp = (int) end($parts);
        if ($timestamp + $expire < time()) {
            return null;
        }
        return static::findOne([
            'password_reset_token' => $token,
            'status_id' => self::STATUS_ACTIVE,
        ]);
    }


    public function getId()
    {
        return $this->getPrimaryKey();
    }


    public function getAuthKey()
    {
        return $this->auth_key;
    }


    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }


    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }


    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }


    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }


    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }


    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    public function getProfile()
    {
        return $this->hasOne(Profile::className(), ['user_id'=>id]);
    }

    public function getRole()
    {
        return $this->hasOne(Role::className(), ['role_value' => 'role_id']);
    }

    public function getRoleName()
    {
        return $this->role ? $this->role->role_name : '- no role -';
    }

    public static function getRoleList()
    {
        $droptions = Role::find()->asArray()->all();
        return Arrayhelper::map($droptions, 'role_value', 'role_name');
    }

    public function getStatus()
    {
        return $this->hasOne(Status::className(), ['status_value' => 'status_id']);
    }

    public function getStatusName()
    {
        return $this->status ? $this->status->status_name : '- no status -';
    }

    public static function getStatusList()
    {
        $droptions = Status::find()->asArray()->all();
        return Arrayhelper::map($droptions, 'status_value', 'status_name');
    }

    public function getUserType()
    {
        return $this->hasOne(UserType::className(), ['user_type_value' => 'user_type_id']);
    }

    public function getUserTypeName()
    {
        return $this->userType ? $this->userType->user_type_name : '- no user type -';
    }

    public static function getUserTypeList()
    {
        $droptions = UserType::find()->asArray()->all();
        return Arrayhelper::map($droptions, 'user_type_value', 'user_type_name');
    }

    public function getUserTypeId()
    {
        return $this->userType ? $this->userType->id : 'none';
    }

    public function getProfileId()
    {
        return $this->profile ? $this->profile->id : 'none';
    }

    public function getProfileLink()
    {
        $url = Url::to(['profile/view', 'id'=>$this->profileId]);
        $options = [];
        return Html::a($this->profile ? 'profile' : 'none', $url, $options);
    }

    public function getUserIdLink()
    {
        $url = Url::to(['user/update', 'id'=>$this->id]);
        $options = [];
        return Html::a($this->id, $url, $options);
    }

    public function getUserLink()
    {
        $url = Url::to(['user/view', 'id'=>$this->id]);
        $options = [];
        return Html::a($this->username, $url, $options);
    }
}