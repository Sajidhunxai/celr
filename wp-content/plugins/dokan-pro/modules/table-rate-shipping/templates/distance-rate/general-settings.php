<div class="dokan-form-group">
    <label class="dokan-w3 dokan-control-label" for="distance_rate_title"><?php esc_html_e( 'Method Title', 'dokan' ); ?></label>
    <div class="dokan-w5 dokan-text-left">
        <input id="distance_rate_title" value="<?php echo esc_attr( $title ); ?>" name="distance_rate_title" placeholder="<?php esc_attr_e( 'Method Title', 'dokan' ); ?>" class="dokan-form-control input-md" type="text">
        <p><?php esc_html_e( 'This controls the title which the user sees during checkout.', 'dokan' ); ?></p>
    </div>
</div>

<div class="dokan-form-group">
    <label class="dokan-w3 dokan-control-label" for="distance_rate_tax_status"><?php esc_html_e( 'Tax Status', 'dokan' ); ?></label>
    <div class="dokan-w5 dokan-text-left">
        <select id="distance_rate_tax_status" name="distance_rate_tax_status" class="dokan-on-off dokan-form-control">
            <option value="taxable" <?php selected( $tax_status, 'taxable' ); ?>><?php esc_html_e( 'Taxable', 'dokan' ); ?></option>
            <option value="none" <?php selected( $tax_status, 'none' ); ?>><?php esc_html_e( 'None', 'dokan' ); ?></option>
        </select>
    </div>
</div>

<div class="dokan-form-group">
    <label class="dokan-w3 dokan-control-label" for="dokan_distance_rate_mode"><?php esc_html_e( 'Transportation Mode', 'dokan' ); ?></label>
    <div class="dokan-w5 dokan-text-left">
        <select id="dokan_distance_rate_mode" name="distance_rate_mode" class="dokan-on-off dokan-form-control">
            <option value="driving" <?php selected( $distance_rate_mode, 'driving' ); ?>><?php esc_html_e( 'Driving', 'dokan' ); ?></option>
            <option value="walking" <?php selected( $distance_rate_mode, 'walking' ); ?>><?php esc_html_e( 'Walking', 'dokan' ); ?></option>
            <option value="bicycle" <?php selected( $distance_rate_mode, 'bicycle' ); ?>><?php esc_html_e( 'Bicycling', 'dokan' ); ?></option>
        </select>
    </div>
</div>

<div class="dokan-form-group">
    <label class="dokan-w3 dokan-control-label" for="dokan_distance_rate_avoid"><?php esc_html_e( 'Avoid', 'dokan' ); ?></label>
    <div class="dokan-w5 dokan-text-left">
        <select id="dokan_distance_rate_avoid" name="distance_rate_avoid" class="dokan-on-off dokan-form-control">
            <option value="none" <?php selected( $distance_rate_avoid, 'none' ); ?>><?php esc_html_e( 'None', 'dokan' ); ?></option>
            <option value="tolls" <?php selected( $distance_rate_avoid, 'tolls' ); ?>><?php esc_html_e( 'Tolls', 'dokan' ); ?></option>
            <option value="highways" <?php selected( $distance_rate_avoid, 'highways' ); ?>><?php esc_html_e( 'Highways', 'dokan' ); ?></option>
            <option value="ferries" <?php selected( $distance_rate_avoid, 'ferries' ); ?>><?php esc_html_e( 'Ferries', 'dokan' ); ?></option>
        </select>
    </div>
</div>

<div class="dokan-form-group">
    <label class="dokan-w3 dokan-control-label" for="dokan_distance_rate_unit"><?php esc_html_e( 'Distance Unit', 'dokan' ); ?></label>
    <div class="dokan-w5 dokan-text-left">
        <select id="dokan_distance_rate_unit" name="distance_rate_unit" class="dokan-on-off dokan-form-control">
            <option value="metric" <?php selected( $distance_rate_unit, 'metric' ); ?>><?php esc_html_e( 'Metric', 'dokan' ); ?></option>
            <option value="imperial" <?php selected( $distance_rate_unit, 'imperial' ); ?>><?php esc_html_e( 'Imperial', 'dokan' ); ?></option>
        </select>
    </div>
</div>

<div class="dokan-form-group">
    <label class="dokan-w3 dokan-control-label" for="dokan_distance_rate_show_distance"><?php esc_html_e( 'Show distance', 'dokan' ); ?></label>
    <div class="dokan-w5 dokan-text-left">
        <div class="checkbox">
            <label>
                <input type="hidden" name="distance_rate_show_distance" value="no">
                <input type="checkbox" id="dokan_distance_rate_show_distance" name="distance_rate_show_distance" value="yes" <?php checked( $distance_rate_show_distance, 'yes' ); ?>> <?php esc_html_e( 'Show the distance next to the shipping cost for the customer.', 'dokan' ); ?>
            </label>
        </div>
    </div>
</div>

<div class="dokan-form-group">
    <label class="dokan-w3 dokan-control-label" for="dokan_distance_rate_show_duration"><?php esc_html_e( 'Show duration', 'dokan' ); ?></label>
    <div class="dokan-w5 dokan-text-left">
        <div class="checkbox">
            <label>
                <input type="hidden" name="distance_rate_show_duration" value="no">
                <input type="checkbox" id="dokan_distance_rate_show_duration" name="distance_rate_show_duration" value="yes" <?php checked( $distance_rate_show_duration, 'yes' ); ?>> <?php esc_html_e( 'Show the duration next to the shipping cost for the customer.', 'dokan' ); ?>
            </label>
        </div>
    </div>
</div>

<h3 class="dokan-text-left"><?php esc_html_e( 'Shipping Address', 'dokan' ); ?></h3>
<p class="dokan-text-left"><?php esc_html_e( 'Please enter the address that you are shipping from below to work out the distance of the customer from the shipping location.', 'dokan' ); ?></p>

<div class="dokan-form-group">
    <label class="dokan-w3 dokan-control-label" for="dokan_distance_rate_address_1"><?php esc_html_e( 'Address 1', 'dokan' ); ?></label>
    <div class="dokan-w5 dokan-text-left">
        <input id="dokan_distance_rate_address_1" value="<?php echo esc_attr( $distance_rate_address_1 ); ?>" name="distance_rate_address_1" class="dokan-form-control input-md" type="text">
        <p><?php esc_html_e( 'First address line of where you are shipping from.', 'dokan' ); ?></p>
    </div>
</div>

<div class="dokan-form-group">
    <label class="dokan-w3 dokan-control-label" for="dokan_distance_rate_address_2"><?php esc_html_e( 'Address 2', 'dokan' ); ?></label>
    <div class="dokan-w5 dokan-text-left">
        <input id="dokan_distance_rate_address_2" value="<?php echo esc_attr( $distance_rate_address_2 ); ?>" name="distance_rate_address_2" class="dokan-form-control input-md" type="text">
        <p><?php esc_html_e( 'Second address line of where you are shipping from.', 'dokan' ); ?></p>
    </div>
</div>

<div class="dokan-form-group">
    <label class="dokan-w3 dokan-control-label" for="dokan_distance_rate_city"><?php esc_html_e( 'City', 'dokan' ); ?></label>
    <div class="dokan-w5 dokan-text-left">
        <input id="dokan_distance_rate_city" value="<?php echo esc_attr( $distance_rate_city ); ?>" name="distance_rate_city" class="dokan-form-control input-md" type="text">
        <p><?php esc_html_e( 'City of where you are shipping from.', 'dokan' ); ?></p>
    </div>
</div>

<div class="dokan-form-group">
    <label class="dokan-w3 dokan-control-label" for="dokan_distance_rate_postal_code"><?php esc_html_e( 'Zip/Postal Code', 'dokan' ); ?></label>
    <div class="dokan-w5 dokan-text-left">
        <input id="dokan_distance_rate_postal_code" value="<?php echo esc_attr( $distance_rate_postal_code ); ?>" name="distance_rate_postal_code" class="dokan-form-control input-md" type="text">
        <p><?php esc_html_e( 'Zip or Postal Code of where you are shipping from.', 'dokan' ); ?></p>
    </div>
</div>

<div class="dokan-form-group">
    <label class="dokan-w3 dokan-control-label" for="dokan_distance_rate_state_province"><?php esc_html_e( 'State/Province', 'dokan' ); ?></label>
    <div class="dokan-w5 dokan-text-left">
        <input id="dokan_distance_rate_state_province" value="<?php echo esc_attr( $distance_rate_state_province ); ?>" name="distance_rate_state_province" class="dokan-form-control input-md" type="text">
        <p><?php esc_html_e( 'State/Province of where you are shipping from.', 'dokan' ); ?></p>
    </div>
</div>

<div class="dokan-form-group">
    <label class="dokan-w3 dokan-control-label" for="dokan_distance_rate_country"><?php esc_html_e( 'Country', 'dokan' ); ?></label>
    <div class="dokan-w5 dokan-text-left">
        <?php
        $country_obj = new WC_Countries();
        $countries   = $country_obj->countries;
        ?>
        <select name="distance_rate_country" class="dokan-form-control input-md" id="dokan_distance_rate_country">
            <option value=""><?php esc_html_e( '- Select a location -', 'dokan' ); ?></option>
            <?php foreach ( $countries as $country ) : ?>
                <option value="<?php echo esc_attr( $country ); ?>" <?php selected( $distance_rate_country, $country, true ); ?>><?php echo esc_html( $country ); ?></option>
            <?php endforeach; ?>
        </select>
        <p><?php esc_html_e( 'Country of where you are shipping from.', 'dokan' ); ?></p>
    </div>
</div>

<?php if ( ! empty( $get_address ) ) : ?>
    <div class="dokan-form-group">
        <label class="dokan-w3 dokan-control-label" for="dokan_distance_rate_state_province"><?php esc_html_e( 'Map', 'dokan' ); ?></label>
        <div class="dokan-w9 dokan-text-left">
            <p><?php echo esc_html( $get_address ); ?></p>
            <iframe width="100%" height="350" frameborder="0" style="border:0" src="https://www.google.com/maps/embed/v1/place?q=<?php echo urlencode( $get_address ); ?>&key=<?php echo $gmap_api_key; ?>"></iframe>
        </div>
    </div>
<?php endif; ?>
