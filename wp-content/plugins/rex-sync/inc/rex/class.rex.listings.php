<?php
namespace Rex\API;

require_once 'class.rex.php';

class Rex_Listings extends Rex{
    var $class_name = 'Listings';

    public function getPropertiesForListings($listing_ids, $listing_viewstate_id = false, $return_format = false){
        $method = $this->get_request_method($this->class_name, __FUNCTION__);
        $token = $this->get_token();

        if( !is_array($listing_ids))
            $listing_ids = array($listing_ids);

        $args = array(
            'listing_ids' => $listing_ids,
        );

        if($listing_viewstate_id){
            $args['listing_viewstate_id'] = $listing_viewstate_id;
        }

        if($return_format){
            $args['return_format'] = $return_format;
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
}