<?php
namespace Push\Model\Dao;

class SubscriberListTable extends AbstractCommonTable
{
    public function insert($table)
    {
        $table = $this->exchangeObj($table);
        
        $this->tableGateway->insert($table);
    }
    
    public function update($table)
    {
        $table = $this->exchangeObj($table);
        
        $this->tableGateway->update($table, array(
            'subscription_id' => $table['subscription_id']
        ));
    }
    
    public function insertOrUpdate($table)
    {
        $table = $this->exchangeObj($table);
    
        $row = $this->selectOne(['subscription_id' => $table['subscription_id']], true);
        if($row){
            $this->update($table);
        }else{
            $this->insert($table);
        }
    }
    
    private function exchangeObj($table)
    {
        if($table instanceof SubscriberList){
            $array = [];
            foreach(get_object_vars($table) as $key => $value){
                $array[$key] = $value;
            }
            $table = $array;
        }
        
        return $table;
    }
    
    public function delete($where)
    {
        $result = $this->tableGateway->delete($where);
        return $result;
    }
}
