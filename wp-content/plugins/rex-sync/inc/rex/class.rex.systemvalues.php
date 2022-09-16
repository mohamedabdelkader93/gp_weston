<?php
namespace Rex\API;
require_once 'class.rex.php';

class Rex_SystemValues extends Rex{
    var $class_name = 'SystemValues';

    public function getCategoryValues($list_name, $ids_only = true){
        $method = $this->get_request_method($this->class_name, __FUNCTION__);
        $token = $this->get_token();

        $args = array(
            'method' => $method,
            'token' => $token,
            'args' => array(
                'list_name' => $list_name,
                'ids_only' => $ids_only
            )
        );
        
        $results = $this->request($args);
        $results = $this->decode($results);

        return $results;
    }

    public function getValueCategories(){
        $method = $this->get_request_method($this->class_name, __FUNCTION__);
        $token = $this->get_token();

        $args = array(
            'method' => $method,
            'token' => $token,
            'args' => array(
            )
        );

        $results = $this->request($args);
        $results = $this->decode($results);

        return $results;
    }

    public function autocompleteCategoryValues($list_name, $search_name, $limit = 50){
        $method = $this->get_request_method($this->class_name, __FUNCTION__);
        $token = $this->get_token();

        $args = array(
            'method' => $method,
            'token' => $token,
            'args' => array(
                'list_name' => $list_name,
                'search_string' => $search_name,
                'limit' => $limit
            )
        );

        $results = $this->request($args);
        $results = $this->decode($results);

        return $results;
    }

}