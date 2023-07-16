<?php 
function render_warehouse_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    
    // Process the form submission
    if ( isset( $_POST['submit'] ) ) {
        save_warehouse_data();
    }

    // Display the warehouse form
    ?>
    <div class="wrap">
        <h1>Warehouse Settings</h1>
        <form method="post">
            <label for="location_name">Location Name:</label>
            <select id="location_name" name="location_name" required>
                <?php
                $attribute_slug = 'pa_location'; // Adjust the attribute slug according to your setup
                $terms = get_terms( array(
                    'taxonomy' => $attribute_slug,
                    'hide_empty' => false,
                ) );

                foreach ( $terms as $term ) {
                    echo '<option value="' . esc_attr( $term->name ) . '">' . esc_html( $term->name ) . '</option>';
                }
                ?>
            </select>
            <br>
            <label for="warehouse_fee">Warehouse Fee:</label>
            <input type="number" id="warehouse_fee" name="warehouse_fee" step="0.01" required>
            <br>
            <input type="submit" name="submit" value="Save">
        </form>

        <h2>Submitted Warehouse Data</h2>
        <table class="wp-list-table widefat striped">
            <thead>
                <tr>
                    <th>Location Name</th>
                    <th>Warehouse Fee</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $existing_warehouses = get_option( 'custom_warehouses', array() );

                foreach ( $existing_warehouses as $location_name => $warehouse_fee ) {
                    echo '<tr>';
                    echo '<td>' . esc_html( $location_name ) . '</td>';
                    echo '<td>' . esc_html( $warehouse_fee ) . '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}


// Save the warehouse data to the database
function save_warehouse_data() {
    $location_name = sanitize_text_field( $_POST['location_name'] );
    $warehouse_fee = floatval( $_POST['warehouse_fee'] );

    // Save the warehouse data to the database (you can modify this to fit your needs)
    $existing_warehouses = get_option( 'custom_warehouses', array() );
    $existing_warehouses[ $location_name ] = $warehouse_fee;

    update_option( 'custom_warehouses', $existing_warehouses );
}

?>