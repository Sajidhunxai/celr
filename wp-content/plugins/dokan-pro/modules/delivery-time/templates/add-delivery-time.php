<div class='dokan-form-group' style="display: <?php echo isset( $status ) && '1' === $status ? 'flex' : 'none'; ?>;">
    <label class='day and-time'></label>

    <!-- Store opening times start -->
    <label for='delivery-opening-time-<?php echo esc_attr( $current_day ); ?>' class='time' >
        <div class='clock-picker'>
            <span class="far fa-clock"></span>
            <input type='text' class='dokan-form-control opening-time' placeholder='<?php echo esc_attr( $place_start ); ?>'
                id='delivery-opening-time-<?php echo esc_attr( $current_day ); ?>' autocomplete='off'
                value='<?php echo esc_attr( $opening_time ); ?>' />
            <input type='hidden' value='<?php echo esc_attr( $opening_time ); ?>' class='clockOne'
                name='delivery_opening_time[<?php echo esc_attr( $current_day ); ?>][]' />
            <span class="fa fa-exclamation-triangle"></span>
        </div>
    </label>
    <!-- Store opening times end -->

    <span class='time-to fas fa-minus'></span>

    <!-- Store closing times start -->
    <label for='delivery-closing-time-<?php echo esc_attr( $current_day ); ?>' class='time' >
        <div class='clock-picker'>
            <span class="far fa-clock"></span>
            <input type='text' class='dokan-form-control closing-time' placeholder='<?php echo esc_attr( $place_end ); ?>'
                id='delivery-closing-time-<?php echo esc_attr( $current_day ); ?>' autocomplete='off'
                value='<?php echo esc_attr( $closing_time ); ?>' />
            <input type='hidden' value='<?php echo esc_attr( $closing_time ); ?>' class='clockTwo'
                name='delivery_closing_time[<?php echo esc_attr( $current_day ); ?>][]' />
            <span class="fa fa-exclamation-triangle"></span>
        </div>
    </label>
    <!-- Store closing times end -->

    <!-- Store times action start -->
    <label for='open-close-actions' class='open-close-actions'>
        <a href='' class='remove-store-closing-time'>
            <span class="fas fa-times"></span>
        </a>
        <a href='' class='added-store-opening-time'>
            <?php echo esc_html( $add_action ); ?>
        </a>
    </label>
    <!-- Store times action end -->
</div>
