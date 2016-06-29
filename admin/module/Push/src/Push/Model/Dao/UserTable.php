<?php
namespace Push\Model\Dao;

use Zend\Db\TableGateway\TableGateway;

class UserTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    /**
     * 全ての行を返す
     *
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    /**
     * データを挿入する
     */
    public function insert(User $user)
    {
        $data = array();
        foreach(get_object_vars($user) as $key => $value){
            if($key === 'table_key' || $key === 'update_datetime') continue;
            $data[$key] = $user->$key;
        }
        $data['table_key'] = $this->createTableKey();

        $this->tableGateway->insert($data);

        $user->table_key = $data['table_key'];
        $user->update_datetime = null;

        return $user;
    }

    /**
     * データを更新する
     */
    public function update($user)
    {
        if(gettype($user) === 'array'){
            $this->tableGateway->update($data, array(
                'table_id' => $user->table_id,
            ));
        }else if(gettype($user) === 'object'){
            $data = array();
            foreach(get_object_vars($user) as $key => $value){
                if($key === 'table_key' || $key === 'add_datetime' || $key === 'update_datetime') continue;

                $data[$key] = $user->$key;
            }

            $this->tableGateway->update($data, array(
                'table_id' => $user->table_id,
            ));

            return $user;
        }
    }

    /**
     * key の重複を確認する
     */
    public function isDuplicateTableKey($tableKey)
    {
        return $this->selectOne(array(
            'table_key' => $tableKey
        ));
    }

    /**
     * key を生成する
     */
    public function createTableKey()
    {
        $tableKey = bin2hex(openssl_random_pseudo_bytes(10));
        while($this->isDuplicateTableKey($tableKey)){
            $tableKey = bin2hex(openssl_random_pseudo_bytes(10));
        }
        return $tableKey;
    }

    /**
     * 1行を返す
     */
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
        return $row;
    }

    /**
     * 複数行を返す
     */
    public function select(array $where, $ignoreActiveFlg = false)
    {
        if($ignoreActiveFlg === false){
            $where['flg_active'] = 'yes';
        }

        $select = $this->tableGateway->getSql()->select();
        $select->where($where);
        $rowSet = $this->tableGateway->selectWith($select);
        if($rowSet === null){
            return array();
        }

        return $rowSet;
    }

    /**
     * アダプタを返す
     *
     * @return \Zend\Db\Adapter\AdapterInterface
     */
    public function getAdapter()
    {
        return $this->tableGateway->getAdapter();
    }
}
