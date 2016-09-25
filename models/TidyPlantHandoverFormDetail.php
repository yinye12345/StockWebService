<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tidy_plant_handover_form_detail".
 *
 * @property integer $id
 * @property integer $handover_id
 * @property integer $pid
 * @property string $pname
 * @property integer $cid
 * @property string $cname
 * @property integer $deliver_prick_num
 * @property integer $deliver_branch_num
 * @property integer $deliver_bind_num
 * @property integer $tran_prick_num
 * @property integer $tran_branch_num
 * @property integer $tran_bind_num
 * @property integer $receive_prick_num
 * @property integer $receive_branch_num
 * @property integer $receive_bind_num
 * @property integer $fit_order_num
 * @property string $deliver_mark
 * @property string $receive_mark
 * @property string $modify_datetime
 */
class TidyPlantHandoverFormDetail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tidy_plant_handover_form_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['handover_id', 'pid', 'cid', 'goods_type', 'deliver_prick_num', 'deliver_branch_num', 'deliver_bind_num', 'tran_prick_num', 'tran_branch_num', 'tran_bind_num', 'receive_prick_num', 'receive_branch_num', 'receive_bind_num', 'fit_order_num'], 'integer'],
            [['modify_datetime'], 'safe'],
            [['pname', 'cname'], 'string', 'max' => 50],
            [['deliver_mark', 'receive_mark'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'handover_id' => 'Handover ID',
            'pid' => 'Pid',
            'pname' => 'Pname',
            'cid' => 'Cid',
            'cname' => 'Cname',
            'deliver_prick_num' => 'Deliver Prick Num',
            'deliver_branch_num' => 'Deliver Branch Num',
            'deliver_bind_num' => 'Deliver Bind Num',
            'tran_prick_num' => 'Tran Prick Num',
            'tran_branch_num' => 'Tran Branch Num',
            'tran_bind_num' => 'Tran Bind Num',
            'receive_prick_num' => 'Receive Prick Num',
            'receive_branch_num' => 'Receive Branch Num',
            'receive_bind_num' => 'Receive Bind Num',
            'fit_order_num' => 'Fit Order Num',
            'deliver_mark' => 'Deliver Mark',
            'receive_mark' => 'Receive Mark',
            'modify_datetime' => 'Modify Datetime',
        ];
    }
}
