<?php
namespace Push\Controller;

use Push\Model\Dao\GoogleOAuth;
use Push\Model\Dao\User;
use Push\Util\GoogleOAuthUtil;

/**
 * Class UserController
 */
class UserController extends CommonController
{
    public function initDispatch(){
        return false;
    }
    
    public function signInAction() {
        return $this->viewModel;
    }
    
    public function signOutAction() {
        $this->userUtil->signOut();
        return $this->redirect()->toUrl('/push/user/sign-in');
    }

    public function signinWithGoogleAction(){
        $sm = $this->getServiceLocator();
        $config = $sm->get('Config');

        $googleOAuth = new GoogleOAuth();
        $googleOAuth->client_id = $config['api']['google']['client_id'];
        $googleOAuth->client_secret = $config['api']['google']['client_secret'];
        $googleOAuth->callback = $config['api']['google']['callback'];
        $googleOAuth->scope = $config['api']['google']['scope'];
        $googleOAuth->access_type = 'online';
        $googleOAuth->approval_prompt = 'auto';
        $googleOAuth->state = $this->params()->fromQuery('state', 'accounts');

        $googleOAuthUtil = new GoogleOAuthUtil($googleOAuth);

        return $this->redirect()->toUrl($googleOAuthUtil->getOAuthUrl());
    }
    
    public function callbackAction(){
        $sm = $this->getServiceLocator();
        $config = $sm->get('Config');
        
        // 同意しないなどで戻ったきた場合
        if($this->getRequest()->getQuery('error') || !$this->getRequest()->getQuery('code')){
            return $this->redirect()->toUrl('/push/user/sign-out');
        }
        
        // アクセストークン取得
        $googleOAuth = new GoogleOAuth();
        $googleOAuth->client_id = $config['api']['google']['client_id'];
        $googleOAuth->client_secret = $config['api']['google']['client_secret'];
        $googleOAuth->callback = $config['api']['google']['callback'];
        
        $googleOAuthUtil = new GoogleOAuthUtil($googleOAuth);
        $accessTokenResponse = $googleOAuthUtil->getAccessToken($this->params()->fromQuery('code'));
        
        // アクセストークンのバリデーション
        if(!$googleOAuthUtil->verifyAccessToken($accessTokenResponse->access_token)){
            return $this->redirect()->toUrl('/push/user/sign-out');
        }
        
        // ユーザ情報取得
        $userInfo = $googleOAuthUtil->request("https://www.googleapis.com/oauth2/v2/userinfo");
        if(!$userInfo || empty($userInfo->email)){
            return $this->redirect()->toUrl('/push/user/sign-out');
        }
        
        $userTable = $sm->get('Push\Model\Dao\UserTable');
        $existUser = $userTable->selectOne(array(
            'email' => $userInfo->email
        ));
        
        if(!$existUser){
            $user = new User();
            $user->email = $userInfo->email;
            $user->name = (property_exists($userInfo, 'name') ? $userInfo->name : null);
            $user->google_id = (property_exists($userInfo, 'id') ? $userInfo->id : null);
            $user->google_link = (property_exists($userInfo, 'link') ? $userInfo->link : null);
            $user->flg_active = 'yes';
            $user->add_datetime = date('Y-m-d H:i:s');
            
            $existUser = $userTable->insert($user);
        }
        
        // サインイン処理
        $this->userUtil->signIn($existUser);
        return $this->redirect()->toUrl('/');
    }
}
