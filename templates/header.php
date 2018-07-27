<?php
/**
 * PDF Header template
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php the_title(); ?></title>
    <link rel="stylesheet" href="<?php echo PDFGEN_URLPATH ?>templates/css/pdf.css">
</head>
<body <?php body_class() ?>>