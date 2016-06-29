<?php
namespace Push\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Push\Module;

/**
 * Class BatchController
 */
class BatchController extends AbstractActionController
{
    /** @var \Zend\Console\Request */
    protected $request;
    
    /** @var console parameters */
    private $verbose;
    private $startInterval;
    
    public function onDispatch(MvcEvent $e)
    {
        // arguments check
        $request = $this->getRequest();
        if (!$request instanceof \Zend\Console\Request){
            throw new \RuntimeException('You can only use this action from a console!');
            exit();
        }
        $this->request = $request;
        
        echo 'EXE ：' . $this->params('action') . PHP_EOL;
        $startDate = date('Y-m-d H:i:s');
        echo 'START  開始時間：' . $startDate . PHP_EOL;
        $this->startInterval = time();
        
        $this->verbose = $this->request->getParam('verbose') || $this->request->getParam('v');
        
        ini_set('memory_limit', -1);

        return parent::onDispatch($e);
    }
    
    /**
     * テーブルオプティマイズ
     * 一日に一回 cronで実行
     */
    public function tableOptimizeAction()
    {
        // 30日以前のデータを削除する
        $deleteDate = date('Y-m-d', strtotime("-30 days"));
        
        $adapter = Module::$dbAdapter;
        
        $sql = "ALTER TABLE `subscriber_list` ENGINE InnoDB";
        $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        
        $showNotificationLogTable = $this->getServiceLocator()->get('Push\Model\ShowNotificationLogTable');
        $predicate = new \Zend\Db\Sql\Predicate\Predicate();
        $predicate = $predicate->lessThanOrEqualTo('add_datetime', $deleteDate);
        $showNotificationLogTable->delete($predicate);
        $sql = "ALTER TABLE `show_notification_log` ENGINE InnoDB";
        $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        
        $clickNotificationLogTable = $this->getServiceLocator()->get('Push\Model\ClickNotificationLogTable');
        $predicate = new \Zend\Db\Sql\Predicate\Predicate();
        $predicate = $predicate->lessThanOrEqualTo('add_datetime', $deleteDate);
        $clickNotificationLogTable->delete($predicate);
        $sql = "ALTER TABLE `click_notification_log` ENGINE InnoDB";
        $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        
        $errorNotificationLogTable = $this->getServiceLocator()->get('Push\Model\ErrorNotificationLogTable');
        $predicate = new \Zend\Db\Sql\Predicate\Predicate();
        $predicate = $predicate->lessThanOrEqualTo('add_datetime', $deleteDate);
        $errorNotificationLogTable->delete($predicate);
        $sql = "ALTER TABLE `error_notification_log` ENGINE InnoDB";
        $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        
        $sendHistoryTable = $this->getServiceLocator()->get('Push\Model\SendHistoryTable');
        $predicate = new \Zend\Db\Sql\Predicate\Predicate();
        $predicate = $predicate->lessThanOrEqualTo('send_datetime', $deleteDate);
        $sendHistoryTable->delete($predicate);
        $sql = "ALTER TABLE `send_history` ENGINE InnoDB";
        $adapter->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
        
        echo 'END  経過時間：' . gmdate("H:i:s", (time()-$this->startInterval));
        echo "\n";
    }
    
    /**
     * 通知実行
     */
    public function sendPushAction()
    {
        $sendId = $this->request->getParam('sendId', false);    // 即時配信の場合、send_history_table_idが引数
        
        $sendHistoryTable = $this->getServiceLocator()->get('Push\Model\SendHistoryTable');
        $subscriberListTable = $this->getServiceLocator()->get('Push\Model\SubscriberListTable');
        
        if($sendId){
            $history = $sendHistoryTable->select(['send_history_table_id' => $sendId], true);
        }else{
            $history = $sendHistoryTable->fetchAll();
        }
        
        foreach($history as $row){
            // 即時配信 or 未送信で予約時刻を経過した場合
            if($sendId || (!$row->send_datetime && strtotime($row->schedule_datetime) < time())){
                $googleAnalyticsAccount = $googleAnalyticsAccountTable->selectOne(['webproperty_id' => $row->webproperty_id]);
                if(!$googleAnalyticsAccount) continue;
                if($row->address1){
                    $subscriberList = $subscriberListTable->select(['webproperty_id' => $row->webproperty_id, 'address1' => $row->address1], true);
                }else{
                    $subscriberList = $subscriberListTable->select(['webproperty_id' => $row->webproperty_id], true);
                }
                $count = 0;
                foreach($subscriberList as $subscriber){
                    // 先に送信時間を設定しておく
                    $row->send_datetime = date("Y-m-d H:i:s");
                    $sendHistoryTable->update($row);
                    
                    if($subscriber->browser === 'chrome'){
                        $cmd = 'curl --header "Authorization: key=' . $googleAnalyticsAccount->api_key .
                        '" --header Content-Type:"application/json" ' . $config['api']['google']['gcm']['endpoint'] .
                        ' -d "{\\"registration_ids\\":[\\"' . $subscriber->subscription_id . '\\"]}"';
                        $output = "";
                        exec($cmd, $output);
                        if(isset($output[0]) && $output[0]){
                            $res = \Zend\Json\Json::decode($output[0]);
                            if($res->success == 0 || $res->failure > 0){
                                $subscriberListTable->delete(['subscription_id' => $subscriber->subscription_id]);
                                continue;
                            }
                        }
                    }else if($subscriber->browser === 'firefox'){
                        $cmd = 'curl --header "TTL: 60" --header "Content-Length: 0" -d "content=" ' . $config['api']['mozilla']['firefox']['endpoint'] .
                        '/' . $subscriber->subscription_id;
                        $output = "";
                        exec($cmd, $output);
                        if(isset($output[0]) && $output[0]){
                            try{
                                $res = \Zend\Json\Json::decode($output[0]);
                                if($res->error){
                                    $subscriberListTable->delete(['subscription_id' => $subscriber->subscription_id]);
                                    continue;
                                }
                            }catch(\Exception $e){
                                // ブラウザを閉じている場合、「Finished Routing」というレスが返却される
                            }
                        }
                    }
                    
                    if($this->verbose){
                        echo $cmd . "\n";
                    }
                
                    $count++;
                    
                    // 300件毎に1秒待つ
                    if($count % 300 == 0) sleep(1);
                }
                if($count > 0) {
                    $row->sent_user_count = $count;
                }
                $row->send_datetime = date("Y-m-d H:i:s");
                $sendHistoryTable->update($row);
            }
        }
        
        echo 'END  経過時間：' . gmdate("H:i:s", (time()-$this->startInterval));
        echo "\n";
    }
    

    /**
     * 国土交通省のAPIでジオコーディング
     * http://www.finds.jp/wsdocs/rgeocode/index.html.ja
     * HttpControllerで実装しているため、実行しなくても問題ないはず・・・
     */
    public function rgeocodeAction()
    {
        $subscriberListTable = $this->getServiceLocator()->get('Push\Model\SubscriberListTable');
        foreach($subscriberListTable->fetchAll() as $row){
            // 逆ジオコーディング
            if($row->address1 == null && $row->latitude && $row->longitude){
                $config = $this->getServiceLocator()->get('Config');
                $rgeoUrl = sprintf($config['api']['rgeocode'], $row->latitude, $row->longitude);
                $json = file_get_contents($rgeoUrl);
                if(!$json || $json === FALSE) continue;
                try{
                    $result = \Zend\Json\Json::decode($json);
                    if(isset($result->result->prefecture->pname)){
                        $row->address1 = $result->result->prefecture->pname;
                    }
                    if(isset($result->result->municipality->mname)){
                        $row->address2 = $result->result->municipality->mname;
                    }
                    if(isset($result->result->local[0]->section)){
                        $row->address3 = $result->result->local[0]->section;
                    }
                    $subscriberListTable->update($row);
                }catch(\Exception $e){
                    continue;
                }
            }
        }
        
        echo 'END  経過時間：' . gmdate("H:i:s", (time()-$this->startInterval));
        echo "\n";
    }
}
