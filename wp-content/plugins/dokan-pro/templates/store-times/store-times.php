<div class="dokan-time-slots">
    <?php
    foreach ( $dokan_days as $day_key => $day ) :
        $working_status = ! empty( $store_info[ $day_key ]['status'] ) ? $store_info[ $day_key ]['status'] : 'close';
        $opening_time   = dokan_get_store_times( $day_key, 'opening_time' );
        $closing_time   = dokan_get_store_times( $day_key, 'closing_time' );
        $full_day       = false;

        if ( $opening_time === '12:00 am' && $closing_time === '11:59 pm' ) {
            $full_day = true;
        }
        ?>
        <div class="dokan-store-times">
            <div class="dokan-form-group">
                <label class="day control-label" for="working-days">
                    <?php echo esc_html( dokan_get_translated_days( $day_key ) ); ?>
                </label>

                <label class="dokan-on-off dokan-status" for="<?php echo esc_attr( $day_key ); ?>-working-status">
                    <p class="switch tips">
                        <span class="slider round"></span>
                    </p>
                    <p class='working-status'>
                        <input type="hidden"
                            name="store_day[<?php echo esc_attr( $day_key ); ?>]"
                            id="<?php echo esc_attr( $day_key ); ?>-working-status"
                            class="dokan-on-off toogle-checkbox"
                            value="<?php echo 'open' === $working_status ? '1' : '0'; ?>" />
                        <span class="open-status"><?php esc_html_e( 'Open', 'dokan' ); ?></span>
                        <span class="close-status"><?php esc_html_e( 'Closed', 'dokan' ); ?></span>
                    </p>
                </label>

                <!-- Store opening times start -->
                <label for="opening-time-<?php echo esc_attr( $day_key ); ?>" class="time" style="visibility: <?php echo 'open' === $working_status ? 'visible' : 'hidden'; ?>;">
                    <div class='clock-picker'>
                        <span class="far fa-clock"></span>
                        <input type="text" class="dokan-form-control opening-time"
                            id="opening-time-<?php echo esc_attr( $day_key ); ?>"
                            placeholder="<?php echo esc_attr( $place_start ); ?>"
                            value="<?php echo esc_attr( $full_day ? $all_day : $opening_time ); ?>" />
                        <input type="hidden" value="<?php echo esc_attr( $opening_time ); ?>"
                            class="clockOne" name="opening_time[<?php echo esc_attr( $day_key ); ?>][]" />
                        <span class="fa fa-exclamation-triangle"></span>
                    </div>
                </label>
                <!-- Store opening times end -->

                <span class="time-to fas fa-minus"
                    style="visibility: <?php echo 'open' === $working_status ? 'visible' : 'hidden'; ?>; display: <?php echo $full_day ? 'none' : 'block'; ?>"
                ></span>

                <!-- Store closing times start -->
                <label class="time" for="closing-time-<?php echo esc_attr( $day_key ); ?>"
                    style="visibility: <?php echo 'open' === $working_status ? 'visible' : 'hidden'; ?>; display: <?php echo $full_day ? 'none' : 'block'; ?>">
                    <div class='clock-picker'>
                        <span class="far fa-clock"></span>
                        <input type="text" class="dokan-form-control closing-time"
                            id="closing-time-<?php echo esc_attr( $day_key ); ?>"
                            placeholder="<?php echo esc_attr( $place_end ); ?>"
                            value="<?php echo esc_attr( ! $full_day ? $closing_time : '' ); ?>" />
                        <input type="hidden" value="<?php echo esc_attr( $closing_time ); ?>"
                            class="clockTwo" name="closing_time[<?php echo esc_attr( $day_key ); ?>][]" />
                        <span class="fa fa-exclamation-triangle"></span>
                    </div>
                </label>
                <!-- Store closing times end -->

                <!-- Store times action start -->
                <label for="open-close-actions" class="open-close-actions"
                    style="visibility: <?php echo 'open' === $working_status ? 'visible' : 'hidden'; ?>;">
                    <a href="" class="remove-store-closing-time"><span class="fas fa-times"></span></a>
                    <a href="" class="added-store-opening-time" style='display: <?php echo $full_day ? 'none' : 'block'; ?>'><?php echo esc_html( $add_action ); ?></a>
                </label>
                <!-- Store times action end -->

            </div>

            <?php
            /**
             * Added store times after store time settings.
             *
             * @since 3.7.8
             *
             * @param string $day_key
             * @param string $working_status
             */
            do_action( 'after_dokan_store_time_settings_form', $day_key, $working_status );
            ?>

        </div>
    <?php endforeach; ?>
</div>
