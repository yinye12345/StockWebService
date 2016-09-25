<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tidy_plant_deliver_form_detail".
 *
 * @property integer $id
 * @property integer $deliver_id
 * @property integer $goods_pid
 * @property string $goods_pname
 * @property integer $goods_cid
 * @property string $goods_cname
 * @property integer $deliver_prick_num
 * @property integer $deliver_branch_num
 * @property integer $deliver_bind_num
 * @property integer $receive_prick_num
 * @property integer $receive_branch_num
 * @property integer $receive_bind_num
 * @property string $mark
 * @property string $create_datetime
 */
class TidyPlantDeliverFormDetail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tidy_plant_deliver_form_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['deliver_id', 'goods_pid', 'goods_cid', 'deliver_prick_num', 'deliver_branch_num', 'deliver_bind_num', 'receive_prick_num', 'receive_branch_num', 'receive_bind_num'], 'integer'],
            [['create_datetime'], 'safe'],
            [['goods_pname', 'goods_cname'], 'string', 'max' => 20],
            [['mark'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'deliver_id' => 'Deliver ID',
            'goods_pid' => 'Goods Pid',
            'goods_pname' => 'Goods Pname',
            'goods_cid' => 'Goods Cid',
            'goods_cname' => 'Goods Cname',
            'deliver_prick_num' => 'Deliver Prick Num',
            'deliver_branch_num' => 'Deliver Branch Num',
            'deliver_bind_num' => 'Deliver Bind Num',
            'receive_prick_num' => 'Receive Prick Num',
            'receive_branch_num' => 'Receive Branch Num',
            'receive_bind_num' => 'Receive Bind Num',
            'mark' => 'Mark',
            'create_datetime' => 'Create Datetime',
        ];
    }
}
