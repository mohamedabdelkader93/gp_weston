<?php
/**
 * Plugin Name: efrekia-essential-plugin
 * Plugin URI: https://efrekia.com
 * Description: CPT of efrekia theme
 * Version: 1.0
 * Author: efrekiadev team
 * Author URI: https://efrekia.com
 */
//Custom Posts Types 

function efrekia_custom_posts(){
    //Slider custom posts
    register_post_type('sliders',array(
        'labels'=>array( 'name' =>__('Sliders','efrekia'),
                         'singular_name' => __('Slider','efrekia')
        ),
    'public' => true,
    'show_ui'=> true,
    'supports'=>array('title','editor','thumbnail','custom-fields'),
    'menu_icon' => 'dashicons-slides',
    'show_in_rest' => true
            
));
   //Service custom posts
   register_post_type('services',array(
    'labels'=>array( 'name' =>__('Services','efrekia'),
                     'singular_name' => __('Service','efrekia')
    ),
'public' => true,
'show_ui'=> true,
'supports'=>array('title','editor','custom-fields'),
'menu_icon' => 'dashicons-admin-tools',
'show_in_rest' => true
        
));
  //Counter custom posts
  register_post_type('counters',array(
    'labels'=>array( 'name' =>__('Counters','efrekia'),
                     'singular_name' => __('Counter','efrekia')
    ),
'public' => true,
'show_ui'=> true,
'supports'=>array('title','custom-fields'),
'menu_icon' => 'dashicons-arrow-down-alt'

        
));
  //Team custom posts
  register_post_type('teams',array(
    'labels'=>array( 'name' =>__('Teams','efrekia'),
                     'singular_name' => __('Team','efrekia')
    ),
'public' => true,
'show_ui'=> true,
'supports'=>array('title','thumbnail','custom-fields'),
'menu_icon' => 'dashicons-admin-users'

        
));
  //Testimonials custom posts
  register_post_type('testimonials',array(
    'labels'=>array( 'name' =>__('Testimonials','efrekia'),
                     'singular_name' => __('Testimonial','efrekia')
    ),
'public' => true,
'show_ui'=> true,
'supports'=>array('title','thumbnail','custom-fields'),
'menu_icon' => 'dashicons-admin-comments'

        
));
// Portfolio custom posts
register_post_type('portfolio',array(
    'labels'=>array( 'name' =>__('Portfolios','efrekia'),
                     'singular_name' => __('Portfolio','efrekia')
    ),
'public' => true,
'capability_type' => 'post',  
'show_ui'=> true,
'hierarchical' => false,  
'rewrite' => true,
'supports'=>array('title','thumbnail','editor','custom-fields'),
'menu_icon' => 'dashicons-admin-customizer',
 'taxonomies' => array('portfolio-category')
       
));

// Gallery custom posts
register_post_type('gallery',array(
    'labels'=>array( 'name' =>__('Gallerys','efrekia'),
                     'singular_name' => __('Gallery','efrekia')
    ),
'public' => true,
'capability_type' => 'post',  
'show_ui'=> true,
'hierarchical' => false,  
'rewrite' => true,
'supports'=>array('title','thumbnail','custom-fields'),
'menu_icon' => 'dashicons-admin-media'
 
       
));


register_taxonomy('portfolio-category','portfolio',array(
        'labels' => array(
            'name' => __('Categories','efrekia'),
            'singular_name' => __('Category','efrekia')    
   ),
       'slug'                       => 'portfolio-category',
          'with_front'                 => false,
       'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
       'show_in_nav_menus'          => true,
       'show_tagcloud'              => false
));
}
add_action('init','efrekia_custom_posts');