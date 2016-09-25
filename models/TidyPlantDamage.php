<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tidy_plant_damage".
 *
 * @property integer $id
 * @property integer $relative_id
 * @property string $damage_type
 * @property integer $batch_id
 * @property integer $city_id
 * @property integer $user_id
 * @property string $damage_img
 * @property string $create_datetime
 * @property integer $status
 */
class TidyPlantDamage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tidy_plant_damage';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['relative_id', 'batch_id', 'city_id', 'user_id', 'status'], 'integer'],
            [['create_datetime'], 'safe'],
            [['damage_type'], 'string', 'max' => 20],
            [['damage_img'], 'string', 'max' => 512],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'relative_id' => 'Relative ID',
            'damage_type' => 'Damage Type',
            'batch_id' => 'Batch ID',
            'city_id' => 'City ID',
            'user_id' => 'User ID',
            'damage_img' => 'Damage Img',
            'create_datetime' => 'Create Datetime',
            'status' => 'Status',
        ];
    }
}
