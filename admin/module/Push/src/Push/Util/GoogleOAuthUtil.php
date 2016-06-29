<?php
namespace Push\Util;

use Push\Model\Dao\GoogleOAuth;

use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Json\Json;
use Zend\Uri\UriFactory;

class GoogleOAuthUtil
{
    public $googleOauth;
    
    public function __construct(GoogleOAuth $googleOauth) {
        $this->googleOauth = $googleOauth;
    }
    
    /**
     * 認証用URL取得
     */
    public function getOAuthUrl(){
        $uri = UriFactory::factory("https://accounts.google.com/o/oauth2/auth");
        $uri->setQuery(array(
            'response_type' => 'code',
            'client_id' => $this->googleOauth->client_id,
            'redirect_uri' => $this->googleOauth->callback,
            'scope' => $this->googleOauth->scope,
            'access_type' => $this->googleOauth->access_type,
            'approval_prompt' => $this->googleOauth->approval_prompt,
            'state' => $this->googleOauth->state
        ));
        return $uri->toString();
    }

    /**
     * access_token 取得
     */
    public function getAccessToken($code)
    {
        // アクセストークン取得
        $client = new Client();
        $client->setAdapter('Zend\Http\Client\Adapter\Curl');
        $client->setUri('https://accounts.google.com/o/oauth2/token');
        $client->setMethod(Request::METHOD_POST);
        $client->setParameterPost(array(
            'code' => $code,
            'client_id' => $this->googleOauth->client_id,
            'client_secret' => $this->googleOauth->client_secret,
            'redirect_uri' => $this->googleOauth->callback,
            'grant_type' => 'authorization_code'
        ));
        $response = $client->send();

        $responseObj = Json::decode($response->getBody());

        $this->googleOauth->access_token = $responseObj->access_token;
        $this->googleOauth->token_type = $responseObj->token_type;
        $this->googleOauth->expires_datetime = date("Y-m-d H:i:s", time() + $responseObj->expires_in);
        $this->googleOauth->id_token = (property_exists($responseObj, 'id_token') ? $responseObj->id_token : null);
        $this->googleOauth->refresh_token = (property_exists($responseObj, 'refresh_token') ? $responseObj->refresh_token : null);

        return $responseObj;
    }

    /**
     * refresh_token を使い access_token を再取得
     */
    public function refreshToken()
    {
        $client = new Client();
        $client->setAdapter('Zend\Http\Client\Adapter\Curl');
        $client->setUri('https://accounts.google.com/o/oauth2/token');
        $client->setMethod(Request::METHOD_POST);
        $client->setParameterPost(array(
            'refresh_token' => $this->googleOauth->refresh_token,
            'client_id' => $this->googleOauth->client_id,
            'client_secret' => $this->googleOauth->client_secret,
            'grant_type' => 'refresh_token',
        ));

        $response = $client->send();

        $responseObj = Json::decode($response->getBody());
        if (isset($responseObj->error)) {
            error_log("refreshToken() failed");
            error_log(print_r($responseObj, true));
            throw new \Exception($responseObj->error);
        }

        $this->googleOauth->access_token = $responseObj->access_token;
        $this->googleOauth->token_type = $responseObj->token_type;
        $this->googleOauth->expires_datetime = date("Y-m-d H:i:s", time() + $responseObj->expires_in);

        return $responseObj;
    }

    /**
     * access_token のバリデーション
     */
    public function verifyAccessToken($accessToken)
    {
        $client = new Client();
        $client->setAdapter('Zend\Http\Client\Adapter\Curl');
        $client->setUri('https://www.googleapis.com/oauth2/v1/tokeninfo');
        $client->setMethod(Request::METHOD_GET);
        $client->setParameterGet(array(
            'access_token' => $accessToken,
        ));

        $response = $client->send();

        $responseObj = Json::decode($response->getBody());

        if(isset($responseObj->error)){
            return false;
        }else{
            return true;
        }
    }

    /**
     * GET リクエスト送信
     */
    public function get($uri, array $params = array())
    {
        return $this->request($uri, $params, Request::METHOD_GET);
    }

    /**
     * POST リクエスト送信
     */
    public function post($uri, array $params = array(), $isJson = true)
    {
        return $this->request($uri, $params, Request::METHOD_POST, $isJson);
    }

    /**
     * リクエスト送信
     */
    public function request($uri, array $params = array(), $method = Request::METHOD_GET, $isJsonPost = false)
    {
        $client = new Client();
        $client->setAdapter('Zend\Http\Client\Adapter\Curl');
        $client->setOptions(array(
            'timeout' => 600
        ));
        $client->setUri($uri);
        $client->setMethod($method);
        $client->setHeaders(array(
            'Authorization' => 'Bearer ' . $this->googleOauth->access_token
        ));

        if($method == Request::METHOD_POST){
            if ($isJsonPost) {
                $client->setRawBody(Json::encode($params));
                $client->setEncType('application/json;charset=utf-8');
            } else {
                $client->setParameterPost($params);
            }
        }else{
            $client->setParameterGet($params);
        }

        $responseObj = $client->send();

        return Json::decode($responseObj->getBody());
    }
    
    /**
     * stdClass を Array に変換
     */
    public static function stdClassToArray($obj)
    {
        if (!is_object($obj) && !is_array($obj)) {
            return $obj;
        }
         
        $arr = (array)$obj;
        foreach ($arr as $key => $value) {
            unset($arr[$key]);
            $key = str_replace('@', '', $key);
            $arr[$key] = self::stdClassToArray($value);
        }
         
        return $arr;
    }
}
