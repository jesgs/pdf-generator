<?php
/**
 * Template functions for PDF generation
 */

if (!function_exists('get_pdf_header')) {
    /**
     * Looks for a header file specific to the pdf template
     * in the theme, otherwise defaults to the plugin template
     */
    function get_pdf_header()
    {
        require_once pdfgen__get_template('header.php');
    }
}

if (!function_exists('get_pdf_footer')) {
    /**
     * Looks for a footer file specific to the pdf template
     * in the theme, otherwise defaults to the plugin template
     */
    function get_pdf_footer()
    {
        require_once pdfgen__get_template('footer.php');
    }
}

if (!function_exists('get_pdf_permalink')) {
    function get_pdf_permalink(\WP_Post $post)
    {
        $permalink = get_permalink($post->ID);
    }
}


/**
 * Look for a template in the theme. If not found, use plugin's template
 * @param string $default_template
 *
 * @return string
 */
function pdfgen__get_template($default_template)
{
    $template = locate_template([
        \JesGs\PDFGenerator\Bootstrap::PDF_ENDPOINT . '/' . $default_template,
    ], false, false);

    if (!$template) {
        $template = PDFGEN_ABSPATH . 'templates/' . $default_template;
    }

    return $template;
}