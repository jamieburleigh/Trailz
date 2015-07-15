<?php
/**
 * Plugin Name: Lollipop Trail
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: A customised breadcrumbs plugin.
 * Version: .1
 * Author: Jamie Burleigh
 */

add_action( 'wp_enqueue_scripts','lollipop_css');
function lollipop_css() {
wp_register_style('lollipop_css', plugins_url('/lollipops.css',__FILE__ ));
wp_enqueue_style('lollipop_css');
}

function plugin_admin_init() {
register_setting( 'plugin_options', 'plugin_options', 'plugin_options_validate');
register_setting( 'plugin_options2', 'plugin_options2', 't5_sae_validate_option');
add_settings_section('plugin_main', 'Main Settings', 'plugin_section_text', 'plugin');
add_settings_field('plugin_text_string', 'The Text to use as \'Home\', leave blank for no home.', 'plugin_setting_string', 'plugin', 'plugin_main');
add_settings_field('plugin_text_string2', 'The Text to use as \'Seperator\'', 'plugin_setting_string2', 'plugin', 'plugin_main');
} 
add_action( 'admin_init', 'plugin_admin_init' );

function plugin_section_text() {
echo '<p>Main description of this section here.</p>';
}

function plugin_setting_string() {
$options = get_option('plugin_options');
echo "<input id='plugin_text_string' name='plugin_options[text_string]' size='40' type='text' value='{$options['text_string']}' />";
}

function plugin_setting_string2() {
$options = get_option('plugin_options');
echo "<input id='plugin_text_string2' name='plugin_options[text_string2]' size='40' type='text' value='{$options['text_string2']}' />";
}

// validate our options
function plugin_options_validate($input) {
$new_input = array();
        if( isset( $input['text_string'] ) )
            $new_input['text_string'] = sanitize_text_field( $input['text_string'] );

        if( isset( $input['text_string2'] ) )
            $new_input['text_string2'] = sanitize_text_field( $input['text_string2'] );

        return $new_input;
}

function my_plugin_menu() {
	add_options_page( 'Lollyipop Trail', 'Lollipops', 'manage_options', 'lollipop-trail', 'lollipop_options' );
}

function lollipop_options() {

echo '<div>
<h2>Lollipop Trail</h2>
A couple of options to customise the lollipop trail.
<form action="options.php" method="post">';
settings_fields('plugin_options');
do_settings_sections('plugin');
echo '<input name="Submit" type="submit" value="<?php esc_attr_e(\'Save Changes\'); ?>" />
</form></div>';

}

add_action( 'admin_menu', 'my_plugin_menu' );

function get_trailz() {

	global $post;

	$options = get_option('plugin_options');
	$home =  $options['text_string'];
    $seperator =  $options['text_string2'];    
    // Set the page title to current page, if exists.
	$page_title = get_the_title($post->ID);
    // Set the current parent to current parent page, if exists.
    $parent_id = $post->post_parent;  
    // Set the current post type, if exists
    $posttype = get_post_type($post->ID);
    // Set the current post type object, if exists.
    $post_type_obj = get_post_type_object($posttype);
    // Set the current post type title, if ecists.
    $post_type_title = $post_type_obj->labels->name;
    // Set the current post type permalink, if exists
    $post_type_link = get_post_type_archive_link($posttype); 
    // Set the current category title, if exists.
    $category = single_cat_title("", false);
    // Set the current taxonemy title, if exists.
    $taxonomy = single_tag_title("", false);    
    
	$trail = '
    <ul class="lollipops">
';
    
    // If current page is home, simply outout Home.
    if (!is_home()  && $home) {
        $trail .= '<li class="crumb" itemscope="itemscope" itemtype="http://data-vocabulary.org/Breadcrumb"><a href="/" itemprop="url"><span itemprop="title">'.$home.'</span></a></li><span class="seperator">'.$seperator.'</span>';   
    }
    
    // Conditionals for pages with no parents
    if (is_page()  && !$parent_id) {
        $trail .= '<li class="crumb active-crumb" itemscope="itemscope" itemtype="http://data-vocabulary.org/Breadcrumb"><span itemprop="title">' . $page_title . '</span></li>';
    } 
    // Conditionals for pages with parents
    elseif ( is_page() && $parent_id ) { 
        $breadcrumbs = array();
        $parent_id = $post->post_parent;
        while ($parent_id) {
            $page = get_page($parent_id);
            $breadcrumbs[] = '<li class="crumb" itemscope="itemscope" itemtype="http://data-vocabulary.org/Breadcrumb"><a href="' . get_permalink($page->ID) . '" itemprop="url"><span itemprop="title">' . get_the_title($page->ID) . '</span></a></li><span class="seperator">'.$seperator.'</span>';
            $parent_id = $page->post_parent;
        }
        $breadcrumbs = array_reverse($breadcrumbs);
        foreach($breadcrumbs as $crumb) $trail .= $crumb;
        $trail .= '<li class="crumb active-crumb" itemscope="itemscope" itemtype="http://data-vocabulary.org/Breadcrumb"><a href="' . get_permalink($page->ID) . '" itemprop="url"><span itemprop="title">' . $page_title . '</span></a></li>';
    }    
    //Conditionals for Archives
    elseif (is_archive()  && !is_category() && !is_tag()) {
        $trail .= '<li class="crumb active-crumb" itemscope="itemscope" itemtype="http://data-vocabulary.org/Breadcrumb"><span itemprop="title">' . $post_type_title . '</span></li>';
    } 
    // Conditionals for Cateory Archives
    elseif (is_category()) {
        $trail .= '<li class="crumb active-crumb" itemscope="itemscope" itemtype="http://data-vocabulary.org/Breadcrumb"><span itemprop="title">' . $category . '</span></li>';
    } 
    elseif (is_tag()) {
        $trail .= '<li class="crumb active-crumb" itemscope="itemscope" itemtype="http://data-vocabulary.org/Breadcrumb"><span itemprop="title">' . $taxonomy . '</span></li>';
    }     
    // Conditionals for Regular Posts
    elseif (is_single()  && ($posttype=='post')) {
        $trail .= '<li class="crumb active-crumb" itemscope="itemscope" itemtype="http://data-vocabulary.org/Breadcrumb"><span itemprop="title">' . $page_title . '</span></li>';
    } 
    // Conditionals for Posts of Custom Post Type
    elseif (is_single()  && !($posttype=='post')) {
        $trail .= '<li class="crumb" itemscope="itemscope" itemtype="http://data-vocabulary.org/Breadcrumb"><a href="' . $post_type_link . '" itemprop="url"><span itemprop="title">' . $post_type_title . '</span></a></li><span class="seperator">'.$seperator.'</span>';
        $trail .= '<li class="crumb active-crumb" itemscope="itemscope" itemtype="http://data-vocabulary.org/Breadcrumb"><span itemprop="title">' . $page_title . '</span></li>';
    }     
	$trail .= '
</ul>
';

	return $trail;	

}

?>
