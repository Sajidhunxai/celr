<?php

namespace Devnet\PPH\Admin;

/**
 * Settings API wrapper class.
 *
 */

class Settings_API
{

    /**
     * settings sections array
     *
     * @var array
     */
    protected $settings_sections = [];

    /**
     * Settings fields array
     *
     * @var array
     */
    protected $settings_fields = [];

    /**
     * Set settings sections
     *
     * @param array   $sections setting sections array
     */
    public function set_sections($sections)
    {
        $this->settings_sections = $sections;

        return $this;
    }

    /**
     * Add a single section
     *
     * @param array   $section
     */
    public function add_section($section)
    {
        $this->settings_sections[] = $section;

        return $this;
    }

    /**
     * Set settings fields
     *
     * @param array   $fields settings fields array
     */
    public function set_fields($fields)
    {
        $this->settings_fields = $fields;

        return $this;
    }

    public function add_field($section, $field)
    {
        $defaults = [
            'name'  => '',
            'label' => '',
            'desc'  => '',
            'type'  => 'text'
        ];

        $arg = wp_parse_args($field, $defaults);
        $this->settings_fields[$section][] = $arg;

        return $this;
    }

    /**
     * Initialize and registers the settings sections and fields to WordPress
     *
     * Usually this should be called at `admin_init` hook.
     *
     * This function gets the initiated settings sections and fields. Then
     * registers them to WordPress and ready for use.
     */
    public function admin_init()
    {
        //register settings sections
        foreach ($this->settings_sections as $section) {
            if (false == get_option($section['id'])) {
                add_option($section['id']);
            }

            if (isset($section['desc']) && !empty($section['desc'])) {
                $section['desc'] = '<div class="inside">' . $section['desc'] . '</div>';
                $callback        = function () use ($section) {
                    $desc = str_replace('"', '\"', $section['desc']);
                    echo esc_html($desc);
                };
            } elseif (isset($section['callback'])) {
                $callback = $section['callback'];
            } else {
                $callback = null;
            }

            add_settings_section($section['id'], $section['title'], $callback, $section['id']);
        }

        //register settings fields
        foreach ($this->settings_fields as $section => $field) {
            foreach ($field as $option) {
                $name     = $option['name'];
                $type     = isset($option['type']) ? $option['type'] : 'text';
                $label    = isset($option['label']) ? $option['label'] : '';
                $callback = isset($option['callback']) ? $option['callback'] : [$this, 'callback_' . $type];

                $args = [
                    'id'                => $name,
                    'class'             => isset($option['class']) ? $option['class'] : $name,
                    'label_for'         => "{$section}[{$name}]",
                    'desc'              => isset($option['desc']) ? $option['desc'] : '',
                    'name'              => $label,
                    'section'           => $section,
                    'size'              => isset($option['size']) ? $option['size'] : null,
                    'options'           => isset($option['options']) ? $option['options'] : '',
                    'std'               => isset($option['default']) ? $option['default'] : '',
                    'sanitize_callback' => isset($option['sanitize_callback']) ? $option['sanitize_callback'] : '',
                    'type'              => $type,
                    'placeholder'       => isset($option['placeholder']) ? $option['placeholder'] : '',
                    'min'               => isset($option['min']) ? $option['min'] : '',
                    'max'               => isset($option['max']) ? $option['max'] : '',
                    'step'              => isset($option['step']) ? $option['step'] : '',
                    'multiple'          => isset($option['multiple']) ? $option['multiple'] : '',
                    'unit'              => isset($option['unit']) ? $option['unit'] : '',
                    'rows'              => isset($option['rows']) ? $option['rows'] : '',
                    'cols'              => isset($option['cols']) ? $option['cols'] : '',
                    'disabled'          => isset($option['disabled']) ? $option['disabled'] : '',
                    'pro'               => isset($option['pro']) ? $option['pro'] : '',
                ];

                add_settings_field("{$section}[{$name}]", $label, $callback, $section, $section, $args);
            }
        }

        // creates our settings in the options table
        foreach ($this->settings_sections as $section) {
            register_setting($section['id'], $section['id'], [$this, 'sanitize_options']);
        }
    }

    /**
     * Get field description for display
     *
     * @param array   $args settings field args
     */
    public function get_field_description($args)
    {
        if (!empty($args['desc'])) {
            $desc = sprintf('<p class="description">%s</p>', esc_html($args['desc']));
        } else {
            $desc = '';
        }

        return $desc;
    }

    /**
     * Displays subtitle or descriptio settings group
     *
     * @param array   $args settings field args
     */
    public function callback_info($args)
    {
        echo sprintf('<p class="info-description">%1$s</p>', wp_kses_post($args['desc']));
    }

    /**
     * Displays a text field for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_text($args)
    {
        $value       = $this->get_option($args['id'], $args['section'], $args['std']);
        $size        = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
        $type        = isset($args['type']) ? $args['type'] : 'text';
        $placeholder = empty($args['placeholder']) ? '' : ' placeholder="' . $args['placeholder'] . '"';
        $maxlength   = isset($args['max']) ? $args['max'] : '';
        $disabled    = isset($args['disabled']) && $args['disabled'] == true ? 'disabled' : '';

        printf(
            '<input type="%1$s" class="%2$s-text" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s" %6$s maxlength="%7$s" %8$s/>',
            esc_attr($type),
            esc_attr($size),
            esc_attr($args['section']),
            esc_attr($args['id']),
            esc_attr($value),
            esc_attr($placeholder),
            esc_attr($maxlength),
            esc_attr($disabled)
        );

        echo $this->get_field_description($args);
    }

    /**
     * Displays a url field for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_url($args)
    {
        $this->callback_text($args);
    }

    /**
     * Displays a number field for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_number($args)
    {
        $value        = $this->get_option($args['id'], $args['section'], $args['std']);
        $size         = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
        $type         = isset($args['type']) ? $args['type'] : 'number';
        $placeholder  = empty($args['placeholder']) ? '' : $args['placeholder'];
        $min          = empty($args['min']) ? '' : $args['min'];
        $max          = empty($args['max']) ? '' : $args['max'];
        $step         = empty($args['step']) ? '' : $args['step'];
        $has_unit_box = $args['unit'] ? 'has-unit-box' : '';

        printf(
            '<input type="%1$s" class="%2$s-number %10$s" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s" placeholder="%6$s" min="%7$s" max="%8$s" step="%9$s" />',

            esc_attr($type),
            esc_attr($size),
            esc_attr($args['section']),
            esc_attr($args['id']),
            esc_attr($value),
            esc_attr($placeholder),
            esc_attr($min),
            esc_attr($max),
            esc_attr($step),
            esc_attr($has_unit_box)
        );

        if ($has_unit_box) {
            echo '<div class="input-unit-box">' . esc_html($args['unit']) . '</div>';
        }

        echo $this->get_field_description($args);
    }

    /**
     * Displays a checkbox for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_checkbox($args)
    {
        $disabled = isset($args['disabled']) && $args['disabled'] == true ? 'disabled' : '';
        $value    = $this->get_option($args['id'], $args['section'], $args['std']);

        echo '<fieldset>';
        printf('<label for="dvnt-%1$s[%2$s]" class="switch">', esc_attr($args['section']), esc_attr($args['id']));
        printf('<input type="hidden" name="%1$s[%2$s]" value="0" />', esc_attr($args['section']), esc_attr($args['id']));
        printf(
            '<input type="checkbox" class="checkbox" id="dvnt-%1$s[%2$s]" name="%1$s[%2$s]" value="1" %3$s  %4$s />',
            esc_attr($args['section']),
            esc_attr($args['id']),
            checked(esc_attr($value), '1', false),
            esc_attr($disabled)
        );
        echo '<span class="slider round"></span>';
        echo '</label>';
        printf('<span class="description">%1$s</span>', esc_html($args['desc']));
        echo '</fieldset>';
    }

    /**
     * Displays a multicheckbox for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_multicheck($args)
    {

        $value = $this->get_option($args['id'], $args['section'], $args['std']);

        echo '<fieldset>';
        printf(
            '<input type="hidden" name="%1$s[%2$s]" value="" />',
            esc_attr($args['section']),
            esc_attr($args['id'])
        );
        foreach ($args['options'] as $key => $label) {
            $checked = isset($value[$key]) ? $value[$key] : '0';

            printf(
                '<label for="dvnt-%1$s[%2$s][%3$s]" class="switch">',
                esc_attr($args['section']),
                esc_attr($args['id']),
                esc_attr($key)
            );
            printf(
                '<input type="checkbox" class="checkbox" id="dvnt-%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%3$s" %4$s />',
                esc_attr($args['section']),
                esc_attr($args['id']),
                esc_attr($key),
                checked(esc_attr($checked), esc_attr($key), false)
            );
            echo '<span class="slider round"></span>';
            echo '</label>';
            printf('<span class="desc">%1$s</span><br>', esc_html($label));
        }

        echo $this->get_field_description($args);
        echo '</fieldset>';
    }

    /**
     * Displays a radio button for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_radio($args)
    {
        $value = $this->get_option($args['id'], $args['section'], $args['std']);

        echo '<fieldset>';

        foreach ($args['options'] as $key => $label) {
            echo sprintf(
                '<label for="dvnt-%1$s[%2$s][%3$s]">',
                esc_attr($args['section']),
                esc_attr($args['id']),
                esc_attr($key)
            );
            echo sprintf(
                '<input type="radio" class="radio" id="dvnt-%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s" %4$s />',
                esc_attr($args['section']),
                esc_attr($args['id']),
                esc_attr($key),
                checked(esc_attr($value), esc_attr($key), false)
            );
            echo sprintf('%1$s</label><br>', esc_html($label));
        }

        echo $this->get_field_description($args);
        echo '</fieldset>';
    }

    /**
     * Displays a radio images as buttons for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_radio_image($args)
    {
        $value = $this->get_option($args['id'], $args['section'], $args['std']);

        echo '<fieldset class="radio-images">';

        foreach ($args['options'] as $key => $label) {
            echo sprintf(
                '<label for="dvnt-%1$s[%2$s][%3$s]">',
                esc_attr($args['section']),
                esc_attr($args['id']),
                esc_attr($key)
            );
            echo sprintf(
                '<input type="radio" class="radio" id="dvnt-%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s" %4$s />',
                esc_attr($args['section']),
                esc_attr($args['id']),
                esc_attr($key),
                checked(esc_attr($value), esc_attr($key), false)
            );
            echo sprintf('<span class="img">%1$s</span></label><br>', esc_html($label));
        }

        echo $this->get_field_description($args);
        echo '</fieldset>';
    }

    /**
     * Displays a selectbox for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_select($args)
    {
        $value = $this->get_option($args['id'], $args['section'], $args['std']);
        $size  = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';

        printf(
            '<select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">',
            esc_attr($size),
            esc_attr($args['section']),
            esc_attr($args['id'])
        );

        foreach ($args['options'] as $key => $label) {

            $is_disabled = strpos($key, '_disabled') === 0 ? 'disabled' : '';

            printf(
                '<option value="%s" %s %s>%s</option>',
                esc_attr($key),
                esc_attr($is_disabled),
                selected(esc_attr($value), esc_attr($key), false),
                esc_attr($label)
            );
        }

        printf('</select>');
        echo $this->get_field_description($args);
    }

    /**
     * Displays a select2 selectbox for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_select2($args)
    {
        $size                = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
        $multiple            = isset($args['multiple']) && $args['multiple'] ? 'multiple' : '';
        $disabled            = isset($args['disabled']) && $args['disabled'] == true ? 'disabled' : '';
        $multiple_collection = $multiple ? '[]' : '';

        printf(
            '<select class="%1$s" name="%2$s[%3$s]%4$s" id="%2$s[%3$s]" %5$s %6$s>',
            esc_attr($size),
            esc_attr($args['section']),
            esc_attr($args['id']),
            esc_attr($multiple_collection),
            esc_attr($multiple),
            esc_attr($disabled)
        );


        foreach ($args['options'] as $key => $label) {

            // all options are always selected.
            printf('<option value="%s" selected>%s</option>', esc_attr($key), esc_html($label));
        }

        printf('</select>');
        echo $this->get_field_description($args);
    }

    /**
     * Displays a textarea for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_textarea($args)
    {
        $value       = $this->get_option($args['id'], $args['section'], $args['std']);
        $size        = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
        $rows        = isset($args['rows']) && !empty($args['rows']) ? $args['rows'] : 5;
        $cols        = isset($args['cols']) && !empty($args['cols']) ? $args['cols'] : 55;
        $placeholder = empty($args['placeholder']) ? '' : ' placeholder="' . $args['placeholder'] . '"';
        $disabled    = isset($args['disabled']) && $args['disabled'] == true ? 'disabled' : '';

        printf(
            '<textarea rows="%1$s" cols="%2$s" class="%3$s-text" id="%4$s[%5$s]" name="%4$s[%5$s]"%6$s %7$s>%8$s</textarea>',
            esc_attr($rows),
            esc_attr($cols),
            esc_attr($size),
            esc_attr($args['section']),
            esc_attr($args['id']),
            esc_attr($placeholder),
            esc_attr($disabled),
            esc_textarea($value)
        );

        echo $this->get_field_description($args);
    }

    /**
     * Displays the html for a settings field
     *
     * @param array   $args settings field args
     * @return string
     */
    public function callback_html($args)
    {
        echo $this->get_field_description($args);
    }

    /**
     * Displays a rich text textarea for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_wysiwyg($args)
    {
        $value = $this->get_option($args['id'], $args['section'], $args['std']);
        $size  = isset($args['size']) && !is_null($args['size']) ? $args['size'] : '500px';

        echo '<div style="max-width: ' . esc_attr($size) . ';">';

        $editor_settings = [
            'teeny'         => false,
            'textarea_name' => $args['section'] . '[' . $args['id'] . ']',
            'textarea_rows' => 10
        ];

        if (isset($args['options']) && is_array($args['options'])) {
            $editor_settings = array_merge($editor_settings, $args['options']);
        }

        wp_editor($value, $args['section'] . '-' . $args['id'], $editor_settings);

        echo '</div>';

        echo $this->get_field_description($args);
    }

    /**
     * Displays a file upload field for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_file($args)
    {
        $value = $this->get_option($args['id'], $args['section'], $args['std']);
        $size  = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
        $id    = $args['section'] . '[' . $args['id'] . ']';
        $label = isset($args['options']['button_label']) ? $args['options']['button_label'] : esc_html__('Choose File', 'product-price-history');

        echo sprintf(
            '<input type="text" class="%1$s-text dvnt-f-url" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>',
            esc_attr($size),
            esc_attr($args['section']),
            esc_attr($args['id']),
            esc_attr($value)
        );
        echo '<input type="button" class="button dvnt-f-browse" value="' . $label . '" />';
        echo $this->get_field_description($args);
    }

    /**
     * Displays a password field for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_password($args)
    {
        $value = $this->get_option($args['id'], $args['section'], $args['std']);
        $size  = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';

        echo sprintf(
            '<input type="password" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>',
            esc_attr($size),
            esc_attr($args['section']),
            esc_attr($args['id']),
            esc_attr($value)
        );

        echo $this->get_field_description($args);
    }

    /**
     * Displays a color picker field for a settings field
     *
     * @param array   $args settings field args
     */
    public function callback_color($args)
    {
        $value = $this->get_option($args['id'], $args['section'], $args['std']);
        $size  = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';

        echo sprintf(
            '<input type="text" class="%1$s-text wp-color-picker-field color-picker" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" data-default-color="%5$s" data-alpha-enabled="true" />',
            esc_attr($size),
            esc_attr($args['section']),
            esc_attr($args['id']),
            esc_attr($value),
            esc_attr($args['std'])
        );

        echo $this->get_field_description($args);
    }

    /**
     * Displays a select box for creating the pages select box
     *
     * @param array   $args settings field args
     */
    public function callback_pages($args)
    {
        $dropdown_args = [
            'selected' => esc_attr($this->get_option($args['id'], $args['section'], $args['std'])),
            'name'     => esc_attr($args['section'] . '[' . $args['id'] . ']'),
            'id'       => esc_attr($args['section'] . '[' . $args['id'] . ']'),
            'echo'     => 0
        ];

        echo wp_dropdown_pages($dropdown_args);
    }

    /**
     * Sanitize callback for Settings API
     *
     * @return mixed
     */
    public function sanitize_options($options)
    {
        if (!$options) {
            return $options;
        }

        foreach ($options as $option_slug => $option_value) {
            $sanitize_callback = $this->get_sanitize_callback($option_slug);

            // If callback is set, call it
            if ($sanitize_callback) {

                $options[$option_slug] = call_user_func($sanitize_callback, $option_value);
                continue;
            }
        }

        return $options;
    }

    /**
     * Get sanitization callback for given option slug
     *
     * @param string $slug option slug
     *
     * @return mixed string or bool false
     */
    public function get_sanitize_callback($slug = '')
    {
        if (empty($slug)) {
            return false;
        }

        // Iterate over registered fields and see if we can find proper callback
        foreach ($this->settings_fields as $section => $options) {
            foreach ($options as $option) {
                if ($option['name'] != $slug) {
                    continue;
                }

                // Return the callback name
                return isset($option['sanitize_callback']) && is_callable($option['sanitize_callback']) ? $option['sanitize_callback'] : false;
            }
        }

        return false;
    }

    /**
     * Get the value of a settings field
     *
     * @param string  $option  settings field name
     * @param string  $section the section name this field belongs to
     * @param string  $default default text if it's not found
     * @return string
     */
    public function get_option($option, $section, $default = '')
    {

        $options = get_option($section);

        if (isset($options[$option])) {
            return $options[$option];
        }

        return $default;
    }

    /**
     * Show navigations as tab
     *
     * Shows all the settings section labels as tab
     */
    public function show_navigation()
    {
        echo '<h2 class="nav-tab-wrapper">';

        $count = count($this->settings_sections);

        // don't show the navigation if only one section exists
        if ($count === 1) {
            return;
        }

        foreach ($this->settings_sections as $tab) {
            echo sprintf('<a href="#%1$s" class="nav-tab" id="%1$s-tab">%2$s</a>', esc_attr($tab['id']), esc_html($tab['title']));
        }

        echo '</h2>';
    }

    /**
     * Show the section settings forms
     *
     * This function displays every sections in a different form
     */
    public function show_forms()
    {
?>
        <div class="metabox-holder">
            <?php foreach ($this->settings_sections as $form) { ?>
                <div id="<?php echo esc_attr($form['id']); ?>" class="group" style="display: none;">
                    <form method="post" action="options.php">
                        <?php

                        do_action('devnet_pph_form_top', $form);
                        do_action('devnet_pph_form_top_' . $form['id'], $form);

                        settings_fields($form['id']);
                        do_settings_sections($form['id']);

                        do_action('devnet_pph_form_bottom_' . $form['id'], $form);
                        do_action('devnet_pph_form_bottom', $form);

                        if (isset($this->settings_fields[$form['id']])) :
                        ?>
                            <div class="submit-row" style="padding-left: 10px">
                                <?php submit_button(); ?>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            <?php } ?>
        </div>
    <?php
        $this->script();
    }

    /**
     * Tabbable JavaScript codes & Initiate Color Picker
     *
     * This code uses localstorage for displaying active tabs
     */
    public function script()
    {
    ?>
        <script>
            jQuery(document).ready(function($) {
                //Initiate Color Picker
                $('.wp-color-picker-field').wpColorPicker();

                // Switches option sections
                $('.group').hide();
                var activetab = '';
                if (typeof(localStorage) != 'undefined') {
                    activetab = localStorage.getItem("activetab");
                }

                //if url has section id as hash then set it as active or override the current local storage value
                if (window.location.hash) {
                    activetab = window.location.hash;
                    if (typeof(localStorage) != 'undefined') {
                        localStorage.setItem("activetab", activetab);
                    }
                }

                if (activetab != '' && $(activetab).length) {
                    $(activetab).fadeIn();
                } else {
                    $('.group:first').fadeIn();
                }
                $('.group .collapsed').each(function() {
                    $(this).find('input:checked').parent().parent().parent().nextAll().each(
                        function() {
                            if ($(this).hasClass('last')) {
                                $(this).removeClass('hidden');
                                return false;
                            }
                            $(this).filter('.hidden').removeClass('hidden');
                        });
                });

                if (activetab != '' && $(activetab + '-tab').length) {
                    $(activetab + '-tab').addClass('nav-tab-active');
                } else {
                    $('.nav-tab-wrapper a:first').addClass('nav-tab-active');
                }
                $('.nav-tab-wrapper a').click(function(evt) {
                    $('.nav-tab-wrapper a').removeClass('nav-tab-active');
                    $(this).addClass('nav-tab-active').blur();
                    var clicked_group = $(this).attr('href');
                    if (typeof(localStorage) != 'undefined') {
                        localStorage.setItem("activetab", $(this).attr('href'));
                    }
                    $('.group').hide();
                    $(clicked_group).fadeIn();
                    evt.preventDefault();
                });

                $('.dvnt-f-browse').on('click', function(event) {
                    event.preventDefault();

                    var self = $(this);

                    // Create the media frame.
                    var file_frame = wp.media.frames.file_frame = wp.media({
                        title: self.data('uploader_title'),
                        button: {
                            text: self.data('uploader_button_text'),
                        },
                        multiple: false
                    });

                    file_frame.on('select', function() {
                        attachment = file_frame.state().get('selection').first().toJSON();
                        self.prev('.dvnt-f-url').val(attachment.url).change();
                    });

                    // Finally, open the modal
                    file_frame.open();
                });
            });
        </script>
<?php
    }
}
