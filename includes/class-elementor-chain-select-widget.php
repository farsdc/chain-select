<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Elementor_Chain_Select_Widget extends \Elementor\Widget_Base
{

    public function get_name()
    {
        return 'chain-select';
    }

    public function get_title()
    {
        return __('Chain Select', 'chain-select');
    }

    public function get_icon()
    {
        return 'eicon-select';
    }

    public function get_categories()
    {
        return ['general'];
    }

    protected function render()
    {
        echo do_shortcode('[chain_select_form]');
    }
}
