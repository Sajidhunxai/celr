<?php

namespace DynamicContentForElementor;

trait Notices
{
    public static function notice($title = '', $content = '')
    {
        ?>
	<div class="elementor-alert elementor-alert-info" role="alert">
		<?php 
        if ($title) {
            ?>
			<span class="elementor-alert-title"><?php 
            echo wp_kses_post($title);
            ?></span>
		<?php 
        }
        if ($content) {
            ?>
			<span class="elementor-alert-description"><?php 
            echo wp_kses_post($content);
            ?></span>
		<?php 
        }
        ?>
	</div>
	<?php 
    }
}
