<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tidy_plant_handover_form".
 *
 * @property integer $id
 * @property integer $distribute_id
 * @property integer $batch_id
 * @property integer $city_id
 * @property integer $type
 * @property string $deliver_name
 * @property integer $deliver_id
 * @property integer $tran_id
 * @property integer $tran_userid
 * @property string $tran_name
 * @property integer $station_id
 * @property string $station_name
 * @property integer $receive_id
 * @property string $receive_name
 * @property string $tran_receive_datetime
 * @property string $tran_deliver_datetime
 * @property string $receive_datetime
 * @property integer $status
 * @property integer $is_tran_damage
 * @property string $create_datetime
 */
class TidyPlantHandoverForm extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tidy_plant_handover_form';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['distribute_id', 'batch_id', 'city_id', 'type', 'deliver_id', 'tran_id', 'tran_userid', 'station_id', 'receive_id', 'status', 'is_tran_damage'], 'integer'],
            [['tran_receive_datetime', 'tran_deliver_datetime', 'receive_datetime', 'create_datetime'], 'safe'],
            [['deliver_name', 'tran_name', 'receive_name'], 'string', 'max' => 20],
            [['station_name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'distribute_id' => 'Distribute ID',
            'batch_id' => 'Batch ID',
            'city_id' => 'City ID',
            'type' => 'Type',
            'deliver_name' => 'Deliver Name',
            'deliver_id' => 'Deliver ID',
            'tran_id' => 'Tran ID',
            'tran_userid' => 'Tran Userid',
            'tran_name' => 'Tran Name',
            'station_id' => 'Station ID',
            'station_name' => 'Station Name',
            'receive_id' => 'Receive ID',
            'receive_name' => 'Receive Name',
            'tran_receive_datetime' => 'Tran Receive Datetime',
            'tran_deliver_datetime' => 'Tran Deliver Datetime',
            'receive_datetime' => 'Receive Datetime',
            'status' => 'Status',
            'is_tran_damage' => 'Is Tran Damage',
            'create_datetime' => 'Create Datetime',
        ];
    }
}
