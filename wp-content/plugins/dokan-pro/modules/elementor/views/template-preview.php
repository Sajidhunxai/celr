<?php
if ( empty( $_GET['id'] ) ) {
    exit;
}

$id = $_GET['id'];
?>
<img style="width: 100%;" src="<?php echo DOKAN_ELEMENTOR_ASSETS . '/images/screenshots/' . $id . '.png' ?>" alt="">
<?php exit;
