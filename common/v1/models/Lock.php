<?php
namespace common\v1\models;

class Lock
{
    public $id;
    public $lock_id;
    public $company_id;
    public $lock_name;
    public $serial_number;
    public $log_number;
    public $lock_group_id;
    public $linked_keys;
    public $lock_area;
    public $lock_address;
    public $lock_loc_unit;
    public $lock_post_code;
    public $lock_plate_num;
    public $site_id;
    public $added_by;
    public $updated_by;

    // Use '' for string to avoid string translation
    public function tableName(){
        return 'locks';
    }

    public function columnName(){
        return [
            'id' => '_id',
            'lock_id' => 'lock_ID',
            'company_id' => 'company_id',
            'lock_name' => 'lock_name',
            'serial_number' => 'serial_number',
            'log_number' => 'log_number',
            'lock_group_id' => 'lock_group_id',
            'linked_keys' => 'linked_keys',
            'lock_area' => 'lock_area',
            'lock_address' => 'lock_address',
            'lock_loc_unit' => 'lock_loc_unit',
            'lock_post_code' => 'lock_post_code',
            'lock_plate_num' => 'lock_plate_num',
            'site_id' => 'site_id',
            'added_by' => 'added_by',
            'updated_by' => 'updated_by',
        ];
    }

    public function rules(){
        return [
            [['lock_name', 'serial_number', 'log_number', 'lock_area', 'lock_address', 'lock_loc_unit',
                'lock_post_code', 'lock_plate_num', 'site_id', 'lock_loc_unit'], 'string'],
            [['lock_id', 'company_id', 'added_by', 'updated_by'], 'integer'],
            [['lock_group_id', 'linked_keys'], 'array'],
        ];
    }
}