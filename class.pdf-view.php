<?php
namespace JesGs\PDFGenerator;


use JesGs\PDFGenerator\Mpdf\Mpdf as Mpdf;
use JesGs\PDFGenerator\Mpdf\MpdfException as MpdfException;
use JesGs\PDFGenerator\Mpdf\Output\Destination as Destination;

class PdfView
{
    /**
     * @var PdfView
     */
    private static $instance = null;

    /**
     * @var Mpdf
     */
    private static $mpdf = null;

    /**
     * Get instance of object
     * @return PdfView
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    /**
     * Get an instance of Mpdf
     * @return Mpdf
     */
    public static function get_mpdf_instance()
    {
        if (self::$mpdf == null) {
            try {
                self::$mpdf = new Mpdf([
                    'tempDir' => get_temp_dir() . '/pdftmp'
                ]);
            } catch (MpdfException $exception) {
	            error_log($exception->getMessage());
            }
        }

	    return self::$mpdf;
    }

    /**
     * Check query vars to determine if output should go to a file or displayed in browser
     *
     * @return bool
     */
    private function do_download()
    {
        return (get_query_var(Bootstrap::PDF_ENDPOINT) === 'download' );
    }

    /**
     * @param string $template Template filename to load
     */
    public function process_template_markup($template)
    {
        /**
         * Load the template, and allow The Loop to run
         * inside output buffering
         */
        ob_start();
        require_once $template;
        $contents = ob_get_contents();
        ob_end_clean();

        /**
         * Pre PDF-generation filtering for HTML doc
         *
         * @since 0.0.1
         *
         * @param string $contents HTML content to be filtered
         * @return string
         */
        $contents = apply_filters('jesgs_pdf_pre_filter_contents', $contents);

        // use post or page-name as filename
        if (get_post_type() == 'page') {
            $filename = get_query_var('pagename');
        } else {
            $filename = get_query_var('name');
        }

        $filename = sanitize_file_name($filename) . '.pdf';
        $this->create_pdf($contents, $filename);
    }

    /**
     * Create the PDF and then display it
     *
     * @param string $contents
     * @param string $filename Optional filename. Required if downloading
     * @param array $args Optional array of arguments to pass
     */
    public function create_pdf($contents, $filename = '', $args = [])
    {
        $destination = $this->do_download() ? Destination::DOWNLOAD : Destination::INLINE;

        $mpdf = self::get_mpdf_instance();
        $mpdf->debug = WP_DEBUG;
        $mpdf->showImageErrors = true;
        $mpdf->CSSselectMedia = apply_filters('jesgs_pdf_css_media', 'print'); // allow this to be overridden
        $mpdf->dpi = 96;
        try {
            $mpdf->WriteHTML($contents, 0);
            $mpdf->Output($filename, $destination);
        } catch (MpdfException $exception) {
            error_log($exception->getMessage());
        }
        exit();
    }
}