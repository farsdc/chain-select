<?php
/*
Plugin Name: Chain Select
Description: A plugin for chained select fields for product search.
Version: 1.0
Author: mahdi najarian 09362005446
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

require_once plugin_dir_path(__FILE__) . 'includes/class-chain-select.php';

function chain_select_enqueue_scripts()
{
    wp_enqueue_style('chain-select-style', plugin_dir_url(__FILE__) . 'assets/css/custom-style.css');
    wp_enqueue_script('chain-select-script', plugin_dir_url(__FILE__) . 'assets/js/custom-select.js', array('jquery'), null, true);

    wp_localize_script('chain-select-script', 'chain_select_ajax_obj', array(
        'ajax_url' => admin_url('admin-ajax.php')
    )
    );
}
add_action('wp_enqueue_scripts', 'chain_select_enqueue_scripts');

function register_chain_select_elementor_widget($widgets_manager)
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-elementor-chain-select-widget.php';
    $widgets_manager->register(new \Elementor_Chain_Select_Widget());
}
add_action('elementor/widgets/register', 'register_chain_select_elementor_widget');
