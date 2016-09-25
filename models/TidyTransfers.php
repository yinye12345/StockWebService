<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tidy_transfers".
 *
 * @property integer $id
 * @property integer $trans_id
 * @property string $trans_name
 * @property integer $city_id
 * @property string $create_time
 * @property integer $cancel
 * @property string $cancel_time
 */
class TidyTransfers extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tidy_transfers';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['trans_id', 'trans_name', 'city_id'], 'required'],
            [['trans_id', 'city_id', 'cancel'], 'integer'],
            [['create_time', 'cancel_time'], 'safe'],
            [['trans_name'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'trans_id' => 'Trans ID',
            'trans_name' => 'Trans Name',
            'city_id' => 'City ID',
            'create_time' => 'Create Time',
            'cancel' => 'Cancel',
            'cancel_time' => 'Cancel Time',
        ];
    }
}
