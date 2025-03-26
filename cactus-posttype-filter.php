<?php
/**
 * Plugin Name: Cactus Post Type Filter
 * Description: A shortcode [posttype_filter] that accepts 'type', 'tag', and 'category' parameters.
 * Version: 1.0
 * Author: CACTUS Design Inc. (Developed by Les Lakewood)
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Enqueue scripts and styles
function tvf_enqueue_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('cactus-filter', plugin_dir_url(__FILE__) . 'filter.js', array('jquery'), '1.0', true);
    wp_enqueue_style('cactus-filter-style', plugin_dir_url(__FILE__) . 'style.css');
}
add_action('wp_enqueue_scripts', 'tvf_enqueue_scripts');

// Get categories dynamically
function get_post_type_categories($atts) {
    
    $categories = get_categories(array(
        'taxonomy'   => $atts['category'],
        'hide_empty' => false,
        'object_type' => array($atts['type']),
    ));

    $category_array = array();
    foreach ($categories as $category) {
        $category_array[$category->term_id] = [$category->name, $category->slug];
    }

    return $category_array;
}

// Get tags dynamically
function get_post_type_tags($atts) {
    $tags = get_terms(array(
        'taxonomy'   => $atts['tag'],
        'hide_empty' => false,
        'object_type' => array($atts['type']),
        // 'sort' => 'ASC',
        // 'orderby' => 'term_id'
    ));
    $tag_array = array();
    foreach ($tags as $tag) {
        $tag_array[$tag->term_id] = [$tag->name, $tag->slug];

    }

    return $tag_array;
}

// Code output by shortcode function
function posttype_filter_shortcode($atts) {
    
    $atts = shortcode_atts(array(
        'type'     => '',
        'category' => '',
        'tag'      => ''
    ), $atts, 'filtered_posts');

    // Query Arguments
    $args = array(
        'post_type'      => sanitize_text_field($atts['type']),
        'posts_per_page' => -1
    );

    $query = new WP_Query($args);
    $categories = get_post_type_categories($atts);
    $tags = get_post_type_tags($atts);

    ob_start();
    ?>
    <form id="cactusFilter">
        <select id="tagFilter">
            <optgroup>
                <option value="">I am a...</option>
                <?php foreach ($tags as $tag): ?>
                    <option value="<?php echo esc_attr($tag[1]); ?>">
                        <?php echo esc_html($tag[0]); ?>
                    </option>
                <?php endforeach; ?>
            </optgroup>
        </select>
        <select id="categoryFilter">
            <optgroup>
                <option value="">I am looking for...</option>
                <?php foreach ($categories as $category) : ?>
                    <option value="<?php echo esc_attr($category[1]); ?>">
                        <?php echo esc_html($category[0]); ?>
                    </option>
                <?php endforeach; ?>
            </optgroup>
        </select>
    </form>

    <div id="cactusFilterGrid" class="cactus-filter-grid">
        <?php while ($query->have_posts()) : $query->the_post();
                global $post;
                
                $post_categories = get_the_terms( $post->ID, $atts['category'] );
                if ( ! empty( $post_categories ) && ! is_wp_error( $post_categories ) ) {
                    $categories = wp_list_pluck( $post_categories, 'slug' );
                    $cat_name = $post_categories['name'];
                }
        
                // Get a list of tags and extract their names
                $post_tags = get_the_terms( $post->ID, $atts['tag'] );
                if ( ! empty( $post_tags ) && ! is_wp_error( $post_tags ) ) {
                    $tags = wp_list_pluck( $post_tags, 'slug' );
                }

                // Set display variables based on category / type

                $cat = "";
                $icon = '<svg role="presentation" version="1.1" xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 32 32"><path style="fill:white" d="M16.168 23.167c-3.953 0-7.175-4.293-7.476-8.091-0.888-0.251-1.691-0.985-1.691-1.909v-2.667c0-0.833 0.807-1.435 1.333-1.739v-2.261c0-2.528 2.021-4.592 4.528-4.664 0.533-0.215 2.647-1.003 4.805-1.003 3.439 0 5.333 2.013 5.333 5.667v2.059c1.333 0.26 1.333 1.331 1.333 1.941v2.667c0 0.928-0.421 1.62-1.101 1.883-0.256 3.94-3.193 8.117-7.065 8.117zM17.668 2.167c-2.163 0-4.383 0.936-4.407 0.947-0.084 0.035-0.172 0.053-0.26 0.053-1.839 0-3.333 1.495-3.333 3.333v2.667c0 0.261-0.155 0.5-0.393 0.608-0.473 0.215-0.913 0.583-0.94 0.731v2.661c0 0.293 0.561 0.667 1 0.667 0.367 0 0.667 0.299 0.667 0.667 0 3.301 2.845 7.333 6.167 7.333 3.24 0 5.751-3.943 5.751-7.333 0-0.368 0.301-0.667 0.667-0.667 0.276 0 0.417-0.224 0.417-0.667v-2.667c0-0.667 0-0.667-0.667-0.667-0.368 0-0.667-0.299-0.667-0.667v-2.667c-0.001-2.916-1.308-4.333-4.001-4.333zM24.335 31.167c-0.171 0-0.341-0.065-0.472-0.195l-2.667-2.667c-0.26-0.257-0.26-0.683 0-0.943 0.257-0.257 0.683-0.257 0.943 0l2.195 2.195 4.861-4.861c0.26-0.257 0.685-0.257 0.943 0 0.26 0.26 0.26 0.685 0 0.943l-5.333 5.333c-0.128 0.129-0.299 0.195-0.469 0.195zM2.335 31.167c-0.367 0-0.667-0.299-0.667-0.667 0-3.861 3.084-6.667 7.333-6.667h1.643l1.776-2.648c0.201-0.305 0.617-0.388 0.921-0.183 0.305 0.204 0.388 0.619 0.185 0.924l-1.972 2.944c-0.124 0.185-0.331 0.296-0.553 0.296h-2c-3.531 0-6 2.193-6 5.333 0 0.368-0.3 0.667-0.667 0.667z"></path></svg>';
                $overlay = "overlay_01416b";
                $img = 'https://jhslearninghub.ca/wp-content/uploads/2021/02/linkedin-sales-solutions-W3Jl3jREpDY-unsplash-scaled.jpg';


                switch(implode(' ', $categories)) {
                    case 'introduction':
                        $cat = "Introduction";
                        $icon = '<svg role="presentation" version="1.1" xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 32 32"><path style="fill:white" d="M16.168 23.167c-3.953 0-7.175-4.293-7.476-8.091-0.888-0.251-1.691-0.985-1.691-1.909v-2.667c0-0.833 0.807-1.435 1.333-1.739v-2.261c0-2.528 2.021-4.592 4.528-4.664 0.533-0.215 2.647-1.003 4.805-1.003 3.439 0 5.333 2.013 5.333 5.667v2.059c1.333 0.26 1.333 1.331 1.333 1.941v2.667c0 0.928-0.421 1.62-1.101 1.883-0.256 3.94-3.193 8.117-7.065 8.117zM17.668 2.167c-2.163 0-4.383 0.936-4.407 0.947-0.084 0.035-0.172 0.053-0.26 0.053-1.839 0-3.333 1.495-3.333 3.333v2.667c0 0.261-0.155 0.5-0.393 0.608-0.473 0.215-0.913 0.583-0.94 0.731v2.661c0 0.293 0.561 0.667 1 0.667 0.367 0 0.667 0.299 0.667 0.667 0 3.301 2.845 7.333 6.167 7.333 3.24 0 5.751-3.943 5.751-7.333 0-0.368 0.301-0.667 0.667-0.667 0.276 0 0.417-0.224 0.417-0.667v-2.667c0-0.667 0-0.667-0.667-0.667-0.368 0-0.667-0.299-0.667-0.667v-2.667c-0.001-2.916-1.308-4.333-4.001-4.333zM24.335 31.167c-0.171 0-0.341-0.065-0.472-0.195l-2.667-2.667c-0.26-0.257-0.26-0.683 0-0.943 0.257-0.257 0.683-0.257 0.943 0l2.195 2.195 4.861-4.861c0.26-0.257 0.685-0.257 0.943 0 0.26 0.26 0.26 0.685 0 0.943l-5.333 5.333c-0.128 0.129-0.299 0.195-0.469 0.195zM2.335 31.167c-0.367 0-0.667-0.299-0.667-0.667 0-3.861 3.084-6.667 7.333-6.667h1.643l1.776-2.648c0.201-0.305 0.617-0.388 0.921-0.183 0.305 0.204 0.388 0.619 0.185 0.924l-1.972 2.944c-0.124 0.185-0.331 0.296-0.553 0.296h-2c-3.531 0-6 2.193-6 5.333 0 0.368-0.3 0.667-0.667 0.667z"></path></svg>';
                        $overlay = "overlay_01416b";
                        $img = 'https://jhslearninghub.ca/wp-content/uploads/2021/02/linkedin-sales-solutions-W3Jl3jREpDY-unsplash-scaled.jpg';
                    break;

                    case 'housing':
                        $cat = "Housing";
                        $icon = '<svg role="presentation" version="1.1" xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 32 32"><path style="fill:white" d="M25.987 32.001h-20c-0.368 0-0.667-0.299-0.667-0.667v-17.333c0-0.368 0.299-0.667 0.667-0.667h20c0.367 0 0.667 0.299 0.667 0.667v17.333c0 0.368-0.299 0.667-0.667 0.667zM6.653 30.668h18.667v-16h-18.667v16zM31.347 14.668c-0.008 0-0.019 0-0.027 0h-30.667c-0.277 0-0.527-0.172-0.624-0.433s-0.023-0.553 0.187-0.737l15.333-13.333c0.251-0.219 0.624-0.219 0.875 0l15.232 13.247c0.213 0.112 0.357 0.333 0.357 0.589 0 0.369-0.3 0.668-0.667 0.668zM2.436 13.335h27.101l-13.551-11.783-13.551 11.783zM24.653 8.668c-0.368 0-0.667-0.299-0.667-0.667v-6.667h-2.667v2.667c0 0.368-0.3 0.667-0.667 0.667-0.368 0-0.667-0.299-0.667-0.667v-3.333c0-0.368 0.299-0.667 0.667-0.667h4c0.367 0 0.667 0.299 0.667 0.667v7.333c0 0.368-0.299 0.667-0.667 0.667zM13.987 25.335h-4c-0.368 0-0.667-0.299-0.667-0.667v-6.667c0-0.368 0.299-0.667 0.667-0.667h4c0.368 0 0.667 0.299 0.667 0.667v6.667c0 0.368-0.299 0.667-0.667 0.667zM10.653 24.001h2.667v-5.333h-2.667v5.333zM23.32 32.001h-5.333c-0.368 0-0.667-0.299-0.667-0.667v-10.667c0-0.368 0.299-0.667 0.667-0.667h5.333c0.367 0 0.667 0.299 0.667 0.667v10.667c0 0.368-0.299 0.667-0.667 0.667zM18.653 30.668h4v-9.333h-4v9.333z"></path></svg>';
                        $overlay = "overlay_00a5df";
                        $img = 'https://jhslearninghub.ca/wp-content/uploads/2021/02/gunnar-ridderstrom-CWYjDOdA5Yc-unsplash-scaled.jpg';
                    break;

                    case 'income':
                        $cat = "Income Maintenance";
                        $icon = '<svg role="presentation" version="1.1" xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 37 32"><path style="fill:white" d="M7.411 27.688c-2.289 0-4.521-0.14-6.827-0.432-0.335-0.043-0.584-0.327-0.584-0.663v-16.003c0-0.191 0.083-0.373 0.225-0.5s0.332-0.187 0.524-0.161c2.251 0.284 4.433 0.421 6.668 0.421 3.048 0 5.831-0.26 8.521-0.511 2.721-0.256 5.535-0.519 8.647-0.519 2.292 0 4.524 0.141 6.828 0.431 0.337 0.041 0.587 0.325 0.587 0.661v16c0 0.193-0.084 0.373-0.227 0.5-0.143 0.128-0.333 0.188-0.524 0.161-2.252-0.285-4.435-0.42-6.669-0.42-3.049 0-5.833 0.259-8.524 0.513-2.72 0.253-5.533 0.52-8.645 0.52zM1.333 26.003c2.044 0.237 4.037 0.352 6.076 0.352 3.049 0 5.833-0.26 8.524-0.513 2.723-0.255 5.535-0.519 8.647-0.519 2.043 0 4.040 0.112 6.084 0.341v-14.659c-2.044-0.236-4.037-0.351-6.080-0.351-3.049 0-5.833 0.26-8.524 0.513-2.721 0.253-5.533 0.517-8.644 0.517-2.039 0-4.036-0.112-6.083-0.343v14.66zM34 24.717c-0.367 0-0.667-0.299-0.667-0.667v-15.411c-5.776-0.664-10.26-0.244-14.6 0.163-4.561 0.427-9.277 0.867-15.483 0.085-0.364-0.047-0.623-0.38-0.579-0.745 0.045-0.364 0.375-0.624 0.745-0.579 6.065 0.765 10.704 0.329 15.193-0.089 4.556-0.427 9.268-0.867 15.475-0.087 0.333 0.043 0.584 0.325 0.584 0.661v16.001c-0.003 0.368-0.303 0.667-0.669 0.667zM36.667 22.073c-0.367 0-0.667-0.299-0.667-0.667v-15.411c-5.776-0.664-10.26-0.244-14.601 0.161-4.559 0.427-9.276 0.868-15.481 0.085-0.365-0.045-0.625-0.38-0.58-0.745 0.047-0.364 0.373-0.623 0.745-0.579 6.064 0.764 10.703 0.331 15.191-0.089 4.56-0.428 9.271-0.868 15.476-0.088 0.335 0.041 0.584 0.325 0.584 0.661v16c0 0.372-0.3 0.671-0.667 0.671zM7.411 25.021c-1.385 0-2.736-0.055-4.129-0.167-0.367-0.028-0.64-0.352-0.611-0.719 0.029-0.364 0.347-0.639 0.719-0.609 1.359 0.109 2.675 0.161 4.023 0.161 0.368 0 0.667 0.301 0.667 0.667-0.001 0.367-0.301 0.667-0.668 0.667zM28.667 13.485c-0.019 0-0.033 0-0.055-0.001-1.349-0.109-2.664-0.163-4.027-0.163-0.367 0-0.667-0.299-0.667-0.667s0.3-0.667 0.667-0.667c1.396 0 2.748 0.055 4.133 0.165 0.367 0.031 0.64 0.352 0.612 0.719-0.025 0.348-0.32 0.613-0.664 0.613zM15.389 21.451c-1.289 0-2.191-0.799-2.191-1.937 0-0.367 0.299-0.667 0.667-0.667s0.667 0.3 0.667 0.667c0 0.527 0.537 0.604 0.857 0.604s0.857-0.081 0.857-0.604c0-0.309 0-0.359-0.911-0.805l-0.484-0.241c-0.887-0.439-1.652-0.813-1.652-2 0-1.147 0.901-1.948 2.191-1.948 1.277 0 2.169 0.801 2.169 1.948 0 0.368-0.299 0.667-0.667 0.667s-0.667-0.299-0.667-0.667c0-0.507-0.455-0.615-0.836-0.615-0.32 0-0.857 0.080-0.857 0.615 0 0.309 0 0.356 0.909 0.804l0.485 0.243c0.888 0.437 1.655 0.813 1.655 2-0.001 1.139-0.904 1.937-2.193 1.937zM15.333 14.655c-0.173 0-0.347-0.065-0.467-0.2-0.133-0.119-0.2-0.292-0.2-0.467 0-0.173 0.067-0.347 0.2-0.467 0.24-0.253 0.68-0.253 0.933 0 0.133 0.12 0.2 0.293 0.2 0.467s-0.067 0.348-0.2 0.467c-0.12 0.135-0.293 0.2-0.467 0.2zM14.667 13.988h1.333v1.333h-1.333v-1.333zM15.333 22.655c-0.173 0-0.347-0.067-0.467-0.199-0.133-0.12-0.2-0.293-0.2-0.468 0-0.173 0.067-0.348 0.2-0.468 0.24-0.252 0.693-0.252 0.933 0 0.133 0.12 0.2 0.295 0.2 0.468s-0.067 0.348-0.2 0.468c-0.12 0.132-0.293 0.199-0.467 0.199zM14.667 20.655h1.333v1.333h-1.333v-1.333z"></path></svg>';
                        $overlay = "overlay_a15cbf";
                        $img = 'https://jhslearninghub.ca/wp-content/uploads/2021/04/pexels-breakingpic-3305-scaled.jpg';
                    break;

                    case 'employment':
                        $cat = "Employment";
                        $icon = '<svg role="presentation" version="1.1" xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 32 32"><path style="fill:white" d="M31.333 29.333h-30.667c-0.367 0-0.667-0.3-0.667-0.667v-21.333c0-0.367 0.3-0.667 0.667-0.667h30.667c0.368 0 0.667 0.3 0.667 0.667v21.333c0 0.367-0.299 0.667-0.667 0.667zM1.333 28h29.333v-20h-29.333v20zM22 7.333c-0.367 0-0.667-0.3-0.667-0.667 0-1.472-1.195-2.667-2.667-2.667h-5.333c-1.471 0-2.667 1.195-2.667 2.667 0 0.367-0.299 0.667-0.667 0.667s-0.667-0.3-0.667-0.667c0-2.205 1.795-4 4-4h5.333c2.205 0 4 1.795 4 4 0 0.367-0.299 0.667-0.667 0.667zM26.751 18.667h-21.5c-2.895 0-5.251-2.357-5.251-5.249 0-0.368 0.299-0.708 0.667-0.708s0.667 0.253 0.667 0.624c0 2.245 1.757 4 3.917 4h21.5c2.16 0 3.916-1.755 3.916-3.916 0-0.368 0.3-0.708 0.667-0.708 0.368 0 0.667 0.253 0.667 0.625v0.084c0 2.891-2.356 5.248-5.249 5.248zM16 16.667c-1.103 0-2-0.897-2-2s0.897-2 2-2c1.104 0 2 0.897 2 2s-0.896 2-2 2zM16 14c-0.368 0-0.667 0.3-0.667 0.667s0.299 0.667 0.667 0.667 0.667-0.3 0.667-0.667-0.299-0.667-0.667-0.667z"></path></svg>';
                        $overlay = "overlay_3a825e";
                        $img = 'https://jhslearninghub.ca/wp-content/uploads/2021/11/pexels-olia-danilevich-5313361-2.jpg';
                    break;

                    case 'black-serving':
                        $cat = "Serving Black Clients";
                        $icon = '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 24 24" width="50" height="50"><line class="st0" x1="20.3" y1="14.3" x2="15.1" y2="15.3"/><path class="st0" d="M14.4,8.5l-3.5,1.1c-.6.2-1.2,0-1.4-.6-.3-.6,0-1.2.5-1.5l3.4-1.7c.6-.3,1.3-.3,1.9-.1l5,1.8"/><path class="st0" d="M3.7,14.3h2.4l3.5,3.9c.5.6,1.3.6,1.9.1.3-.3.5-.6.5-1v-.6h.2c.8.4,1.8,0,2.2-.9,0-.2.1-.4.1-.6h.5c.8,0,1.5-.7,1.5-1.5,0-.3-.1-.6-.3-.9l-3.1-4"/><path class="st0" d="M10.6,7.2l-.4-.3c-.4-.3-.8-.4-1.2-.4-.3,0-.5,0-.8.2l-4.5,1.8"/><path class="st0" d="M.8,6.8h2c.5,0,1,.4,1,.9v6.4c0,.5-.5.9-1,.9H.8"/><path class="st0" d="M23.2,15h-2c-.5,0-1-.4-1-.9v-6.4c0-.5.5-.9,1-.9h2"/><line class="st0" x1="12" y1="16.8" x2="11" y2="15.8"/><line class="st0" x1="14.4" y1="15.3" x2="13" y2="13.9"/></svg>';
                        $overlay = "overlay_eecf07";
                        $img = 'https://jhslearninghub.ca/wp-content/uploads/2025/03/2-1.png';
                    break;

                    case 'indigenous-serving':
                        $cat = "Serving Indigenous Clients";
                        $icon = '<svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 24 24" width="50" height="50"><circle class="st0" cx="12.1" cy="10.2" r="2.4"/><path class="st0" d="M16.4,17.5c-.3-2.4-2.5-4-4.9-3.7-1.9.3-3.5,1.8-3.7,3.7"/><path class="st0" d="M21,17.5c-.3-1.9-2-3.2-3.8-2.9-.6,0-1.2.3-1.7.7"/><circle class="st1" cx="6.6" cy="12" r="1.7"/><circle class="st1" cx="17.6" cy="12" r="1.7"/><path class="st0" d="M8.8,15.3c-.7-.6-1.7-.9-2.6-.8-1.5.2-2.7,1.4-2.9,2.9"/></svg>';
                        $overlay = "overlay_ff7722";
                        $img = 'https://jhslearninghub.ca/wp-content/uploads/2025/03/1-1.png';
                    break;

                    case 'client-checklists':
                        $cat = "Client Checklists";
                        $icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 59 35" width="50" height="50"><g><g><g><path class="st0" d="M56.5,20h-36a2.5,2.5,0,0,1,0-5h36a2.5,2.5,0,0,1,0,5Z"/><path class="st0" d="M8.5,20h-6a2.5,2.5,0,0,1,0-5h6a2.5,2.5,0,0,1,0,5Z"/><path class="st0" d="M56.5,35h-36a2.5,2.5,0,0,1,0-5h36a2.5,2.5,0,0,1,0,5Z"/><path class="st0" d="M8.5,35h-6a2.5,2.5,0,0,1,0-5h6a2.5,2.5,0,0,1,0,5Z"/><path class="st0" d="M56.5,5h-36a2.5,2.5,0,0,1,0-5h36a2.5,2.5,0,0,1,0,5Z"/><path class="st0" d="M8.5,5h-6a2.5,2.5,0,0,1,0-5h6a2.5,2.5,0,0,1,0,5Z"/></g></g></g></svg>';
                        $overlay = "overlay_000000";
                        $img = 'https://jhslearninghub.ca/wp-content/uploads/2025/03/737c11020e7938048d03b324f8fae86cf42e8ad1_checklist-blogpost.avif';
                    break;
                    
                   
                }
        ?>

            <a href="<?php echo has_post_format( 'link' ) ? strip_tags(get_the_excerpt()) : get_the_permalink(); ?>" 
                target="<?php echo has_post_format( 'link' ) ? '_blank' : ''; ?>" 
                title="<?php echo 'Click to access '. get_the_title() ?>" 
                class="box-link cactus-filter-card <?php echo $atts['type'] . ' ' . implode(' ', $categories) . ' ' . implode(' ', $tags); ?>" 
                style="z-index: 1000;">
                
                <div class="nectar-fancy-box style-5 using-img radius <?php echo $overlay; ?>" 
                    data-align="bottom" 
                    data-overlay-opacity="0.9" 
                    data-overlay-opacity-hover="0.8" 
                    data-style="parallax_hover" 
                    data-border-radius="5px" 
                    data-animation="" 
                    data-delay="" 
                    data-color="accent-color" 
                    style="z-index: 100;">
                    
                    <div class="parallaxImg-wrap ">
                        <div class="parallaxImg" id="parallaxImg__0">
                            <div class="parallaxImg-container" 
                                style="transform: perspective(714px);">
                                <div class="parallaxImg-layers">
                                    <div class="parallaxImg-rendered-layer radius" 
                                        data-layer="0" 
                                        style="transform: translateZ(0px);">
                                        <div class="bg-img loaded" 
                                            style="background-image: url(&quot;<?php echo $img ;?>&quot;);">
                                        </div>
                                    </div>
                                    <div class="parallaxImg-rendered-layer" 
                                        data-layer="1" 
                                        style="transform: translateZ(29.0889px);"> 
                                        <div class="meta-wrap" style="min-height: 400px;">
                                            <div class="box-inner">
                                                <span>
                                                    <?php echo $icon ;?>
                                                    <h4><?php echo ( isset($cat_name) ? $cat_title : $cat);?></h4>
                                                </span>
                                                <div>
                                                    <h3><?php the_title(); ?></h3>
                                                    <?php if(has_post_format( 'link' )):?>
                                                        <?php the_content(); ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div> 
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>           

        <?php endwhile; wp_reset_postdata(); ?>

        <div class="no-results hide">
            <p>There are no training videos available for this category. <a class="reset-filters" href="#">Reset filters</a> to see avaiable training videos.</p>
        </div>

    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('filtered_posts', 'posttype_filter_shortcode');

// Rename posts in the admin menu
function update_post_label() {
    global $menu;
    global $submenu;
    $submenu['edit.php'][5][0] = 'Resource';
    $submenu['edit.php'][10][0] = 'Add Resource';
    $submenu['edit.php'][16][0] = 'Resource Tags';
    $menu[5][0] = 'Resources';
 }
 add_action( 'admin_menu', 'update_post_label' );
