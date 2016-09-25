<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tidy_green_type".
 *
 * @property integer $id
 * @property string $code
 * @property string $name
 * @property string $alternative_name
 * @property integer $pid
 * @property integer $order
 * @property integer $status
 * @property string $unit_type
 * @property integer $goods_type
 * @property string $create_datetime
 * @property string $modify_datetime
 */
class TidyGreenType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tidy_green_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pid', 'order', 'status', 'goods_type'], 'integer'],
            [['unit_type'], 'string'],
            [['create_datetime', 'modify_datetime'], 'safe'],
            [['code'], 'string', 'max' => 5],
            [['name', 'alternative_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'name' => 'Name',
            'alternative_name' => 'Alternative Name',
            'pid' => 'Pid',
            'order' => 'Order',
            'status' => 'Status',
            'unit_type' => 'Unit Type',
            'goods_type' => 'Goods Type',
            'create_datetime' => 'Create Datetime',
            'modify_datetime' => 'Modify Datetime',
        ];
    }
}
