<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tidy_plant_category_stock".
 *
 * @property integer $id
 * @property integer $batch_id
 * @property integer $city_id
 * @property integer $record_userid
 * @property string $create_datetime
 */
class TidyPlantCategoryStock extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tidy_plant_category_stock';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['batch_id', 'city_id', 'record_userid'], 'integer'],
            [['create_datetime'], 'safe'],
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
            'city_id' => 'City ID',
            'record_userid' => 'Record Userid',
            'create_datetime' => 'Create Datetime',
        ];
    }
}
