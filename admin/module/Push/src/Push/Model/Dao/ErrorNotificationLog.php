<?php

namespace Push\Model\Dao;

class ErrorNotificationLog
{
    public $subscription_id;
    public $error;
    public $add_datetime;

    /**
     * 配列からプロパティへ値をセット
     *
     * @param array $data
     */
    public function exchangeArray(array $data)
    {
        $this->subscription_id        = (isset($data['subscription_id']) ? $data['subscription_id'] : null);
        $this->error                  = (isset($data['error']) ? $data['error'] : null);
        $this->add_datetime           = (isset($data['add_datetime']) ? $data['add_datetime'] : null);
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