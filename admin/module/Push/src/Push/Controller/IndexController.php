<?php
namespace Push\Controller;

/**
 * Class IndexController
 */
class IndexController extends CommonController
{
    public function initDispatch(){
        return false;
    }
    
    public function indexAction(){
        if(is_array($this->userUtil->get('gcm_key')) || is_array($this->userUtil->get('gcm_project_number'))){
            return $this->forward()->dispatch('Push\Controller\Push', [
                'action' => 'gcm',
            ]);
        }
        
        return $this->forward()->dispatch('Push\Controller\Push', [
            'action' => 'push',
        ]);
    }

}
