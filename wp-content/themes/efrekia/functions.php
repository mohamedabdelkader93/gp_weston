<?php
require_once get_template_directory().'/inc/class-tgm-plugin-activation.php';
require_once get_template_directory().'/inc/efrekia_activation.php';
require_once get_template_directory().'/inc/efrekia-demo-import.php';
require_once get_template_directory().'/inc/efrekia-acf-data.php';

function efrekia_setup(){
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails',array('post','sliders','teams','testimonials','portfolio','gallery'));
    // Editor Styles
    add_theme_support( 'editor-styles' );

    
    load_theme_textdomain('efrekia', get_template_directory() . '/languages');
    register_nav_menus(array(
        'primary-menu' => __('Primary Menu','efrekia')
    ));
}

add_action('after_setup_theme','efrekia_setup');

function efrekia_assets(){
    //wp_enqueue_script( 'script-name', get_template_directory_uri() . '/js/example.js', array(), '1.0.0', true );
    //Load css styles
    wp_enqueue_style( 'google-poopins', 'https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700',array(), '1.0.0', 'all');
    wp_enqueue_style( 'bootstrap', get_template_directory_uri().'/assets/css/bootstrap.min.css', array(), '1.0.0', 'all' );
    wp_enqueue_style( 'font-awesome', get_template_directory_uri().'/assets/css/font-awesome.min.css', array(), '1.0.0', 'all' );
    wp_enqueue_style( 'Magnific-Popup', get_template_directory_uri().'/assets/css/magnific-popup.css', array(), '1.0.0', 'all' );
    wp_enqueue_style( 'Owl-Carousel', get_template_directory_uri().'/assets/css/owl.carousel.css', array(), '1.0.0', 'all' );
    wp_enqueue_style( 'main', get_template_directory_uri().'/assets/css/style.css', array(), '1.0.0', 'all' );
    wp_enqueue_style( 'Responsive-Css', get_template_directory_uri().'/assets/css/responsive.css', array(), '1.0.0', 'all' );


    wp_enqueue_style( 'style-theme', get_stylesheet_uri() );
//Google maps

// wp_enqueue_script( 'google-map', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyBldfZObdPTTBwlROb6RevdPmYwx0cFjQg', array(), '3', true );
// 			wp_enqueue_script( 'google', get_template_directory_uri() . 'assets/js/google.js', array('google-map', 'jquery'), '0.1', true );
    //Load Javascripts scripts
    wp_enqueue_script( 'popper', get_template_directory_uri() . '/assets/js/popper.min.js', array('jquery'), '1.0.0', true );
    wp_enqueue_script( 'bootstrap', get_template_directory_uri() . '/assets/js/bootstrap.min.js', array('jquery'), '1.0.0', true );
    wp_enqueue_script( 'carousel', get_template_directory_uri() . '/assets/js/owl.carousel.min.js', array('jquery'), '1.0.0', true );
    wp_enqueue_script( 'popup', get_template_directory_uri() . '/assets/js/jquery.magnific-popup.min.js', array('jquery'), '1.0.0', true );
    wp_enqueue_script( 'isotope', get_template_directory_uri() . '/assets/js/isotope.min.js', array('jquery'), '1.0.0', true );
    wp_enqueue_script( 'imageloaded', get_template_directory_uri() . '/assets/js/imageloaded.min.js', array('jquery'), '1.0.0', true );
    wp_enqueue_script( 'counterup', get_template_directory_uri() . '/assets/js/jquery.counterup.min.js', array('jquery'), '1.0.0', true );
    wp_enqueue_script( 'waypoint', get_template_directory_uri() . '/assets/js/waypoint.min.js', array('jquery'), '1.0.0', true );
    wp_enqueue_script( 'main', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), '1.0.0', true );


}
add_action('wp_enqueue_scripts','efrekia_assets');



/**
 * Add a sidebar.
 */
function efrekia_widgets_init() {
    register_sidebar( array(
        'name'          => __( 'Main Sidebar', 'efrekia' ),
        'id'            => 'sidebar-1',
        'description'   => __( 'Widgets in this area will be shown on all posts and pages.', 'efrekia' ),
        'before_widget' => '<div class="single-sidebar">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>'
    ) );
    //register footer widgets
    register_sidebar( array(
        'name'          => __( 'Footer Sidebar', 'efrekia' ),
        'id'            => 'footer-1',
        'description'   => __( 'Widgets in this area will be shown on all posts and pages.', 'efrekia' ),
        'before_widget' => ' <div class="single-footer footer-logo">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3>',
        'after_title'   => '</h3>'
    ) );
    register_sidebar( array(
        'name'          => __( 'Footer Sidebar', 'efrekia' ),
        'id'            => 'footer-2',
        'description'   => __( 'Widgets in this area will be shown on all posts and pages.', 'efrekia' ),
        'before_widget' => ' <div class="single-footer">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>'
    ) );    register_sidebar( array(
        'name'          => __( 'Footer Sidebar', 'efrekia' ),
        'id'            => 'footer-3',
        'description'   => __( 'Widgets in this area will be shown on all posts and pages.', 'efrekia' ),
        'before_widget' => ' <div class="single-footer">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>'
    ) );
}
add_action( 'widgets_init','efrekia_widgets_init');

function acf_css(){
    ?>
    <style>
        .header-top{
            background-color:<?php the_field('header_background', 'options'); ?>
        }
    </style>
    <?php
}

add_action('wp_head','acf_css');


if( function_exists('acf_add_options_page') ) {
	
	acf_add_options_page(array(
		'page_title' 	=> 'efrekia General Settings','efrekia',
		'menu_title'	=> 'Efrekia Settings','efrekia',
		'menu_slug' 	=> 'efrekia-general-settings',
		'capability'	=> 'edit_posts',
		'redirect'		=> false
	));
	
	acf_add_options_sub_page(array(
		'page_title' 	=> 'Efrekia Header Settings','efrekia',
		'menu_title'	=> 'Header','efrekia',
		'parent_slug'	=> 'efrekia-general-settings',
	));
	
	acf_add_options_sub_page(array(
		'page_title' 	=> 'Efrekia About Settings','efrekia',
		'menu_title'	=> 'About','efrekia',
		'parent_slug'	=> 'efrekia-general-settings',
	));
    acf_add_options_sub_page(array(
		'page_title' 	=> 'Efrekia Faq & Skills Settings','efrekia',
		'menu_title'	=> 'Faq & Skills','efrekia',
		'parent_slug'	=> 'efrekia-general-settings',
	));
    acf_add_options_sub_page(array(
		'page_title' 	=> 'Efrekia CTA Settings','efrekia',
		'menu_title'	=> 'CTA','efrekia',
		'parent_slug'	=> 'efrekia-general-settings',
	));
    acf_add_options_sub_page(array(
		'page_title' 	=> 'Efrekia Footer Settings','efrekia',
		'menu_title'	=> 'Footer','efrekia',
		'parent_slug'	=> 'efrekia-general-settings',
	));
    acf_add_options_sub_page(array(
		'page_title' 	=> 'Efrekia Contact Settings','efrekia',
		'menu_title'	=> 'Contact','efrekia',
		'parent_slug'	=> 'efrekia-general-settings',
	));
}

function move_comment_field( $fields ) {
    $comment_field = $fields['comment'];
    unset( $fields['comment'] );
    $fields['comment'] = $comment_field;
    return $fields;
}
  
add_filter( 'comment_form_fields', 'move_comment_field' );     



/**
 * Change default fields, add placeholder and change type attributes.
 *
 * @param  array $fields
 * @return array
 */
function efrekia_comment_placeholders( $fields )
{
    $fields['author'] = str_replace(
        '<input',
        '<input placeholder="'
        /* Replace 'theme_text_domain' with your theme’s text domain.
         * I use _x() here to make your translators life easier. :)
         * See http://codex.wordpress.org/Function_Reference/_x
         */
            . _x(
                'First and last name or a nick name',
                'comment form placeholder',
                'theme_text_domain'
                )
            . '"',
        $fields['author']
    );
    $fields['email'] = str_replace(
        '<input id="email" name="email" type="text"',
        /* We use a proper type attribute to make use of the browser’s
         * validation, and to get the matching keyboard on smartphones.
         */
        '<input type="email" placeholder="contact@example.com"  id="email" name="email"',
        $fields['email']
    );
    $fields['url'] = str_replace(
        '<input id="url" name="url" type="text"',
        // Again: a better 'type' attribute value.
        '<input placeholder="http://example.com" id="url" name="url" type="url"',
        $fields['url']
    );

    return $fields;
}
add_filter( 'comment_form_default_fields', 'efrekia_comment_placeholders' );

function efrekia_modify_comment_form_text_area($arg) {
    $arg['comment_field'] = '<p class="comment-form-comment"><label for="comment">' . _x( 'Your Feedback Is Appreciated', 'noun' ) . '</label><textarea id="comment" name="comment" cols="45" rows="1" aria-required="true"></textarea></p>';
    return $arg;
}

add_filter('comment_form_defaults', 'efrekia_modify_comment_form_text_area');

function efrekia_customize_comment_form_text_area($arg) {
    $arg['comment_field'] = '<p class="comment-form-comment"><label for="comment">' . _x( 'Your Feedback Is Appreciated', 'noun' ) . '</label><textarea id="comment" name="comment" placeholder="Please Use Pastebin or Github Gists If You Want To Leave PHP Code In Your Comment. Thanks!"cols="45" rows="1" aria-required="true"></textarea></p>';
    return $arg;
}

add_filter('comment_form_defaults', 'efrekia_customize_comment_form_text_area');


 
