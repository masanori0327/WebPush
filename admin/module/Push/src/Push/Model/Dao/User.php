<?php

namespace Push\Model\Dao;

class User
{
    public $table_id;
    public $table_key;
    public $name;
    public $email;
    public $google_id;
    public $google_link;
    public $gcm_key;
    public $gcm_project_number;
    public $flg_active;
    public $add_datetime;
    public $update_datetime;

    /**
     * 配列からプロパティへ値をセット
     *
     * @param array $data
     */
    public function exchangeArray(array $data)
    {
        $this->table_id                        = (isset($data['table_id']) ? $data['table_id'] : null);
        $this->table_key                       = (isset($data['table_key']) ? $data['table_key'] : null);
        $this->name                            = (isset($data['name']) ? $data['name'] : null);
        $this->email                           = (isset($data['email']) ? $data['email'] : null);
        $this->google_id                       = (isset($data['google_id']) ? $data['google_id'] : null);
        $this->google_link                     = (isset($data['google_link']) ? $data['google_link'] : null);
        $this->gcm_key                         = (isset($data['gcm_key']) ? $data['gcm_key'] : null);
        $this->gcm_project_number              = (isset($data['gcm_project_number']) ? $data['gcm_project_number'] : null);
        $this->flg_active                      = (isset($data['flg_active']) ? $data['flg_active'] : null);
        $this->add_datetime                    = (isset($data['add_datetime']) ? $data['add_datetime'] : null);
        $this->update_datetime                 = (isset($data['update_datetime']) ? $data['update_datetime'] : null);
    }

    /**
     * プロパティから配列を返す
     * @return array
     */
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}