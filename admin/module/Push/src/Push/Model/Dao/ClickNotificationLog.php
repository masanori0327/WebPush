<?php

namespace Push\Model\Dao;

class ClickNotificationLog {
    public $push_log_table_id;
    public $subscription_id;
    public $add_datetime;

    /**
     * 配列からプロパティへ値をセット
     *
     * @param array $data
     */
    public function exchangeArray(array $data) {
        $this->push_log_table_id      = (isset($data['push_log_table_id']) ? $data['push_log_table_id'] : null);
        $this->subscription_id        = (isset($data['subscription_id']) ? $data['subscription_id'] : null);
        $this->add_datetime           = (isset($data['add_datetime']) ? $data['add_datetime'] : null);
    }

    /**
     * プロパティから配列を返す
     * @return array
     */
    public function getArrayCopy() {
        return get_object_vars($this);
    }
}