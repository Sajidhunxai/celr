<input type="text" name="vendor_name[]" value="<?php echo esc_attr($user_name); ?>" placeholder="Vendor Name" />
                <input type="text" name="vendor_price[]" placeholder="Vendor Price" />
                <select name="vendor_location[]">
                    <option value="">Select Location</option>
                    <?php foreach ($location_options as $option) : ?>
                        <option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="vendor_quantity[]">
                    <option value="">Select Quantity</option>
                    <?php foreach ($quantity_options as $option) : ?>
                        <option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="vendor_vintage[]">
                    <option value="">Select Vintage</option>
                    <?php foreach ($vintages_options as $option) : ?>
                        <option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="vendor_format[]">
                    <option value="">Select Formats</option>
                    <?php foreach ($formats_options as $option) : ?>
                        <option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="vendor_purchase[]" placeholder="Vendor Purchase Price" />
                <select name="vendor_tags[]">
                    <option value="">Select Tag</option>
                    <?php foreach ($product_tag_names as $tag) : ?>
                        <option value="<?php echo esc_attr($tag); ?>"><?php echo esc_html($tag); ?></option>
                    <?php endforeach; ?>
                </select>