<?php
/*
Plugin Name: Rex Sync Listings
Plugin URI:
Description: Providing tool to sync all listings, listing agents from Rex Software to WordPress
Version: 1.0.2
Author: Phuc Pham
Author URI: mailto:svincoll4@gmail.com
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

require __DIR__.'/loader.php';


register_activation_hook(__FILE__, 'Rex_Sync_activation');
function Rex_Sync_activation() {
    require 'db.php';

}

register_deactivation_hook(__FILE__, 'Rex_Sync_deactivation');
function Rex_Sync_deactivation() {
    wp_clear_scheduled_hook('rsc_5_minutes_event');
    wp_clear_scheduled_hook('rsc_3_minutes_event');
}

add_filter( 'cron_schedules', 'Rex_Sync_add_intervals' );
function Rex_Sync_add_intervals( $schedules ) {
    // add a 'weekly' schedule to the existing set
    $schedules['5_minutes'] = array(
        'interval' => 5 * MINUTE_IN_SECONDS,
        'display' => __('Once Every 5 Minutes', 'rex-sync')
    );
    $schedules['3_minutes'] = array(
        'interval' => 3 * MINUTE_IN_SECONDS,
        'display' => __('Once Every 3 Minutes', 'rex-sync')
    );
    return $schedules;
}

if(class_exists('\Rex\Sync\Loader'))
    \Rex\Sync\Loader::load();