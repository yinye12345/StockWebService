<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tidy_line_station".
 *
 * @property integer $id
 * @property integer $station_id
 * @property integer $tran_id
 * @property integer $status
 * @property integer $create_datetime
 * @property string $update_datetime
 */
class TidyLineStation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tidy_line_station';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['station_id', 'tran_id', 'status', 'create_datetime'], 'integer'],
            [['update_datetime'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'station_id' => 'Station ID',
            'tran_id' => 'Tran ID',
            'status' => 'Status',
            'create_datetime' => 'Create Datetime',
            'update_datetime' => 'Update Datetime',
        ];
    }
}
