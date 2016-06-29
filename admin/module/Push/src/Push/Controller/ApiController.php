<?php
namespace Push\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Push\Model\SubscriberList;

/**
 * Class ApiController
 */
class ApiController extends AbstractActionController
{
    const REGIST_SUBSCRIPTION_ID = 1;
    const GET_SUBSCRIBER_SQUEEZE_PREFECTURES = 2;
    
    private function errorResponse($code, $msg)
    {
        $response = [
            'error' => 1,
            'code' => $code,
            'msg' => $msg
        ];
        
        $json = \Zend\Json\Json::encode($response);
        return $this->response->setContent($json);
    }
    
    /**
     * subscriptionIdを登録
     * @return json
     * 
     * https://developer.mozilla.org/ja/docs/Web/API/PositionError
     * 0 => 原因不明のエラー
     * 1 => 利用者が位置情報の取得を許可しなかった
     * 2 => 電波状況などで位置情報が取得できなかった
     * 3 => 位置情報の取得に時間がかかり過ぎ
     * 9 => 位置情報APIが使用できません(
     */
    public function registSubscriptionIdAction()
    {
        header("Access-Control-Allow-Origin: *");
        
        $id = $this->params()->fromQuery('id', false);
        
        if(!$id || !$ua) return $this->errorResponse(self::REGIST_SUBSCRIPTION_ID, 'no query subscriberId');
        
        $browser = $this->params()->fromQuery('b', false);
        $terminal = $this->params()->fromQuery('t', false);
        if($terminal === 'p'){
            $terminal = 'pc';
        }else if($terminal === 'm'){
            $terminal = 'mobile';
        }else if($terminal === 't'){
            $terminal = 'tablet';
        }
        
        $lat = $this->params()->fromQuery('lat', null);
        $lon = $this->params()->fromQuery('lon', null);
        $geoError = $this->params()->fromQuery('geoError', null);
        
        $subscriberListTable = $this->getServiceLocator()->get('Push\Model\SubscriberListTable');
        $subscriberList = new SubscriberList();
        $subscriberList->subscription_id = $id;
        $subscriberList->terminal = $terminal;
        $subscriberList->browser = $browser;
        $subscriberList->latitude = $lat;
        $subscriberList->longitude = $lon;
        $subscriberList->geoError = $geoError;
        $subscriberList->add_datetime = date("Y-m-d H:i:s");
        if($lat && $lon){
            try{
                $config = $this->getServiceLocator()->get('Config');
                $rgeoUrl = sprintf($config['api']['rgeocode'], $lat, $lon);
                $json = file_get_contents($rgeoUrl);
                if(!$json || $json === FALSE) continue;
                $result = \Zend\Json\Json::decode($json);
                if(isset($result->result->prefecture->pname)){
                    $subscriberList->address1 = $result->result->prefecture->pname;
                }
                if(isset($result->result->municipality->mname)){
                    $subscriberList->address2 = $result->result->municipality->mname;
                }
                if(isset($result->result->local[0]->section)){
                    $subscriberList->address3 = $result->result->local[0]->section;
                }
            }catch(\Exception $e){
                // こんなとこでエラーされても困る 1
                error_log ($e->getMessage());
            }
        }
        try{
            $subscriberListTable->insertOrUpdate($subscriberList);
        }catch(\Exception $e){
            // こんなとこでエラーされても困る 2
            error_log ($e->getMessage());
        }
        
        $response = [
            'id' => $id,
        ];
        
        $json = \Zend\Json\Json::encode($response);
        return $this->response->setContent($json);
    }
}
