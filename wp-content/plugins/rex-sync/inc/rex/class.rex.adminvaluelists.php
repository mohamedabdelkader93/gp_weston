<?php
namespace Rex\API;

require_once 'class.rex.php';

class Rex_AdminValueLists extends Rex{
    var $class_name = 'AdminValueLists';

    public function getListValues($list_name, $include_system_values = true, $include_omitted_values = true, $retrieve_meta = true){
        $method = $this->get_request_method($this->class_name, __FUNCTION__);
        $token = $this->get_token();

        $args = array(
            'method' => $method,
            'token' => $token,
            'args' => array(
                'list_name' => $list_name,
                'include_system_values' => $include_system_values,
                'include_omitted_values' => $include_omitted_values,
                'retrieve_meta' => $retrieve_meta
            )
        );
        
        $results = $this->request($args);
        $results = $this->decode($results);

        return $results;
    }
}