<?php
namespace Push\Util;

use Zend\Session\Container;
use Push\Model\Dao\User;

/**
 * Class UserUtil
 */
class UserUtil
{
    private $container;

    public function __construct(){
        $this->container = new Container('User');
    }

    public function signIn(User $user){
        $this->container->userData = $user->getArrayCopy();
    }

    public function isSignOn(){
        if(is_array($this->container->userData)){
            return true;
        }else{
            return false;
        }
    }

    public function signOut(){
        $this->container->userData = null;
    }

    public function get($key=null){
        return (isset($this->container->userData[$key]) ? $this->container->userData[$key] : $this->container->userData);
    }
}