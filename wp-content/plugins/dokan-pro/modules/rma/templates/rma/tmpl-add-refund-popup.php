<script type="text/html" id="tmpl-dokan-send-refund">
    <div id="dokan-send-refund-popup" class="dokan-rma-popup white-popup">
        <form method="post" id="dokan-send-refund-popup-form">
            <h2><i class="fas fa-undo-alt" aria-hidden="true"></i>&nbsp;<?php esc_html_e( 'Send Refund Request', 'dokan' ); ?></h2>

            <div class="rma-popup-content refund-content"></div>

            <div class="rma-popup-action">
                <input type="submit" class="dokan-btn dokan-btn-theme" name="dokan_refund_submit" value="<?php esc_attr_e( 'Send Request', 'dokan' ); ?>">
            </div>
        </form>
    </div>
</script>
