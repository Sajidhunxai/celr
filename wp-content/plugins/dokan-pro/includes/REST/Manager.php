<?php

namespace WeDevs\DokanPro\REST;

class Manager {

    /**
     * Register Dokan Pro REST Controllers
     *
     * @since 3.0.0
     *
     * @param array $class_map
     *
     * @return array
     */
    public static function register_rest_routes( $class_map ) {
        $class_map[ DOKAN_PRO_INC . '/REST/StoreCategoryController.php' ]         = StoreCategoryController::class;
        $class_map[ DOKAN_PRO_INC . '/REST/CouponController.php' ]                = CouponController::class;
        $class_map[ DOKAN_PRO_INC . '/REST/ReportsController.php' ]               = ReportsController::class;
        $class_map[ DOKAN_PRO_INC . '/REST/ReviewsController.php' ]               = ReviewsController::class;
        $class_map[ DOKAN_PRO_INC . '/REST/ProductVariationController.php' ]      = ProductVariationController::class;
        $class_map[ DOKAN_PRO_INC . '/REST/StoreController.php' ]                 = StoreController::class;
        $class_map[ DOKAN_PRO_INC . '/REST/ModulesController.php' ]               = ModulesController::class;
        $class_map[ DOKAN_PRO_INC . '/REST/AnnouncementController.php' ]          = AnnouncementController::class;
        $class_map[ DOKAN_PRO_INC . '/REST/LogsController.php' ]                  = LogsController::class;
        $class_map[ DOKAN_PRO_INC . '/REST/RefundController.php' ]                = RefundController::class;
        $class_map[ DOKAN_PRO_INC . '/REST/ChangeLogController.php' ]             = ChangeLogController::class;
        $class_map[ DOKAN_PRO_INC . '/REST/DashboardController.php' ]             = DashboardController::class;
        $class_map[ DOKAN_PRO_INC . '/REST/ProductController.php' ]               = ProductController::class;
        if ( class_exists( 'WeDevs\Dokan\REST\ProductBlockController' ) ) {
            $class_map[ DOKAN_PRO_INC . '/REST/ProductVariationBlockController.php' ] = ProductVariationBlockController::class;
        }

        return $class_map;
    }
}
