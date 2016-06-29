<?php

namespace Push\Model\Dao;

class SubscriberList
{
    public $subscription_id;
    public $terminal;
    public $browser;
    public $latitude;
    public $longitude;
    public $geoError;
    public $add_datetime;
    public $address1;
    public $address2;
    public $address3;
    
    /**
     * 配列からプロパティへ値をセット
     *
     * @param array $data
     */
    public function exchangeArray(array $data)
    {
        $this->subscription_id        = (isset($data['subscription_id']) ? $data['subscription_id'] : null);
        $this->terminal               = (isset($data['terminal']) ? $data['terminal'] : null);
        $this->browser                = (isset($data['browser']) ? $data['browser'] : null);
        $this->latitude               = (isset($data['latitude']) ? $data['latitude'] : null);
        $this->longitude              = (isset($data['longitude']) ? $data['longitude'] : null);
        $this->geoError               = (isset($data['geoError']) ? $data['geoError'] : null);
        $this->add_datetime           = (isset($data['add_datetime']) ? $data['add_datetime'] : null);
        $this->address1               = (isset($data['address1']) ? $data['address1'] : null);
        $this->address2               = (isset($data['address2']) ? $data['address2'] : null);
        $this->address3               = (isset($data['address3']) ? $data['address3'] : null);
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