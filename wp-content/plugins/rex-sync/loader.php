<?php
namespace Rex\Sync;

use Rex\API\RexAPI;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Loader{

    static $page_settings_slug = 'rex-sync-settings';
    static $page_manual_sync_slug = 'rex-sync-manual';
    static $page_queues_slug = 'rex-sync-queues';
    static $page_mapping_slug = 'rex-sync-mapping';
    static $page_logs_slug = 'rex-sync-logs';
    static $option_settings_key = 'rex-sync-settings';
    static $custom_field_prefix = '_rsc';

    /**
     * @var \WP_Error
     */
    static $errors;
    /**
     * @var \WP_Error
     */
    static $messages;

    static function load()
    {
        
        require_once __DIR__.'/inc/class.rex.factory.php';
        require_once __DIR__.'/inc/class.queue.php';
        require_once __DIR__.'/inc/class.logger.php';
        require_once __DIR__.'/inc/class.helper.php';

        self::$errors = new \WP_Error();
        self::$messages = new \WP_Error();

        add_filter('plugin_action_links_'.basename(__DIR__).DIRECTORY_SEPARATOR. basename(__DIR__).'.php', [__CLASS__, 'add_plugin_action_buttons'], 10);

        add_action('admin_init', [__CLASS__, 'admin_init']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'register_admin_scripts']);
        add_action('admin_menu', [__CLASS__, 'register_pages'], 20);
        add_action('init', [__CLASS__, 'register_post_types'], 0);
        add_action('init', [__CLASS__, 'handle_webhook']);
        add_action('add_meta_boxes', [__CLASS__, 'register_meta_boxes'] );


        add_action('wp_ajax_rsc_download_listings', [__CLASS__, 'ajax_rsc_download_listings']);

        add_action('rsc_3_minutes_event', [__CLASS__, 'auto_import_latest_queues']);

        add_action('wp_ajax_rsc_delete_log', [__CLASS__, 'ajax_delete_log']);

        if (! wp_next_scheduled ( 'rsc_5_minutes_event' )) {
            wp_schedule_event(time(), '5_minutes', 'rsc_5_minutes_event');
        }

        if (! wp_next_scheduled ( 'rsc_3_minutes_event' )) {
            wp_schedule_event(time(), '3_minutes', 'rsc_3_minutes_event');
        }
    }

    static function register_post_types(){
        self::register_post_type_listing();
        self::register_post_type_listing_agent();
        self::register_taxonomy_listing_category();
        self::register_taxonomy_listing_state();
    }

    static function admin_init(){
        self::save_settings();
        self::save_mapping_settings();
        self::delete_queues();

        self::test_insert_listing();
    }

    static function add_plugin_action_buttons($actions){

        $actions = array_merge([
            self::$page_settings_slug => '<a href="'.admin_url('admin.php?page='.self::$page_settings_slug).'" aria-label="Settings">Settings</a>'
        ], $actions);

        return $actions;
    }

    static function save_settings(){
        $settings = self::get_settings();
        if(wp_verify_nonce(Helper::POST('rsc-settings-nonce'), 'rsc-settings')){
            $post_settings = Helper::POST('rsc');
            $settings = wp_parse_args($post_settings, $settings);

            update_option(self::$option_settings_key, $settings);

            self::$messages->add('summary', 'Settings have been saved successfully');
        }

    }

    static function get_settings(){
        $default = [
            'user_login' => '',
            'user_password' => '',
            'listing_fields_mapping' => [
                'title' => '_rsc.related.listing_adverts.0.advert_heading',
                'content' => '_rsc.related.listing_adverts.0.advert_body',
            ],
            'listing_custom_fields_mapping' => [
                '_rsc.price_match' => '_rsc.price_match',
                '_rsc.price_advertise_as' => '_rsc.price_advertise_as',
                '_rsc.property.attr_bedrooms' => '_rsc.property.attr_bedrooms',
                '_rsc.property.attr_bathrooms' => '_rsc.property.attr_bathrooms',
                '_rsc.property.attr_toilets' => '_rsc.property.attr_toilets',
                '_rsc.property.attr_garages' => '_rsc.property.attr_garages',
                '_rsc.property.attr_buildarea' => '_rsc.property.attr_buildarea',
                '_rsc.property.attr_buildarea_m2' => '_rsc.property.attr_buildarea_m2',
                '_rsc.property.attr_landarea' => '_rsc.property.attr_landarea',
                '_rsc.property.attr_landarea_m2' => '_rsc.property.attr_landarea_m2',
                '_rsc.authority_type.text' => '_rsc.authority_type.text',
                '_rsc.listing_agent_1.id' => '_rsc.listing_agent_1.id',
                '_rsc.listing_agent_2.id' => '_rsc.listing_agent_2.id',
                '_rsc.property.adr_unit_number' => '_rsc.property.adr_unit_number',
                '_rsc.property.adr_street_number' => '_rsc.property.adr_street_number',
                '_rsc.property.adr_street_name' => '_rsc.property.adr_street_name',
                '_rsc.property.adr_suburb_or_town' => '_rsc.property.adr_suburb_or_town',
                '_rsc.property.adr_locality' => '_rsc.property.adr_locality',
                '_rsc.property.adr_state_or_region' => '_rsc.property.adr_state_or_region',
                '_rsc.property.adr_postcode' => '_rsc.property.adr_postcode',
                '_rsc.property.adr_country' => '_rsc.property.adr_country',
                '_rsc.property.system_search_key' => '_rsc.property.system_search_key',
                '_rsc.related.listing_images' => '_rsc.related.listing_images',
                '_rsc.related.listing_events' => '_rsc.related.listing_events',
            ],
            'agent_fields_mapping' => [
                'title' => '_rsc.name'
            ],
            'agent_custom_fields_mapping' => [
                '_rsc.first_name' => '_rsc.first_name',
                '_rsc.last_name' => '_rsc.last_name',
                '_rsc.email_address' => '_rsc.email_address',
                '_rsc.phone_direct' => '_rsc.phone_direct',
                '_rsc.phone_mobile' => '_rsc.phone_mobile',
                '_rsc.position' => '_rsc.position',
                '_rsc.profile_image' => '_rsc.profile_image',
            ]
        ];

        $settings = get_option(self::$option_settings_key);
        $settings = wp_parse_args($settings, $default);

        $settings = apply_filters('Rex/Sync/Loader/get_settings', $settings);

        return $settings;
    }

    static function save_mapping_settings(){
        $settings = self::get_settings();
        if(wp_verify_nonce(Helper::POST('rsc-mapping-nonce'), 'rsc-mapping')){
            $post_settings = Helper::POST('rsc');

            $settings['listing_fields_mapping'] = $post_settings['listing_fields'];

            $custom_fields = array_combine($post_settings['custom_fields']['wp'], $post_settings['custom_fields']['listing']);
            $settings['listing_custom_fields_mapping'] = $custom_fields;

            update_option(self::$option_settings_key, $settings, $post_settings);

            self::$messages->add('summary', 'Settings have been saved successfully');
        }
    }

    static function delete_queues(){
        if(!current_user_can('manage_options'))
            return;

        if(wp_verify_nonce(Helper::POST('rsc-delete-queues-nonce'), 'rsc-delete-queues')){
            $delete_selected = Helper::POST('delete_selected');
            $delete_all = Helper::POST('delete_all');
            $queue_ids = Helper::POST('queue_id');

            if($delete_selected && empty($queue_ids)){
                self::$errors->add('error', 'No selected found');
            }

            if(!self::$errors->get_error_code()){
                $total_deleted = 0;

                if($delete_all){
                    $status = Helper::GET('status', \Rex\Sync\Queue::STATUS_PENDING);
                    $search_text = Helper::GET('s');

                    do{
                        $paging = Queue::get_paging(1, 100, $status, 'desc', $search_text);
                        $rows = $paging['rows'];
                        foreach($rows as $r){
                            Queue::delete($r['id']);
                            $total_deleted ++;
                        }

                        usleep(10);

                    }while($rows);
                }

                if($delete_selected){
                    foreach($queue_ids as $rid){
                        Queue::delete($rid);
                        $total_deleted ++;
                    }
                }

                self::$messages->add('summary', 'Deleted '.$total_deleted.' rows successfully');
            }

        }
    }

    static function register_pages(){

        add_menu_page(
            __('Rex Sync', 'rex-sync'),
            __('Rex Sync', 'rex-sync'),
            'manage_options',
            self::$page_settings_slug
        );
        add_submenu_page(
            self::$page_settings_slug,
            __('Rex Sync Settings', 'rex-sync'),
            __('Settings', 'rex-sync'),
            'manage_options',
            self::$page_settings_slug,
            array(__CLASS__, 'render_settings_page')
        );
        add_submenu_page(
            self::$page_settings_slug,
            __('Mapping', 'rex-sync'),
            __('Mapping', 'rex-sync'),
            'manage_options',
            self::$page_mapping_slug,
            array(__CLASS__, 'render_mapging_page')
        );
        add_submenu_page(
            self::$page_settings_slug,
            __('Manual Sync', 'rex-sync'),
            __('Manual Sync', 'rex-sync'),
            'manage_options',
            self::$page_manual_sync_slug,
            array(__CLASS__, 'render_manual_sync_page')
        );
        add_submenu_page(
            self::$page_settings_slug,
            __('Queues', 'rex-sync'),
            __('Queues', 'rex-sync'),
            'manage_options',
            self::$page_queues_slug,
            array(__CLASS__, 'render_queues_page')
        );
        add_submenu_page(
            self::$page_settings_slug,
            __('Logs', 'rex-sync'),
            __('Logs', 'rex-sync'),
            'manage_options',
            self::$page_logs_slug,
            array(__CLASS__, 'render_logs_page')
        );

    }

    static function register_meta_boxes(){
        add_meta_box( 'meta-box-rex-listing-custom-fields',
            __( 'Listing Custom Fields', 'rex-sync' ),
            [__CLASS__, 'display_meta_box_listing_custom_fields'],
            'listing'
        );

        add_meta_box( 'meta-box-rex-agent-custom-fields',
            __( 'Agent Custom Fields', 'rex-sync' ),
            [__CLASS__, 'display_meta_box_agent_custom_fields'],
            'listing_agent'
        );
    }

    static function register_admin_scripts(){

        $version = self::get_plugin_version();

        if(Helper::GET('page') == self::$page_mapping_slug){
            wp_enqueue_style('select2', self::get_plugin_dir_url().'external/select2/css/select2.min.css');
            wp_enqueue_script('select2', self::get_plugin_dir_url().'external/select2/js/select2.full.min.js', ['jquery'], false, false);
        }

        wp_enqueue_style('rsc_style', self::get_plugin_dir_url().'css/admin.min.css', false, $version);
        wp_enqueue_script('rsc_script', self::get_plugin_dir_url().'js/admin.js', ['jquery'], $version, true);

    }

    static function render_settings_page(){
        include __DIR__.'/templates/admin.php';
    }

    static function render_mapging_page(){
        include __DIR__.'/templates/admin-mapping.php';
    }

    static function render_manual_sync_page(){
        include __DIR__.'/templates/admin-manual-sync.php';
    }

    static function render_queues_page(){
        include __DIR__.'/templates/admin-queues.php';
    }

    static function render_logs_page(){
        include __DIR__.'/templates/admin-logs.php';
    }

    static function display_meta_box_listing_custom_fields($post){
        include __DIR__.'/templates/meta-box-listing-custom-fields.php';
    }

    static function display_meta_box_agent_custom_fields($post){
        include __DIR__.'/templates/meta-box-agent-custom-fields.php';
    }

    static function get_plugin_dir_url(){
        return plugin_dir_url(__FILE__);
    }

    static function get_plugin_version(){
        $meta = get_plugin_data(__DIR__. DIRECTORY_SEPARATOR. basename(__DIR__).'.php');
        return $meta['Version'];
    }

    static function ajax_rsc_download_listings(){
        if(!current_user_can('manage_options'))
            return;

        set_time_limit(0);

        Queue::cancel_all_pendings(Queue::TYPE_MANUAL);

        $row_ids = [];
        $page = 0;
        $page_size = 200;

        do{
            $page ++ ;
            $listings = RexAPI::listings_search([
                'type' => ['current', 'sold', 'leased'],
                'page-size' => $page_size,
                'page' => $page
            ]);

            if($listings && $listings['rows']){
                foreach($listings['rows'] as $listing_id){
                    $row_ids[] = Queue::insert($listing_id, '', Queue::TYPE_MANUAL);
                }
            }

            sleep(1);

        }while($listings && $listings['rows'] && count($listings['rows']));

        self::remove_non_existing_listing_posts();

        do_action('Rex/Sync/download_listings');

        wp_send_json([
            'data' => $row_ids,
            'total' => count($row_ids)
        ]);

        exit;

    }

    static function test_insert_listing(){
        if(Helper::GET('test-queue')){
            Queue::update(Helper::GET('test-queue'), ['status' => 'pending']);
            self::insert_listing_from_queue(Helper::GET('test-queue'));
            exit;
        }

    }

    static function insert_listing_from_queue($queue_id){
        $row_queue = Queue::get($queue_id);
        if(!$row_queue || !$row_queue['listing_id'])
            return false;

        if($row_queue['status'] != Queue::STATUS_PENDING)
            return false;

        $settings = self::get_settings();
        $listing_id = $row_queue['listing_id'];
        $listing = RexAPI::get_listing($listing_id);
        if(!$listing){
            Logger::info('Cannot retrieve listing from Rex', compact($queue_id, $listing_id));
            Queue::update($queue_id, ['status' => Queue::STATUS_FAIL, 'status_message' => 'Cannot retrieve listing from Rex']);
            return false;
        }

        $listing = apply_filters('Rex/Sync/insert_listing_data', $listing, $queue_id);
        if(!$listing) {
            Logger::info('Listing has been cancelled by developer', compact($queue_id, $listing_id));
            Queue::update($queue_id, ['status' => Queue::STATUS_CANCEL, 'status_message' => 'Listing has been cancelled by developer']);
            return false;
        }

        Queue::update($queue_id, ['jsonstring' => json_encode($listing), 'listing_system_modtime' => $listing->system_modtime]);

        if($listing->system_publication_status != 'published'){
            Queue::update($queue_id, ['status' => Queue::STATUS_CANCEL, 'status_message' => 'Need to be published']);
            return false;
        }

        do_action('Rex/Sync/before_insert_listing_from_queue', $queue_id, $listing);

        $flat_listing = Helper::squash($listing, self::$custom_field_prefix);

        $address = $listing->property->adr_street_number.' '.$listing->property->adr_street_name;

        $post_title = $address;
        $post_content = '';

        if($settings['listing_fields_mapping']['title']
            && isset($flat_listing[$settings['listing_fields_mapping']['title']])
            && $flat_listing[$settings['listing_fields_mapping']['title']]
        ){
            $post_title = $flat_listing[$settings['listing_fields_mapping']['title']];
        }

        if($settings['listing_fields_mapping']['content']
            && isset($flat_listing[$settings['listing_fields_mapping']['content']])
            && $flat_listing[$settings['listing_fields_mapping']['content']]
        ){
            $post_content = $flat_listing[$settings['listing_fields_mapping']['content']];
        }

        $post_args = [
            'post_title' => $post_title,
            'post_name' => sanitize_title($post_title),
            'post_content' => $post_content,
            'post_type' => 'listing',
            'post_date' => date('Y-m-d H:i:s', $listing->system_publication_time)
        ];

        $is_new = true;
        $listing_post = self::find_post_by_listing_id($listing_id);
        if($listing_post){
            $post_args['ID'] = $listing_post->ID;
            $listing_post_id = wp_update_post($post_args);
            $is_new = false;
        }else{
            $post_args['post_status'] = 'draft';
            $listing_post_id = wp_insert_post($post_args);
        }

        if(is_wp_error($listing_post_id)){
            Logger::info('Cannot insert listing post: '.$listing_post_id->get_error_message(), compact($queue_id, $listing_id));
            return false;
        }

        update_post_meta($listing_post_id,'_rsc.id', $listing->id);

        $custom_fields = $settings['listing_custom_fields_mapping'];
        if($custom_fields){
            foreach($custom_fields as $field_key=>$field_map_key){
                if(isset($flat_listing[$field_map_key]))
                    update_post_meta($listing_post_id, $field_key, $flat_listing[$field_map_key]);
            }
        }

        $listing_state = $listing->system_listing_state;
        if($listing_state)
            wp_set_object_terms($listing_post_id, [ucfirst($listing_state)], 'listing_state', false);

        $listing_category = isset($listing->listing_category) ? $listing->listing_category->text : '';
        if($listing_category){
            wp_set_object_terms($listing_post_id, [$listing_category], 'listing_category', false);
        }

        if($listing->listing_agent_1){
            self::update_listing_agent($listing->listing_agent_1);
        }
        if($listing->listing_agent_2){
            self::update_listing_agent($listing->listing_agent_2);
        }


        if($is_new){
            wp_update_post([
                'ID' => $listing_post_id,
                'post_status' => 'publish',
            ]);
            wp_update_post([
                'ID' => $listing_post_id,
                'post_date' => date('Y-m-d H:i:s', $listing->system_publication_time)
            ]);
        }


        Queue::update($queue_id, ['status' => Queue::STATUS_DONE,'post_id'=>$listing_post_id]);

        do_action('Rex/Sync/after_insert_listing_from_queue', $listing_post_id, $queue_id, $listing);

        return $listing_post_id;
    }

    static function find_post_by_listing_id($listing_id){
        global $wpdb;

        $sql = $wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_rsc.id' and meta_value = %d", $listing_id);
        $post_id = $wpdb->get_var($sql);

        $post = false;
        if($post_id){
            $post = get_post($post_id);
        }

        $post = apply_filters('Rex/Sync/find_post_by_listing_id', $post, $listing_id);

        return $post;
    }

    private static function remove_non_existing_listing_posts(){
        global $wpdb;

        $queue_table_name = $wpdb->prefix.Queue::$table_name;

        /**
         * Move all listings non-existing to Trash
         */
        $sql = "UPDATE {$wpdb->posts} 
            SET post_status = 'trash' 
            WHERE post_type = 'listing' and post_status = 'publish'
            and ID in (
              SELECT p.ID
              FROM {$wpdb->posts} p
              INNER JOIN {$wpdb->postmeta} listing_meta on (p.post_type = 'listing' and p.ID = listing_meta.post_id and listing_meta.meta_key = '_rsc.id' and listing_meta.meta_value != '')
              WHERE p.ID not in (
                SELECT pm.post_id 
                  FROM {$wpdb->postmeta} pm
                  INNER JOIN  {$queue_table_name} qu on (pm.meta_key = '_rsc.id' and pm.meta_value = qu.listing_id)
                  WHERE qu.status = 'pending' and qu.type = 'manual'
              )
            )
            ";

        $wpdb->query($sql);

        /**
         * Move all existing Listings from Trash back to Publish
         */
        $sql = "UPDATE {$wpdb->posts} 
            SET post_status = 'publish' 
            WHERE post_type = 'listing' and post_status = 'trash'
            and ID in (
              SELECT p.ID
              FROM {$wpdb->posts} p
              INNER JOIN {$wpdb->postmeta} listing_meta on (p.post_type = 'listing' and p.ID = listing_meta.post_id and listing_meta.meta_key = '_rsc.id' and listing_meta.meta_value != '')
              WHERE p.ID in (
                SELECT pm.post_id 
                  FROM {$wpdb->postmeta} pm
                  INNER JOIN  {$queue_table_name} qu on (pm.meta_key = '_rsc.id' and pm.meta_value = qu.listing_id)
                  WHERE qu.status = 'pending' and qu.type = 'manual'
              )
            )
            ";

        $wpdb->query($sql);
    }

    static function find_agent_post_by_agent_id($agent_id){
        global $wpdb;

        $sql = "SELECT agent_meta.post_id 
        FROM {$wpdb->postmeta} agent_meta
        INNER JOIN {$wpdb->posts} p on (agent_meta.post_id = p.ID and p.post_type = 'listing_agent' and meta_key = '_rsc.agent_id' and meta_value = %d)
        WHERE p.post_status != 'trash'
        ";
        $sql = $wpdb->prepare($sql, $agent_id);
        $post_id = $wpdb->get_var($sql);

        $post = false;
        if($post_id){
            $post = get_post($post_id);
        }

        $post = apply_filters('Rex/Sync/find_agent_post_by_agent_id', $post, $agent_id);

        return $post;
    }

    static function update_listing_agent($agent_data){

        if(empty($agent_data->id))
            return false;

        $data = apply_filters('Rex/Sync/insert_listing_agent_data', $agent_data);
        if(!$data){
            Logger::info('Agent has been cancelled by developer', $agent_data);
            return false;
        }

        do_action('Rex/Sync/before_insert_listing_agent', $data);

        $is_new = true;
        $settings = self::get_settings();
        $agent_post = self::find_agent_post_by_agent_id($data->id);

        $flat_data = Helper::squash($data, self::$custom_field_prefix);

        $post_title = $data->name;
        if($settings['agent_fields_mapping']['title']
            && isset($flat_data[$settings['agent_fields_mapping']['title']])
            && $flat_data[$settings['agent_fields_mapping']['title']]
        ){
            $post_title = $flat_data[$settings['agent_fields_mapping']['title']];
        }

        if($agent_post){
            $post_args = [
                'ID' => $agent_post->ID,
                'post_title' => $post_title,
            ];
            $agent_post_id = wp_update_post($post_args);
            $is_new = false;
        }else{
            $post_args = [
                'post_title' => $post_title,
                'post_name' => sanitize_title($post_title),
                'post_type' => 'listing_agent',
                'post_status' => 'draft'
            ];

            $agent_post_id = wp_insert_post($post_args);
        }

        update_post_meta($agent_post_id, '_rsc.agent_id', $data->id);

        $agents_custom_fields_mapping = $settings['agent_custom_fields_mapping'];
        if($agents_custom_fields_mapping){
            foreach($agents_custom_fields_mapping as $field_key=>$map_key){
                update_post_meta($agent_post_id, $field_key, $flat_data[$map_key]);
            }
        }

        if($is_new){
            wp_update_post(['ID' => $agent_post_id, 'post_status' => 'publish']);
        }

        do_action('Rex/Sync/after_insert_listing_agent', $agent_post_id, $data);

        return $agent_post_id;
    }

    static function auto_import_latest_queues(){

        set_time_limit(0);

        $queues_paging = Queue::get_paging(1, 100, Queue::STATUS_PENDING);
        $queues = $queues_paging['rows'];

        foreach($queues as $row){
            $queue_id = $row['id'];
            self::insert_listing_from_queue($queue_id);
        }
    }

    static function get_webhook_url(){
        return add_query_arg('rschook', 'rex', home_url());
    }

    static function handle_webhook(){
        if(Helper::GET('rschook') != 'rex')
            return;

        $body_post = file_get_contents('php://input');
        if(empty($body_post))
            return;

        $json_object = json_decode($body_post, ARRAY_A);
        if(!$json_object || !isset($json_object['data']))
            return;

        foreach($json_object['data'] as $item){
            if($item['type'] == 'listings.updated' || $item['type'] == 'listings.created'){
                $listing_id = $item['payload']['context']['record_id'];
                if(!$listing_id)
                    continue;

                Queue::insert($listing_id, "", Queue::TYPE_AUTO);
            }
        }

        exit;
    }

    static function register_post_type_listing() {

        /**
         * Post Type: Listings.
         */

        $labels = [
            "name" => __( "Listings", "rsc" ),
            "singular_name" => __( "Listing", "rsc" ),
        ];

        $args = [
            "label" => __( "Listings", "rsc" ),
            "labels" => $labels,
            "description" => "",
            "public" => true,
            "publicly_queryable" => true,
            "show_ui" => true,
            "show_in_rest" => true,
            "rest_base" => "",
            "rest_controller_class" => "WP_REST_Posts_Controller",
            "has_archive" => false,
            "show_in_menu" => true,
            "show_in_nav_menus" => true,
            "delete_with_user" => false,
            "exclude_from_search" => false,
            "capability_type" => "post",
            "map_meta_cap" => true,
            "hierarchical" => false,
            "rewrite" => [ "slug" => "listing", "with_front" => true ],
            "query_var" => true,
            "supports" => [ "title", "editor", "thumbnail", "author" ],
            "show_in_graphql" => false,
        ];

        register_post_type( "listing", $args );
    }

    static function register_post_type_listing_agent() {

        /**
         * Post Type: Listing Agents.
         */

        $labels = [
            "name" => __( "Listing Agents", "rsc" ),
            "singular_name" => __( "Listing Agent", "rsc" ),
        ];

        $args = [
            "label" => __( "Listing Agents", "rsc" ),
            "labels" => $labels,
            "description" => "",
            "public" => true,
            "publicly_queryable" => true,
            "show_ui" => true,
            "show_in_rest" => true,
            "rest_base" => "",
            "rest_controller_class" => "WP_REST_Posts_Controller",
            "has_archive" => false,
            "show_in_menu" => true,
            "show_in_nav_menus" => true,
            "delete_with_user" => false,
            "exclude_from_search" => false,
            "capability_type" => "post",
            "map_meta_cap" => true,
            "hierarchical" => false,
            "rewrite" => [ "slug" => "our-people", "with_front" => true ],
            "query_var" => true,
            "supports" => [ "title", "editor", "thumbnail" ],
            "show_in_graphql" => false,
        ];

        register_post_type( "listing_agent", $args );
    }

    static function register_taxonomy_listing_category() {

        /**
         * Taxonomy: Listing Categories.
         */

        $labels = [
            "name" => __( "Listing Categories", "rsc" ),
            "singular_name" => __( "Listing Category", "rsc" ),
        ];


        $args = [
            "label" => __( "Listing Categories", "rsc" ),
            "labels" => $labels,
            "public" => false,
            "publicly_queryable" => true,
            "hierarchical" => true,
            "show_ui" => true,
            "show_in_menu" => true,
            "show_in_nav_menus" => true,
            "query_var" => true,
            "rewrite" => [ 'slug' => 'listing_category', 'with_front' => true, ],
            "show_admin_column" => true,
            "show_in_rest" => true,
            "show_tagcloud" => false,
            "rest_base" => "listing_category",
            "rest_controller_class" => "WP_REST_Terms_Controller",
            "show_in_quick_edit" => false,
            "show_in_graphql" => false,
        ];
        register_taxonomy( "listing_category", [ "listing" ], $args );
    }

    static function register_taxonomy_listing_state() {

        /**
         * Taxonomy: Listing States.
         */

        $labels = [
            "name" => __( "Listing States", "rsc" ),
            "singular_name" => __( "Listing State", "rsc" ),
        ];


        $args = [
            "label" => __( "Listing States", "rsc" ),
            "labels" => $labels,
            "public" => false,
            "publicly_queryable" => true,
            "hierarchical" => false,
            "show_ui" => true,
            "show_in_menu" => true,
            "show_in_nav_menus" => true,
            "query_var" => true,
            "rewrite" => [ 'slug' => 'listing_state', 'with_front' => true, ],
            "show_admin_column" => true,
            "show_in_rest" => true,
            "show_tagcloud" => false,
            "rest_base" => "listing_state",
            "rest_controller_class" => "WP_REST_Terms_Controller",
            "show_in_quick_edit" => false,
            "show_in_graphql" => false,
        ];
        register_taxonomy( "listing_state", [ "listing" ], $args );
    }


    static function get_listing_demo(){
        $file_content = file_get_contents(__DIR__.'/data/listing-demo.json');
        return json_decode($file_content);
    }

    static function get_listing_demo_fields(){
        $listing = self::get_listing_demo();
        $field_keys = Helper::squash($listing, self::$custom_field_prefix);
        $field_keys = apply_filters('Rex/Sync/get_listing_demo_fields', $field_keys);
        return $field_keys;
    }

    static function ajax_delete_log(){
        if(!current_user_can('manage_options'))
            return;

        $file_name = Helper::POST('file');
        Logger::delete_file($file_name);

        echo 1;
        exit;
    }
}