<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tidy_plant_deliver_form".
 *
 * @property integer $id
 * @property integer $batch_id
 * @property integer $batch_suffix_id
 * @property integer $city_id
 * @property integer $deliver_userid
 * @property integer $deliver_phone
 * @property string $deliver_datetime
 * @property integer $receive_userid
 * @property string $receive_datetime
 * @property integer $deliver_num
 * @property integer $receive_num
 * @property integer $status
 * @property integer $damage_flag
 * @property string $create_datetime
 */
class TidyPlantDeliverForm extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tidy_plant_deliver_form';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['batch_id', 'batch_suffix_id', 'city_id', 'deliver_userid', 'deliver_phone', 'receive_userid', 'deliver_num', 'receive_num', 'status', 'damage_flag'], 'integer'],
            [['deliver_datetime', 'receive_datetime', 'create_datetime'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'batch_id' => 'Batch ID',
            'batch_suffix_id' => 'Batch Suffix ID',
            'city_id' => 'City ID',
            'deliver_userid' => 'Deliver Userid',
            'deliver_phone' => 'Deliver Phone',
            'deliver_datetime' => 'Deliver Datetime',
            'receive_userid' => 'Receive Userid',
            'receive_datetime' => 'Receive Datetime',
            'deliver_num' => 'Deliver Num',
            'receive_num' => 'Receive Num',
            'status' => 'Status',
            'damage_flag' => 'Damage Flag',
            'create_datetime' => 'Create Datetime',
        ];
    }
}
