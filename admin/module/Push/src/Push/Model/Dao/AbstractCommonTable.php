<?php
namespace Push\Model\Dao;

use Zend\Db\TableGateway\TableGateway;

abstract class AbstractCommonTable
{
    protected $tableGateway;
    protected $lastExeSql = "";

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public abstract function insert($table);

    public abstract function update($table);
    
    public function delete($array)
    {
        $result = $this->tableGateway->delete($array);
        return $result;
    }

    public function selectOne(array $where, $ignoreActiveFlg = false)
    {
        if($ignoreActiveFlg === false){
            $where['flg_active'] = 'yes';
        }

        $select = $this->tableGateway->getSql()->select();
        $select->where($where);
        $select->limit(1);
        $rowSet = $this->tableGateway->selectWith($select);
        $row = $rowSet->current();
        if (!$row) {
            return false;
        }
        
        // SQL確認用コード
        $this->lastExeSql = $select->getSqlString($this->getAdapter()->getPlatform());

        return $row;
    }

    public function select(array $where, $ignoreActiveFlg = false)
    {
        if($ignoreActiveFlg === false){
            $where['flg_active'] = 'yes';
        }

        $select = $this->tableGateway->getSql()->select();
        $select->where($where);
        $rowSet = $this->tableGateway->selectWith($select);

        // SQL確認用コード
        $this->lastExeSql = $select->getSqlString($this->getAdapter()->getPlatform());

        if($rowSet === null){
            return array();
        }

        return $rowSet;
    }
    
    public function isDuplicateTableKey($tableKey, $tableKeyVal)
    {
        return $this->selectOne(array(
            $tableKey => $tableKeyVal
        ));
    }
    
    public function createTableKey($tableKey)
    {
        $tableKeyVal = bin2hex(openssl_random_pseudo_bytes(10));
        while($this->isDuplicateTableKey($tableKey, $tableKeyVal)){
            $tableKeyVal = bin2hex(openssl_random_pseudo_bytes(10));
        }
        return $tableKeyVal;
    }
    
    public function getLastExeSql()
    {
        return $this->lastExeSql;
    }

    public function getAdapter()
    {
        return $this->tableGateway->getAdapter();
    }
}
