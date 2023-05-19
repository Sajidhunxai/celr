<?php

/**
 * Load Dokan Plugin when all plugins loaded
 *
 * @return \WeDevs\DokanPro\Modules\Elementor\Module|stdClass
 */
function dokan_elementor() {
    return dokan_pro()->module->elementor ?? new stdClass();
}
