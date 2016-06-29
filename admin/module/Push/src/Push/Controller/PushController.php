<?php
namespace Push\Controller;

use Push\Model\Dao\PushLog;

/**
 * Class PushController
 * @package Push\Controller
 */
class PushController extends CommonController
{
    const SEND_COMAND = 'php -f /var/www/push/public/index.php sp';
    
    public function initDispatch(){
       return false;
    }
    
    /**
     * GCMの設定
     * @return \Zend\View\Model\ViewModel
     */
    public function gcmAction(){
        return $this->viewModel;
    }
    
    public function gcmSettingAction(){
        $projectNumber = $this->params()->fromPost('project', false);
        $key = $this->params()->fromPost('key', false);
        if(!$projectNumber || !$key) {
            return $this->redirect()->toUrl("/");
        }
        
        $userTable = $this->serviceLocator->get('Push\Model\Dao\UserTable');
        $user = $userTable->selectOne(['table_id' => $this->userUtil->get('table_id')]);
        $user->gcm_key = $key;
        $user->gcm_project_number = $projectNumber;
        $user = $userTable->update($user);
        $this->userUtil->signIn($user);
        
        $manifestJson =
<<<EOT
{
    "name": "Push Sample",
    "short_name": "Push Sample",
    "icons": [{ "sizes": "192x192" }],
    "display": "standalone",
    "gcm_sender_id": "{$projectNumber}",
    "//": "gcm_user_visible_only is only needed until Chrome 44 is in stable ",
    "gcm_user_visible_only": true
}
EOT;
        $file = __DIR__ . "/../../../../../public/allow/manifest/{$user->table_id}.json";
        $fp = fopen($file, "w+");
        flock($fp, LOCK_EX);
        fwrite($fp, $manifestJson);
        flock($fp, LOCK_UN);
        fclose($fp);
        
        return $this->redirect()->toUrl('/');
    }

    /**
     * プッシュ通知画面
     * @return \Zend\View\Model\ViewModel
     */
    public function pushAction(){
        return $this->viewModel;
    }
    
    /**
     * 通知実行
     * @return \Zend\Http\Response
     */
    public function sendAction(){
        $title = $this->params()->fromPost('title', false);
        $message = $this->params()->fromPost('msg', false);
        $link = $this->params()->fromPost('link', false);
        if(!$title || !$message || !$link) {
            return $this->redirect()->toUrl("/");
        }
        
        $pushLogTable = $this->getServiceLocator()->get('Push\Model\Dao\PushLogTable');
        $pushLog = new PushLog();
        $pushLog->user_table_id = $this->userUtil->get('table_id');
        $pushLog->title = $title;
        $pushLog->message = $message;
        $pushLog->link = $link;
        $pushLog->count = 0;
        $pushLog->add_datetime = date("Y-m-d H:i:s");
        try{
            $pushLog = $pushLogTable->insert($pushLog);
        }catch(\Exception $e){
            return $this->redirect()->toUrl("/");
        }
        
        exec(self::SEND_COMAND . " --sendId={$pushLog['table_id']} -v > /dev/null &");

        return $this->redirect()->toUrl('/');
    }
    
    /**
     * 通知履歴表示
     * @return \Zend\View\Model\ViewModel
     */
    public function historyAction() {
        $pushLogTable = $this->getServiceLocator()->get('Push\Model\Dao\PushLogTable');
        $history = $pushLogTable->selectOrderByAddDatetime(["user_table_id" => $this->userUtil->get('table_id')]);
        
        $showNotificationLogTable = $this->getServiceLocator()->get('Push\Model\Dao\ShowNotificationLogTable');
        $clickNotificationLogTable = $this->getServiceLocator()->get('Push\Model\Dao\ClickNotificationLogTable');
        
        $viewValue = [];
        foreach($history as $row){
            $showNotificationLog = $showNotificationLogTable->select(['push_log_table_id' => $row->table_id], true);
            $clickNotificationLog = $clickNotificationLogTable->select(['push_log_table_id' => $row->table_id], true);
            
            $row->show_count = count($showNotificationLog);
            $row->click_count = count($clickNotificationLog);
            $viewValue[] = $row;
        }
        
        $this->viewModel->setVariable('history', $viewValue);
        return $this->viewModel;
    }
}
