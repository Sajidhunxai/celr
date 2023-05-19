<div id="dokan-mangopay-bank-account-list-notice" class="dokan-alert hidden"></div>
<?php if ( ! empty( $bank_accounts ) ) : ?>
<table class="bank-account-list list-table">
    <thead>
        <tr>
            <td><?php esc_html_e( 'Account Type', 'dokan' ); ?></td>
            <td><?php esc_html_e( 'Account No / IBAN', 'dokan' ); ?></td>
            <td class="bank-account-status"><?php esc_html_e( 'Status', 'dokan' ); ?></td>
        </tr>
    </thead>

    <tbody>
        <?php foreach ( $bank_accounts as $bank_account ) : ?>
            <?php if ( $bank_account->Active ) : ?>
                <tr>
                    <td><?php esc_html_e( $bank_account->Type, 'dokan' ); ?></td>
                    <td><?php esc_html_e( 'IBAN' !== $bank_account->Type ? $bank_account->Details->AccountNumber : $bank_account->Details->IBAN, 'dokan' ); ?></td>
                    <?php if ( $bank_account->Id === $active_account ) : ?>
                    <td class="bank-account-status">
                        <span class="active"><?php esc_html_e( 'Active', 'dokan' ); ?></span>
                    </td>
                    <?php else : ?>
                    <td class="bank-account-status">
                        <button type="button"
                            id="dokan-mp-active-bank-account-<?php echo esc_attr( $bank_account->Id ); ?>"
                            data-bank-account="<?php echo esc_attr( $bank_account->Id ); ?>"
                            data-user="<?php echo esc_attr( $user_id ); ?>">
                            <?php esc_html_e( 'Make Active', 'dokan' ); ?>
                        </button>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else : ?>
<div style="margin: 15px 0;">
    <?php esc_html_e( 'No bank account found. Please create one to get automatic payouts.', 'dokan' ) ?>
</div>
<?php endif; ?>