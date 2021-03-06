<?php
namespace Push\Model\Dao;

class ShowNotificationLogTable extends AbstractCommonTable
{
    public function insert($table)
    {
        $table = $this->exchangeObj($table);
        
        $this->tableGateway->insert($table);
        
    }
    
    public function update($table) {}
    
    private function exchangeObj($table)
    {
        if($table instanceof ShowNotificationLog){
            $array = [];
            foreach(get_object_vars($table) as $key => $value){
                $array[$key] = $value;
            }
            $table = $array;
        }
        
        return $table;
    }
}
