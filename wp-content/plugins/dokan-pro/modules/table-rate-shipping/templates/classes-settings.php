<?php if ( ! $classes ) : ?>
    <p class="description"><?php esc_html_e( 'No shipping classes exist - you can ignore this option :)', 'dokan' ); ?></p>
<?php else : ?>
    <table id="dokan_shipping_rates_classes" class="widefat shippingrows">
        <thead>
        <tr>
            <th><?php esc_html_e( 'Class', 'dokan' ); ?></th>
            <th><?php esc_html_e( 'Priority', 'dokan' ); ?></th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <td colspan="2">
                <p class="description per_order">
                    <?php esc_html_e( 'When calculating shipping, the cart contents will be searched for all shipping classes. If all product shipping classes are identical, the corresponding class will be used.', 'dokan' ); ?>
                </p>
                <p class="description per_order">
                    <?php esc_html_e( 'If there are a mix of classes then the class with the lowest number priority (defined above) will be used.', 'dokan' ); ?>
                </p>
            </td>
        </tr>
        </tfoot>
        <tbody>
        <tr>
            <th><?php esc_html_e( 'Default', 'dokan' ); ?></th>
            <td><input type="text" size="2" name="dokan_table_rate_default_priority" value="<?php echo esc_attr( $default_priority ); ?>" /></td>
        </tr>
        <?php
        foreach ( $classes as $class ) {
            $priority = ( isset( $class_priorities[ $class->slug ] ) ) ? $class_priorities[ $class->slug ] : 10;

            echo '<tr><th>' . esc_html( $class->name ) . '</th><td><input type="text" value="' . esc_attr( $priority ) . '" size="2" name="dokan_table_rate_priorities[' . esc_attr( $class->slug ) . ']" /></td></tr>';
        }
        ?>
        </tbody>
    </table>
<?php endif; ?>
