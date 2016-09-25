<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tidy_plant_damage_detail".
 *
 * @property integer $id
 * @property integer $damage_id
 * @property integer $pid
 * @property string $pname
 * @property integer $cid
 * @property string $cname
 * @property integer $prick_num
 * @property integer $branch_num
 * @property integer $bind_num
 * @property string $modify_datetime
 */
class TidyPlantDamageDetail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tidy_plant_damage_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['damage_id', 'pid', 'cid', 'prick_num', 'branch_num', 'bind_num'], 'integer'],
            [['modify_datetime'], 'safe'],
            [['pname', 'cname'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'damage_id' => 'Damage ID',
            'pid' => 'Pid',
            'pname' => 'Pname',
            'cid' => 'Cid',
            'cname' => 'Cname',
            'prick_num' => 'Prick Num',
            'branch_num' => 'Branch Num',
            'bind_num' => 'Bind Num',
            'modify_datetime' => 'Modify Datetime',
        ];
    }
}
