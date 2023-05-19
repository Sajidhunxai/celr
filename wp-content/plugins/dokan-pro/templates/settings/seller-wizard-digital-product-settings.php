<tr>
    <th scope="row"><label for="dokan_digital_product"><?php echo esc_html( $label ); ?></label></th>
    <td>
        <select
            class="wc-enhanced-select"
            name="dokan_digital_product"
            id="dokan_digital_product"
            data-placeholder="<?php esc_html_e( 'Select one', 'dokan' ); ?>"
        >
            <?php foreach ( $plans as $plan_key => $plan ) : ?>
                <option value="<?php echo esc_attr( $plan_key ); ?>" <?php selected( $digital_mode, $plan_key ); ?>>
                    <?php echo esc_html( $plan ); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </td>
</tr>
