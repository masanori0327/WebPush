<?php

namespace Push\Model\Dao;

class PushLog
{
    public $table_id;
    public $user_table_id;
    public $title;
    public $message;
    public $link;
    public $count;
    public $add_datetime;

    /**
     * 配列からプロパティへ値をセット
     *
     * @param array $data
     */
    public function exchangeArray(array $data) {
        $this->table_id               = (isset($data['table_id']) ? $data['table_id'] : null);
        $this->user_table_id          = (isset($data['user_table_id']) ? $data['user_table_id'] : null);
        $this->title                  = (isset($data['title']) ? $data['title'] : null);
        $this->message                = (isset($data['message']) ? $data['message'] : null);
        $this->link                   = (isset($data['link']) ? $data['link'] : null);
        $this->count                  = (isset($data['count']) ? $data['count'] : null);
        $this->add_datetime           = (isset($data['add_datetime']) ? $data['add_datetime'] : null);
    }

    /**
     * プロパティから配列を返す
     * @return array
     */
    public function getArrayCopy(){
        return get_object_vars($this);
    }
}