<?php

defined('ABSPATH') || exit;

add_action('init', 'wnbell_change_metakeys_once');
function wnbell_change_metakeys_once()
{
    if (get_option('wnbell_change_metakeys_complete') != 'completed') {
        $options = get_option('wnbell_options');
        $length = sizeof(isset($options['wnbell_name']) ? $options['wnbell_name'] : array(0));
        $meta_keys = array();
        for ($i = 0; $i < $length; $i++) {
            $meta_keys[] = array($options['wnbell_name'][$i], 'wnbell_item_name_' . $i);
        }
        foreach ($meta_keys as $k) {

            $args = array(
                'post_type' => array('wnbell_notifications'),
                'posts_per_page' => -1,
                //'post_status' => 'publish',
                'meta_key' => $k[0],
            );

            $the_query = new WP_Query($args);

            if ($the_query->have_posts()) {

                while ($the_query->have_posts()) {
                    $the_query->the_post();

                    $meta = get_post_meta(get_the_ID(), $k[0], true);

                    if ($meta) {

                        // Migrate the meta to the new name

                        update_post_meta(get_the_ID(), $k[1], $meta); // add the meta with the new name
                        delete_post_meta(get_the_ID(), $k[0]); // delete the old meta

                    }
                }

            }

            wp_reset_postdata(); // Restore original Post Data

        }
        update_option('wnbell_change_metakeys_complete', 'completed');
    }
}
