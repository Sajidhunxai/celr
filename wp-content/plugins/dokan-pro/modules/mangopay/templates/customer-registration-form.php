<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <?php
        foreach ( $fields as $key => $data ) :
            woocommerce_form_field( "dokan_user_$key", $data );
        endforeach;
    ?>
</p>
