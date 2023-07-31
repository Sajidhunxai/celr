<?php

namespace Devnet\PPH\Includes;

class Helper
{
    /**
     * Create price history entry in the pph table.
     *
     * @since     1.0.0
     */
    public static function create_entry( $product, $interval = null )
    {
        if ( !$product ) {
            return;
        }
        $product_id = $product->get_id();
        $parent_product_id = $product->get_parent_id();
        $regular_price = $product->get_regular_price();
        $sale_price = $product->get_sale_price();
        $price = $product->get_price();
        $type = $product->get_type();
        $currency = get_woocommerce_currency();
        $hidden = 0;
        $date_created = current_time( 'mysql' );
        if ( !$price ) {
            return;
        }
        global  $wpdb ;
        $table_name = $wpdb->prefix . 'pph_price_history';
        $most_recent_entry = $wpdb->get_row( $wpdb->prepare( "SELECT id, price, date_created FROM {$table_name} WHERE product_id = %d ORDER BY date_created DESC LIMIT 1", $product_id ) );
        $interval_has_elapsed = true;
        $price_has_changed = true;
        
        if ( $most_recent_entry ) {
            // Check if the price has changed
            $price_has_changed = (double) $most_recent_entry->price !== (double) $price;
            
            if ( $interval && is_numeric( $interval ) ) {
                $current_timestamp = strtotime( $date_created );
                $entry_timestamp = strtotime( $most_recent_entry->date_created );
                // Check if interval is provided and interval time has elapsed
                $interval_has_elapsed = $current_timestamp - $entry_timestamp >= $interval;
            }
        
        }
        
        // Only insert a new row or update existing row if necessary
        
        if ( $price_has_changed ) {
            // Prepare the data for insertion or update
            $data = [
                'product_id'        => $product_id,
                'parent_product_id' => $parent_product_id,
                'regular_price'     => $regular_price,
                'sale_price'        => $sale_price,
                'price'             => $price,
                'product_type'      => $type,
                'currency'          => $currency,
                'hidden'            => $hidden,
                'date_created'      => $date_created,
            ];
            $placeholders = [
                '%d',
                '%s',
                '%s',
                '%s',
                '%f',
                '%s',
                '%s',
                '%d',
                '%s'
            ];
            
            if ( $interval_has_elapsed ) {
                // Insert a new row
                $wpdb->insert( $table_name, $data, $placeholders );
            } else {
                // ignore current date. We need preserve first date to determine if interval has elapsed.
                unset( $data['date_created'] );
                // For exiting entry we'll update all data except date.
                self::update_entry( $most_recent_entry->id, $data );
            }
        
        }
    
    }
    
    /**
     * Update price history entry in the pph table.
     *
     * @since     1.0.0
     */
    public static function update_entry( $id, $data )
    {
        if ( !$id ) {
            return;
        }
        global  $wpdb ;
        $table_name = $wpdb->prefix . 'pph_price_history';
        $data_to_update = [];
        $placeholders = [];
        
        if ( isset( $data['product_id'] ) ) {
            $data_to_update['product_id'] = $data['product_id'];
            $placeholders[] = '%d';
        }
        
        
        if ( isset( $data['parent_product_id'] ) ) {
            $data_to_update['parent_product_id'] = $data['parent_product_id'];
            $placeholders[] = '%d';
        }
        
        
        if ( isset( $data['regular_price'] ) ) {
            $data_to_update['regular_price'] = $data['regular_price'];
            $placeholders[] = '%s';
        }
        
        
        if ( isset( $data['sale_price'] ) ) {
            $data_to_update['sale_price'] = $data['sale_price'];
            $placeholders[] = '%s';
        }
        
        
        if ( isset( $data['price'] ) ) {
            $data_to_update['price'] = $data['price'];
            $placeholders[] = '%f';
        }
        
        
        if ( isset( $data['product_type'] ) ) {
            $data_to_update['product_type'] = $data['product_type'];
            $placeholders[] = '%s';
        }
        
        
        if ( isset( $data['currency'] ) ) {
            $data_to_update['currency'] = $data['currency'];
            $placeholders[] = '%s';
        }
        
        
        if ( isset( $data['hidden'] ) ) {
            $data_to_update['hidden'] = $data['hidden'];
            $placeholders[] = '%d';
        }
        
        
        if ( isset( $data['date_created'] ) ) {
            $data_to_update['date_created'] = $data['date_created'];
            $placeholders[] = '%s';
        }
        
        if ( !empty($data_to_update) ) {
            $wpdb->update(
                $table_name,
                $data_to_update,
                [
                'id' => $id,
            ],
                $placeholders,
                [ '%d' ]
            );
        }
    }
    
    /**
     * Delete price history entry from the pph table.
     *
     * @since     1.0.0
     */
    public static function delete_product_entries( $product_id )
    {
        if ( !$product_id ) {
            return;
        }
        global  $wpdb ;
        $table_name = $wpdb->prefix . 'pph_price_history';
        $wpdb->query( $wpdb->prepare( "DELETE FROM {$table_name} WHERE product_id = %d", $product_id ) );
    }
    
    /**
     * Get price history entry from the pph table.
     *
     * @since     1.0.0
     */
    public static function get_price_history( $product_id, $get_hidden = true, $range = '' )
    {
        if ( !$product_id ) {
            return;
        }
        global  $wpdb ;
        $table_name = $wpdb->prefix . 'pph_price_history';
        $hidden_rule = ( $get_hidden ? '' : 'AND hidden <> 1' );
        // All time entries
        $price_history = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE product_id = %d {$hidden_rule}  ORDER BY date_created ASC", $product_id ), ARRAY_A );
        
        if ( $range ) {
            // From last xy days/months
            // convert input like "7_days" to "-7 days".
            $str_range = '-' . str_replace( '_', ' ', $range );
            $price_history = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE product_id = %d {$hidden_rule} AND date_created >= %s ORDER BY date_created ASC", $product_id, date( 'Y-m-d', strtotime( $str_range ) ) ), ARRAY_A );
        }
        
        return $price_history;
    }
    
    /**
     * Delete old data from the pph table.
     *
     * @since     2.1.3
     */
    public static function delete_old_data( $table, $older_than )
    {
        if ( !$older_than ) {
            return;
        }
        $date_interval = "";
        $interval_value = "";
        // Extract the numeric value from the older_than using regex
        preg_match( '/(\\d+)_\\w+/', $older_than, $matches );
        
        if ( !empty($matches) ) {
            $interval_value = intval( $matches[1] );
            $date_interval = preg_replace( '/\\d+_/', '', $older_than );
            $date_interval = rtrim( $date_interval, 's' );
        }
        
        global  $wpdb ;
        $table_name = $wpdb->prefix . 'pph_' . $table;
        $wpdb->get_results( $wpdb->prepare( "DELETE FROM {$table_name} WHERE date_created < DATE_SUB(NOW(), INTERVAL %d {$date_interval})", $interval_value ), ARRAY_A );
        return $wpdb->rows_affected;
    }

}