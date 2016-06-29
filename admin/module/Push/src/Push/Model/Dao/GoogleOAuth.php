<?php
namespace Push\Model\Dao;

class GoogleOAuth
{
    public $client_id;
    public $client_secret;
    public $callback;
    public $scope;
    public $access_type;
    public $approval_prompt;
    public $state;
    public $token_type;
    public $id_token;
    public $access_token;
    public $refresh_token;
    public $expires_datetime;

    function exchangeArray(array $data)
    {
        $this->client_id        = (isset($data['client_id']) ? $data['client_id'] : null);
        $this->client_secret    = (isset($data['client_secret']) ? $data['client_secret'] : null);
        $this->callback         = (isset($data['callback']) ? $data['callback'] : null);
        $this->scope            = (isset($data['scope']) ? $data['scope'] : null);
        $this->access_type      = (isset($data['access_type']) ? $data['access_type'] : null);
        $this->approval_prompt  = (isset($data['approval_prompt']) ? $data['approval_prompt'] : null);
        $this->state            = (isset($data['state']) ? $data['state'] : null);
        $this->token_type       = (isset($data['token_type']) ? $data['token_type'] : null);
        $this->id_token         = (isset($data['id_token']) ? $data['id_token'] : null);
        $this->access_token     = (isset($data['access_token']) ? $data['access_token'] : null);
        $this->refresh_token    = (isset($data['refresh_token']) ? $data['refresh_token'] : null);
        $this->expires_datetime = (isset($data['expires_datetime']) ? $data['expires_datetime'] : null);
    }
    
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}