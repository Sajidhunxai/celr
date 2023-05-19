<?php

/**
 * Update vendor and product geolocation data
 *
 * @since 2.9.11
 */
class Dokan_SPMV_Update_Product_Visibility extends Abstract_Dokan_Background_Processes {

    /**
     * Action
     *
     * @since 2.9.11
     *
     * @var string
     */
    protected $action = 'Dokan_SPMV_Update_Product_Visibility';

    /**
     * Perform updates
     *
     * @since 2.9.11
     *
     * @param mixed $item
     *
     * @return mixed
     */
    public function task( $item ) {
        global $wpdb;

        if ( empty( $item['map_ids'] ) ) {
            return false;
        }

        $map_id = absint( array_pop( $item['map_ids'] ) );

        dokan_spmv_update_clone_visibilities( $map_id );

        return $item;
    }
}
