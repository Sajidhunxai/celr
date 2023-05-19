<div class='dokan-form-group'>
    <span class="day and-time"></span>

    <!-- Store opening times start -->
    <label for='opening-time-<?php echo esc_attr( $current_day ); ?>' class='time' >
        <div class='clock-picker'>
            <span class="far fa-clock"></span>
            <input type='text' class='dokan-form-control opening-time'
                id='opening-time-<?php echo esc_attr( $current_day ); ?>'
                placeholder='<?php echo esc_attr( $place_start ); ?>'
                value='<?php echo esc_attr( dokan_get_store_times( $current_day, 'opening_time', $index ) ); ?>'
                autocomplete='off' />
            <input type="hidden" value='<?php echo esc_attr( dokan_get_store_times( $current_day, 'opening_time', $index ) ); ?>'
                class="clockOne" name="opening_time[<?php echo esc_attr( $current_day ); ?>][]" />
            <span class="fa fa-exclamation-triangle"></span>
        </div>
    </label>
    <!-- Store opening times end -->

    <span class='time-to fas fa-minus'></span>

    <!-- Store closing times start -->
    <label for='closing-time-<?php echo esc_attr( $current_day ); ?>' class='time' >
        <div class='clock-picker'>
            <span class="far fa-clock"></span>
            <input type='text' class='dokan-form-control closing-time'
                id='closing-time-<?php echo esc_attr( $current_day ); ?>'
                placeholder='<?php echo esc_attr( $place_end ); ?>'
                value='<?php echo esc_attr( dokan_get_store_times( $current_day, 'closing_time', $index ) ); ?>'
                autocomplete='off' />
            <input type="hidden" value='<?php echo esc_attr( dokan_get_store_times( $current_day, 'closing_time', $index ) ); ?>'
                class="clockTwo" name='closing_time[<?php echo esc_attr( $current_day ); ?>][]' />
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
