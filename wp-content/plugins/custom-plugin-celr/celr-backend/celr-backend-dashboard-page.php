<?php
function custom_plugin_dashboard_page() {
    // Outputting the welcome message
    echo '<h1>Welcome to the Multi-Vendor Dashboard!</h1>';

    // Additional content
    echo '<p>This is the main dashboard for the Single Product Multi-Vendor Plugin.</p>';

    // Styling
    echo '<style>';
    echo 'h1 {';
    echo '  font-size: 24px;';
    echo '  font-weight: bold;';
    echo '  color: #333;';
    echo '}';
    echo 'p {';
    echo '  font-size: 16px;';
    echo '  color: #666;';
    echo '}';
    echo '</style>';
}
?>