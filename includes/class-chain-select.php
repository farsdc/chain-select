<?php

if (!class_exists('ChainSelect')) {
    class ChainSelect
    {
        public function __construct()
        {
            add_action('wp_ajax_chain_select_get_attributes', array($this, 'get_attributes'));
            add_action('wp_ajax_nopriv_chain_select_get_attributes', array($this, 'get_attributes'));
            add_action('wp_ajax_chain_select_get_vendors', array($this, 'get_vendors'));
            add_action('wp_ajax_nopriv_chain_select_get_vendors', array($this, 'get_vendors'));
            add_action('wp_ajax_chain_select_get_products', array($this, 'get_products'));
            add_action('wp_ajax_nopriv_chain_select_get_products', array($this, 'get_products'));
            add_action('wp_ajax_get_cities', array($this, 'get_cities'));
            add_action('wp_ajax_nopriv_get_cities', array($this, 'get_cities'));
            add_shortcode('chain_select_form', array($this, 'render_form'));
        }

        public function get_attributes()
        {
            if (isset($_POST['category_id'])) {
                $category_id = intval($_POST['category_id']);
                $custom_fields = get_term_meta($category_id, 'category_custom_fields', true);

                if (!empty($custom_fields)) {
                    $fields = explode(PHP_EOL, $custom_fields);
                    foreach ($fields as $field) {
                        list($field_name, $field_type, $field_values) = explode('|', $field);
                        $values = explode(',', $field_values);
                        switch (trim($field_type)) {
                            case 'radio':
                            case 'checkbox':
                            case 'select':
                                echo '<div class="form-group"><label>' . esc_html($field_name) . '</label>';
                                echo '<select name="attributes[' . esc_attr($field_name) . ']">';
                                foreach ($values as $value) {
                                    echo '<option value="' . esc_attr($value) . '">' . esc_html($value) . '</option>';
                                }
                                echo '</select></div>';
                                break;
                            default:
                                echo '<div class="form-group"><label>' . esc_html($field_name) . '</label>';
                                echo '<input type="text" name="attributes[' . esc_attr($field_name) . ']" /></div>';
                                break;
                        }
                    }
                }
            }
            wp_die();
        }

        public function get_cities()
        {
            if (!isset($_POST['province_id'])) {
                wp_send_json_error(array('message' => 'Invalid request'));
            }

            global $wpdb;
            $province_id = intval($_POST['province_id']);
            $cities = $wpdb->get_results($wpdb->prepare("SELECT id, title FROM province_cities WHERE parent = %d ORDER BY title ASC", $province_id));
            wp_send_json_success(array('cities' => $cities));
        }

        public function get_vendors()
        {
            if (isset($_POST['attributes'])) {
                $attributes = $_POST['attributes'];
                $meta_query = array('relation' => 'AND');

                foreach ($attributes as $key => $value) {
                    $meta_query[] = array(
                        'key' => sanitize_title($key),
                        'value' => sanitize_text_field($value),
                        'compare' => 'LIKE',
                    );
                }

                if (isset($_POST['province_id']) && !empty($_POST['province_id'])) {
                    $meta_query[] = array(
                        'key' => 'vendor_province',
                        'value' => intval($_POST['province_id']),
                        'compare' => '='
                    );
                }

                if (isset($_POST['city_id']) && !empty($_POST['city_id'])) {
                    $meta_query[] = array(
                        'key' => 'vendor_city',
                        'value' => intval($_POST['city_id']),
                        'compare' => '='
                    );
                }

                $vendors = get_users(
                    array(
                        'role' => 'vendor',
                        'meta_query' => $meta_query,
                    )
                );

                if (!empty($vendors)) {
                    echo '<div class="form-group"><label>' . __('Select Vendor', 'chain-select') . '</label>';
                    echo '<select id="chain-select-vendor-select">';
                    foreach ($vendors as $vendor) {
                        echo '<option value="' . esc_attr($vendor->ID) . '">' . esc_html($vendor->display_name) . '</option>';
                    }
                    echo '</select></div>';
                } else {
                    echo '<p>' . __('با این مشخصات هیچ فروشنده ای یافت نشد', 'chain-select') . '</p>';
                }
            }
            wp_die();
        }

        public function get_products()
        {
            if (isset($_POST['category_id'])) {
                $category_id = intval($_POST['category_id']);
                $vendor_id = isset($_POST['vendor_id']) ? intval($_POST['vendor_id']) : '';
                $attributes = isset($_POST['attributes']) ? $_POST['attributes'] : array();

                $args = array(
                    'post_type' => 'product',
                    'posts_per_page' => -1,
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'product_cat',
                            'field' => 'term_id',
                            'terms' => $category_id,
                        ),
                    ),
                );

                $meta_query = array('relation' => 'AND');

                if ($vendor_id) {
                    $meta_query[] = array(
                        'key' => '_vendor_id',
                        'value' => $vendor_id,
                        'compare' => '=',
                    );
                }

                if (isset($_POST['province_id']) && !empty($_POST['province_id'])) {
                    $meta_query[] = array(
                        'key' => 'vendor_province',
                        'value' => intval($_POST['province_id']),
                        'compare' => '='
                    );
                }

                if (isset($_POST['city_id']) && !empty($_POST['city_id'])) {
                    $meta_query[] = array(
                        'key' => 'vendor_city',
                        'value' => intval($_POST['city_id']),
                        'compare' => '='
                    );
                }

                if (!empty($attributes) && is_array($attributes)) {
                    foreach ($attributes as $key => $value) {
                        if (!empty($value)) {
                            $meta_query[] = array(
                                'key' => sanitize_title($key),
                                'value' => $value,
                                'compare' => 'LIKE',
                            );
                        }
                    }
                }

                if (!empty($meta_query)) {
                    $args['meta_query'] = $meta_query;
                }

                $products = new WP_Query($args);

                if ($products->have_posts()) {
                    ob_start();
                    include plugin_dir_path(__FILE__) . '../templates/product-list.php';
                    $output = ob_get_clean();
                    wp_send_json_success($output);
                } else {
                    wp_send_json_error(array('message' => __('No products found.', 'chain-select')));
                }
            } else {
                wp_send_json_error(array('message' => __('Invalid request.', 'chain-select')));
            }
            wp_die();
        }

        public function render_form()
        {
            ob_start(); ?>

            <div id="chain-select-container">
                <div class="form-group">
                    <label><?php _e('انتخاب دسته بندی', 'chain-select'); ?></label>
                    <select id="chain-select-category">
                        <option value=""><?php _e('انتخاب دسته بندی محصول', 'chain-select'); ?></option>
                        <?php
                        $categories = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => false));
                        foreach ($categories as $category) {
                            echo '<option value="' . esc_attr($category->term_id) . '">' . esc_html($category->name) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><?php _e('انتخاب استان', 'chain-select'); ?></label>
                    <select id="chain-select-province">
                        <option value=""><?php _e('انتخاب استان', 'chain-select'); ?></option>
                        <?php
                        global $wpdb;
                        $provinces = $wpdb->get_results("SELECT id, title FROM province_cities WHERE parent = 0 ORDER BY title ASC");
                        foreach ($provinces as $province) {
                            echo '<option value="' . esc_attr($province->id) . '">' . esc_html($province->title) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><?php _e('انتخاب شهر', 'chain-select'); ?></label>
                    <select id="chain-select-city">
                        <option value=""><?php _e('انتخاب شهر', 'chain-select'); ?></option>
                    </select>
                </div>
                <div id="chain-select-attributes" class="form-group"></div>
                <div id="chain-select-vendors" class="form-group"></div>
            </div>
            <div id="chain-select-container2">
                <div class="form-group">
                    <button id="chain-select-show-products"><?php _e('نمایش محصولات', 'chain-select'); ?></button>
                </div>
                <div id="chain-select-product-list"></div>
            </div>

            <?php
            return ob_get_clean();
        }
    }
}

new ChainSelect();
