<?php
namespace Rex\API;

require_once 'class.rex.cache.php';

class Rex{
    var $host = 'https://api.rexsoftware.com/v1/rex';
    var $user_name = '';
    var $password = '';
    var $token = '';
    var $is_decoded = true;
    var $cache_object = null;
    var $is_cached = true;
    var $last_response = false;

    public function __construct($user_name, $password){
        $this->user_name = $user_name;
        $this->password = $password;
    }

    public function get_token(){

        $this->token = $this->get_cache('token');
        if($this->token)
            return $this->token;

        $args = array(
            'method'=>'Authentication::login',
            'args' => array(
                'email'=>$this->user_name,
                'password'=>$this->password
            ),
            'token'=>''
        );


        $results = $this->request($args);

        if($results) {
            $result_object = $this->decode($results);
            $this->token = $result_object->result;
        }

        if(!$this->token)
            throw new \Exception('Cannot get token from Rex');

        $this->set_cache('token', $this->token);

        return $this->token;
    }

    public function request($args=array()){

        $remote_args = [
            'timeout' => 15,
            'headers' => [
                'Content-Type' => 'application/json',
                'X-App-Identifier' => 'Integration:RexSyncPlugin'
            ]
        ];
        $remote_url = $this->host."/".$args['method'];
        $remote_args['body'] = json_encode($args['args']);
        if(isset($args['token']) && $args['token']){
            $remote_args['headers']['Authorization'] = 'Bearer '.$args['token'];
        }

        $remote_post = wp_remote_post( $remote_url, $remote_args );
        $http_code = wp_remote_retrieve_response_code($remote_post);
        if($http_code != 200)
            return false;

        $this->last_response = wp_remote_retrieve_body($remote_post);
        return $this->last_response;
    }

    public function read($id, $fields = false, $extra_fields = false){
        $method = $this->get_request_method($this->class_name, __FUNCTION__);
        $token = $this->get_token();

        $args = array(
            'id' => $id,
        );

        if($extra_fields){
            $args['extra_fields'] = $extra_fields;
        }

        if($fields){
            $args['fields'] = $fields;
        }

        $request_args = array(
            'method' => $method,
            'token' => $token,
            'args' => $args
        );

        $results = $this->request($request_args);
        $results = $this->decode($results);

        return $results;
    }

    public function search($criteria=array(), $order_by=false, $offset=0, $limit=50, $create_viewstate=false, $search_state='active', $result_format='stubs', $extra_options = false){
        $method = $this->get_request_method($this->class_name, __FUNCTION__);
        $token = $this->get_token();

        $args = array();
        if($criteria){
            $args['criteria'] = $criteria;
        }

        if($order_by){
            $args['order_by'] = $order_by;
        }

        $args['offset'] = $offset;
        $args['limit'] = $limit;
        $args['create_viewstate'] = $create_viewstate;
        $args['search_state'] = $search_state;
        $args['result_format'] = $result_format;


        $request_args = array(
            'method' => $method,
            'token' => $token,
            'args' => $args
        );

        $results = $this->request($request_args);
        $results = $this->decode($results);

        return $results;
    }

    public function set_cache_object(Rex_Cache $object){
        $this->cache_object = $object;
    }

    protected function get_request_method($class_name, $method){
        return "{$class_name}::{$method}";
    }

    protected function decode($result){
        if($this->is_decoded && !is_object($result)){
            return json_decode($result);
        }

        return $result;
    }

    protected function set_cache($key, $value){
        if( $this->is_cached && $this->cache_object){
            $this->cache_object->set_cache($key, $value);
        }
    }

    protected function get_cache($key){
        if( $this->is_cached && $this->cache_object ){
            return $this->cache_object->get_cache($key);
        }

        return false;
    }



}