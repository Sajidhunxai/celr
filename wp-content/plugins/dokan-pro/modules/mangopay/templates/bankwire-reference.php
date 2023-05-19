<h2><?php esc_html_e( 'Information for your bank transfer', 'dokan' ); ?></h2>
<p><?php esc_html_e( 'To complete your order, please do a bank transfer with the following information, including the bank wire reference.', 'dokan' ); ?><p>
<p><?php esc_html_e( 'We will process your order once the transfer is received.', 'dokan' ); ?><p>

<ul class="order_details">
    <li class="mp_amount">
        <?php esc_html_e( 'Amount', 'dokan' ); ?>:
        <strong><?php echo esc_html( wc_price( $amount, [ 'currency' => $currency ] ) ); ?></strong>
    </li>
    <li class="mp_owner">
        <?php esc_html_e( 'Bank account owner', 'dokan' ); ?>:
        <strong><?php echo esc_html( $owner ); ?></strong>
    </li>
    <li class="mp_address">
        <?php esc_html_e( 'Owner address', 'dokan' ); ?>:
        <div class="mp_address_block">
            <strong><?php echo esc_html( $address ); ?></strong>
        </div>
    </li>
    <li class="mp_iban">
        <?php esc_html_e( 'IBAN', 'dokan' ); ?>:
        <strong><?php echo esc_html( $iban ); ?></strong>
    </li>
    <li class="mp_bic">
        <?php esc_html_e( 'BIC', 'dokan' ); ?>:
        <strong><?php echo esc_html( $bic ); ?></strong>
    </li>
    <li class="mp_wire_ref">
        <?php esc_html_e( 'Wire Reference', 'dokan' ); ?>:
        <strong><?php echo esc_html( $wire_ref ); ?></strong>
    </li>
</ul>
