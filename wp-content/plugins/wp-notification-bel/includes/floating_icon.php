<?php

defined( 'ABSPATH' ) || exit;
add_action( 'wp_footer', 'wnbell_footer_display' );
function wnbell_floating_display()
{
    $settings = get_option( 'wnbell_settings' );
    
    if ( is_user_logged_in() ) {
        $floating = ( isset( $settings['floating'] ) ? $settings['floating'] : 0 );
        
        if ( $floating ) {
            $class = "wnbell-sticky-btn wnbell-dropdown-toggle";
            ?>
    <div class="<?php 
            echo  $class ;
            ?>">
<?php 
            echo  wnbell_notification_display( $floating ) ;
            ?>
</div>
<?php 
        }
    
    } else {
        $floating_lo = ( isset( $settings['floating_lo'] ) ? $settings['floating_lo'] : 0 );
        
        if ( $floating_lo ) {
            $class = "wnbell-sticky-btn wnbell-dropdown-toggle";
            ?>
    <div class="<?php 
            echo  $class ;
            ?>">
<?php 
            echo  wnbell_notification_display_logged_out( $floating_lo ) ;
            ?>
</div>
<?php 
        }
    
    }

}

function wnbell_footer_display()
{
    ?>
    <div class="wnbell_dropdown_wrap_ss" id="wnbell_dropdown_wrap_ss">
    <div class="wnbell-spinner-wrap-ss" id="wnbell-spinner-wrap-ss">
<span class="wnbell-spinner-ss" id="wnbell-spinner-ss"></span>
</div>
    <div class="wnbell_dropdown_list_ss" id="wnbell_dropdown_list_ss"></div>
    </div>
    <?php 
    wnbell_floating_display();
}
