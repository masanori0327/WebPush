<?php
namespace Push\Model\Dao;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Adapter\Adapter as Zend_Db_Adapter;

class DbAdapter implements FactoryInterface
{
    /**
     * Create db adapter
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Zend_Db_Adapter
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        return new Zend_Db_Adapter($config['db']['push']);
    }
}
