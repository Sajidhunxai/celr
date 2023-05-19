<div id="dokan_table_rate_shipping_rows">
    <h3 class="dokan-text-left"><?php esc_html_e( 'Table Rates', 'dokan' ); ?></h3>
    <?php
    dokan_get_template_part(
        'rows-settings', '', [
            'is_table_rate_shipping' => true,
            'instance_id'            => $instance_id,
            'shipping_classes'       => $shipping_classes,
            'normalized_rates'       => $normalized_rates,
        ]
    );
    ?>
</div>

<div id="dokan_table_rate_class_priorities">
    <h3 class="dokan-text-left"><?php esc_html_e( 'Class Priorities', 'dokan' ); ?></h3>

    <?php
        dokan_get_template_part(
            'classes-settings', '', [
                'is_table_rate_shipping' => true,
                'instance_id'            => $instance_id,
                'classes'                => $classes,
                'default_priority'       => $default_priority,
                'class_priorities'       => $class_priorities,
            ]
        );
        ?>
</div>
