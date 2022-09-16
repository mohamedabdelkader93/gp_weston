<?php
namespace Rex\API;

require_once 'class.rex.php';

class Rex_AccountUsers extends Rex{
    var $class_name = 'AccountUsers';

    public function search($criteria=array(), $order_by=false, $offset=0, $limit=50, $create_viewstate=true, $search_state='active', $result_format='ids', $extra_options = false){

        $results = parent::search($criteria, $order_by, $offset, $limit, $create_viewstate, $search_state, $result_format, $extra_options);

        return $results;
    }

    public function findByEmail($email){
        $search_args = array(
            array(
                'name' => 'email',
                'value' => $email
            )
        );

        $result = $this->search($search_args);

        if($result->result && $result->result->rows){
            $user = $this->read($result->result->rows[0]);
            return $user->result;
        }

        return false;

    }
}