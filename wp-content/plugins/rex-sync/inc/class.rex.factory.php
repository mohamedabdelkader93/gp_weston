<?php
namespace Rex\API;

require_once 'rex/class.rex.listings.php';
require_once 'rex/class.rex.publishedlistings.php';
require_once 'rex/class.rex.adminvaluelists.php';
require_once 'rex/class.rex.systemvalues.php';
require_once 'rex/class.rex.properties.php';
require_once 'rex/class.rex.accountusers.php';
require_once 'rex/class.rex.cache.php';

class RexFactory{
    public static function create($class_name, $user_name = '', $password = ''){

        $class = "Rex\API\Rex_{$class_name}";
        $object = new $class($user_name, $password);
        
        return $object;
    }
}

class RexAPI{
    static $instances = array();
    static $cache_object = false;

    private static function get_cache_object(){

        self::$cache_object = new Rex_Cache();

        return self::$cache_object;
    }

    private static function get_instance($class_name){

        $settings = \Rex\Sync\Loader::get_settings();
        $user_name = $settings['user_login'];
        $password = $settings['user_password'];

        if( !isset(self::$instances[$class_name])){
            self::$instances[$class_name] = RexFactory::create($class_name, $user_name, $password);
        }

        return self::$instances[$class_name];
    }

    static function get_system_values($name){
        $cached = self::get_cache_object()->get_cache('SystemValues_'.$name);
        if($cached)
            return $cached;
        $instance = self::get_instance('SystemValues');
        $list = $instance->getCategoryValues($name);

        if( $list ){
            self::get_cache_object()->set_cache('SystemValues_'.$name, $list->result);
            return $list->result;
        }

        return false;
    }

    static function get_property_categories(){
        return self::get_system_values('property_category');
    }

    static function get_listing_categories(){
        return self::get_system_values('listing_category');
    }

    static function get_listing_sub_categories($name='residential'){
        return self::get_system_values('listing_subcat_'.$name);
    }

    static function get_property_sub_categories($name='residential'){
        return self::get_system_values('property_subcat_'.$name);
    }

    static function get_listing_system_states(){
        return self::get_system_values('listing_system_state');
    }

    static function get_local_agency(){
        return self::get_system_values('local_agency');
    }

    static function get_account_users(){
        return self::get_system_values('account_users');
    }

    static function get_system_value_list_names(){
        $instance = self::get_instance('SystemValues');
        $list = $instance->getValueCategories();

        if( $list )
            return $list->result;

        return false;
    }

    static function get_price_lists(){
        $prices = get_field('rex_searchable_prices', 'option');
        $prices = explode(',', $prices);
        $prices = array_map('trim', $prices);
        foreach($prices as &$price){
            $price *= 1000;
        }

        return $prices;
    }

    static function get_sort_list(){
        return array(
            'price-low' => 'Price Low-High',
            'price-high' => 'Price High-Low',
            'date' => 'Listing Date',
//            'bedrooms' => 'Bedrooms',
//            'bathrooms' => 'Bathrooms',
//            'garages' => 'Garages',
        );
    }

    static function format_currency($value){
        return '$'.number_format($value, 0);
    }

    static function get_quick_listing_lens(){
        return array(
            'sale' => 'Buying',
            'rental' => 'Renting'
        );
    }

    static function get_listing_lens(){
        return array(
            'sale' => 'Buying',
            'rental' => 'Renting',
            'sold' => 'Sold',
            'leased' => 'Leased',
        );
    }

    static function get_property($id, $fields = false, $extra_fields = false){
        $instance = self::get_instance('Properties');
        $list = $instance->read($id, $fields, $extra_fields);

        if( $list )
            return $list->result;

        return false;
    }

    static function get_listing($id, $fields = false, $extra_fields = false){

        $hash_key = base64_encode(json_encode(array($id, $fields, $extra_fields)));
        $cached = self::get_cache_object()->get_cache($hash_key);
        if($cached){
            return $cached;
        }

        $instance = self::get_instance('Listings');
        $list = $instance->read($id, $fields, $extra_fields);


        if( $list ){
            self::get_cache_object()->set_cache($hash_key, $list->result);
            return $list->result;
        }

        return false;
    }

    static function get_properties_for_listings($listing_ids, $listing_viewstate_id = false, $return_format = false){
        $instance = self::get_instance('Listings');
        $list = $instance->getPropertiesForListings($listing_ids, $listing_viewstate_id, $return_format);

        if( $list )
            return $list->result;

        return false;
    }

    static function find_suburbs($name){
        $instance = self::get_instance('SystemValues');
        $list = $instance->autocompleteCategoryValues('suburbs', $name);

        if( $list )
            return $list->result;

        return false;
    }

    static function listings_search($args=array()){

        $hash_key = base64_encode(json_encode($args));
        $cached = self::get_cache_object()->get_cache($hash_key);
        if($cached){
            return $cached;
        }

        $criteria = array();

        $type = $args['type'];
        $type = is_array($type)?$type:array($type);


        $listing_categories = array();
        if(in_array('rental', $type)){
            $listing_categories += array('residential_rental','commercial_rental','holiday_rental',);
        }

        if(in_array('sale', $type)){
            $listing_categories += array('residential_sale','land_sale','business_sale', 'commercial_sale','rural_sale');
        }

        if($args['listing']){
            $listing_categories += is_array($args['listing'])?$args['listing']:array($args['listing']);
        }

        $listing_categories = array_unique($listing_categories);
        if(count($listing_categories)){
            $criteria[] = array(
                'name' => 'listing.listing_category_id',
                'type' => 'in',
                'value' => $listing_categories
            );
        }

        $listing_states = array();
        if(in_array('sold', $type)){
            $listing_states += array('sold');
        }

        if(in_array('leased', $type)){
            $listing_states[] = 'leased';
        }

        if(in_array('current', $type)){
            $listing_states[] = 'current';
        }

        if(count($listing_states)){
            $criteria[] = array(
                'name' => 'listing.system_listing_state',
                'type' => 'in',
                'value' => $listing_states
            );
        }else{
            $criteria[] = array(
                'name' => 'listing.system_listing_state',
                'value' => 'current'
            );
        }


        if($args['category']){
            $criteria[] = array(
                'name' => 'property.property_category_id',
                'type' => 'in',
                'value' => is_array($args['category'])?$args['category']:array($args['category'])
            );
        }

        if($args['subcat']){
            $criteria[] = array(
                'name' => 'listing.subcategories',
                'type' => 'intersect_any',
                'value' => is_array($args['subcat'])?$args['subcat']:array($args['subcat'])
            );
        }

        if($args['min-price']){
            $criteria[] = array(
                'name' => 'listing.price_match',
                'type' => '>=',
                'value' => $args['min-price']
            );
        }

        if($args['max-price']){
            $criteria[] = array(
                'name' => 'listing.price_match',
                'type' => '<=',
                'value' => $args['max-price']
            );
        }

        if($args['agent']){
            $criteria[] = array(
                'name' => 'listing.listing_agent_id',
                'value' => $args['agent']
            );
        }

        if($args['bathrooms']){
            $criteria[] = array(
                'name' => 'property.attr_bathrooms',
                'value' => $args['bathrooms']
            );
        }

        if($args['bedrooms']){
            $criteria[] = array(
                'name' => 'property.attr_bedrooms',
                'value' => $args['bedrooms']
            );
        }

        if($args['garages']){
            $criteria[] = array(
                'name' => 'property.attr_garages',
                'value' => $args['garages']
            );
        }

        if($args['cars']){
            $criteria[] = array(
                'name' => 'property.attr_carports',
                'value' => $args['cars']
            );
        }

        if($args['suburbs']){
            $criteria[] = array(
                'name' => 'property.adr_suburb_or_town',
                'type' => 'in',
                'value' => is_array($args['suburbs'])?$args['suburbs']:array($args['suburbs'])
            );
        }

        if($args['inspections']){
            $criteria[] = array(
                'name' => 'listing.event_date',
                'type' => '>=',
                'value' => '2000-01-01'
            );
        }

        if($args['system_modtime']){
            $criteria[] = array(
                'name' => 'listing.system_modtime',
                'type' => '>',
                'value' => $args['system_modtime']
            );
        }


        $order_by = array();

        if($args['order']){
            switch($args['order']){
                case 'price-low': $order_by['price_match'] = 'ASC'; break;
                case 'price-high': $order_by['price_match'] = 'DESC'; break;
//                case 'bedrooms': $order_by['attr_bedrooms'] = 'DESC'; break;
//                case 'bathrooms': $order_by['attr_bathrooms'] = 'DESC'; break;
//                case 'garages': $order_by['attr_garages'] = 'DESC'; break;
                case 'system_modtime': $order_by['system_modtime'] = 'ASC'; break;
                default:
                    break;
            }
        }

        if(empty($order_by)){
            $order_by['state_date'] = 'DESC';
        }

        $page_size = 50;
        if($args['page-size']){
            $page_size = intval($args['page-size']);
        }
        $page = 1;
        if($args['page']){
            $page = intval($args['page']);
        }

        $offset = $page_size * ($page-1);
        if(isset($args['offset'])){
            $offset += intval($args['offset']);
        }

        $instance = self::get_instance('PublishedListings');
        $list = $instance->search($criteria, $order_by, $offset, $page_size, false, 'active', 'ids');

        $list_result = $list->result;

        $result = array(
            'rows' => $list_result->rows,
            'total' => $list_result->total
        );

        self::get_cache_object()->set_cache($hash_key, $result);

        return $result;
    }


    static function parse_coordinates($system_geo){
        $temp = trim($system_geo, 'POINT()');
        list($lat, $long) = explode(' ', $temp);

        return array($lat, $long);
    }

    static function find_user_by_email($email){
        $hash_key = 'user_'.$email;
        $cached = self::get_cache_object()->get_cache($hash_key);
        if($cached)
            return $cached;

        $instance = self::get_instance('AccountUsers');
        $user = $instance->findByEmail($email);

        self::get_cache_object()->set_cache($hash_key, $user);

        return $user;
    }
}