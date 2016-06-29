<?php
namespace Push\Model\Dao;

class PushLogTable extends AbstractCommonTable
{
    public function selectOneOrderByAddDatetime(array $where){
        $select = $this->tableGateway->getSql()->select();
        $select->where($where);
        $select->order("add_datetime desc");
        $select->limit(1);
        $rowSet = $this->tableGateway->selectWith($select);
        $row = $rowSet->current();
        if (!$row) {
            return false;
        }
        
        $this->lastExeSql = $select->getSqlString($this->getAdapter()->getPlatform());
        
        return $row;
    }
    
    public function selectOrderByAddDatetime(array $where){
        $select = $this->tableGateway->getSql()->select();
        $select->where($where);
        $select->order("add_datetime desc");
        $rowSet = $this->tableGateway->selectWith($select);
        
        $this->lastExeSql = $select->getSqlString($this->getAdapter()->getPlatform());

        if($rowSet === null){
            return array();
        }

        return $rowSet;
    }
    
    public function update($table) {
        $table = $this->exchangeObj($table);
        
        $this->tableGateway->update($table, array(
            'table_id' => $table['table_id'],
        ));
    }
    
    public function insert($table) {
        $table = $this->exchangeObj($table);
        
        $this->tableGateway->insert($table);
        
        $table['table_id'] = $this->tableGateway->getLastInsertValue();
        
        return $table;
        
    }
    
    public function delete($table) {
        $table = $this->exchangeObj($table);
        $result = $this->tableGateway->delete($table);
        return $result;
    }
    
    private function exchangeObj($table) {
        if($table instanceof PushLog){
            $array = [];
            foreach(get_object_vars($table) as $key => $value){
                $array[$key] = $value;
            }
            $table = $array;
        }
        
        return $table;
    }
}
