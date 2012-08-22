<?php 


@define('JST_PATH', trailingslashit(get_stylesheet_directory()));
@define('JST_URL', trailingslashit(get_stylesheet_directory_uri()));
@define('JST_VER', '1.0');


// WP will include this file by default but it needs to be loaded prior to the parent themes versions
include_once JST_PATH.'plugins/load.php'; 

function annojst_widgets_init() {
	include_once(JST_PATH . 'functions/JST_Widget_Recently_Categories.php');
	register_widget('JST_Widget_Recently_Categories');
}
add_action('widgets_init', 'annojst_widgets_init');

function annojst_assets() {
	cfct_template_file('assets', 'load');
}
add_action('wp_enqueue_scripts', 'annojst_assets');