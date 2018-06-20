<?php
/**
 * PDF Generator
 *
 * @package     PDFGenerator
 * @author      Jess Green
 * @copyright   2018 Jess Green
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: PDF Generator
 * Plugin URI: https://www.jessgreen.io/projects/pdf-generator
 * Description: A template-based PDF generator
 * Version:     1.0.0
 * Author:      Jess Green
 * Author URI: https://www.jessgreen.io/
 * Text Domain: pdf-generator
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */
namespace JesGs\PDFGenerator;

use \Mpdf\Mpdf as Mpdf;
use \Mpdf\MpdfException;
use \Mpdf\Output\Destination;

if (!defined('ABSPATH')) exit;

$plugin_folder = plugin_basename(dirname(__FILE__));

if (!defined('PDFGEN_DOMAIN')) {
    define('PDFGEN_DOMAIN', $plugin_folder);
}

if (!defined('PDFGEN_ABSPATH')) {
    define('PDFGEN_ABSPATH', plugin_dir_path(__FILE__));
}
if (!defined('PDFGEN_URLPATH')) {
    define('PDFGEN_URLPATH', plugin_dir_url(__FILE__));
}

if (!defined('PDFGEN_LANG')) {
    define('PDFGEN_LANG', $plugin_folder . '/lang');
}

require PDFGEN_ABSPATH . 'vendor/autoload.php';

add_action('plugins_loaded', array(__NAMESPACE__ . '\Bootstrap', 'load_plugin'));

class Bootstrap
{
    const PDF_ENDPOINT = 'pdf';

    /**
     * @var \JesGs\PDFGenerator\Bootstrap
     */
    private static $instance = null;

    /**
     * @var \Mpdf\Mpdf
     */
    private static $mpdf = null;


    /**
     * Load the plugin
     */
    public static function load_plugin()
    {
        self::$instance = new self();
    }


    /**
     * @return \Mpdf\Mpdf
     */
    public static function get_mpdf_instance()
    {
        if (self::$mpdf == null) {
            try {
                $upload_dir = wp_upload_dir(false);
                self::$mpdf = new Mpdf([
                    'tempDir' => $upload_dir['basedir'] . '/pdftmp'
                ]);
            } catch (MpdfException $exception) {
                error_log($exception->getMessage());
            }
        }

        return self::$mpdf;
    }


    /**
     * Bootstrap constructor.
     */
    protected function __construct()
    {
        add_action('init', array($this, 'init'), 999);
        add_filter('query_vars', array($this, 'query_vars'));
        add_filter('template_include', array($this, 'template_include'));
    }


    /**
     * Run init functionality
     *
     * @see init() hook
     * @return void
     */
    public function init()
    {
        /* add `pdf` endpoint */
        add_rewrite_endpoint( self::PDF_ENDPOINT, EP_PERMALINK | EP_PAGES );
    }


    /**
     * Add a new query var
     * @param array $vars Array of query vars
     *
     * @return array
     */
    public function query_vars($vars)
    {
        $vars[] = self::PDF_ENDPOINT;
        return $vars;
    }

    /**
     * @param $default_template
     *
     * @return string|
     */
    public function template_include($default_template)
    {
        if (!get_query_var(self::PDF_ENDPOINT)) {
            return $default_template;
        }

        $template = locate_template(array(
            self::PDF_ENDPOINT . '/' . basename($default_template),
        ));

        if (!$template) {
            $template = PDFGEN_ABSPATH . 'templates/' . basename($default_template);
        }

        add_filter('body_class', function ($classes) {
            $classes[] = 'pdf-view';
            return $classes;
        });

        if (filter_input(INPUT_GET, 'test')) {
            return $template;
        }

        ob_start();
        require_once $template;
        $contents = ob_get_contents();
        ob_end_clean();

        /**
         * Pre PDF-generation filtering for HTML doc
         *
         * @since 0.1.0
         *
         * @param string $contents HTML content to be filtered
         * @return string
         */
        $contents = apply_filters('jesgs_pdf_pre_filter_contents', $contents);

        $filename = sanitize_file_name(get_query_var('pdf'));
        $this->create_pdf($contents, $filename);
        return true;
    }

    /**
     * Check query vars to determine if output should go to a file or displayed in browser
     *
     * @return bool
     */
    private function do_download()
    {
        return get_query_var(self::PDF_ENDPOINT) && filter_input(INPUT_GET, self::PDF_ENDPOINT);
    }

    /**
     * Create the PDF and then display it
     *
     * @param string $contents
     * @param string $filename
     */
    private function create_pdf($contents, $filename)
    {
        $destination = $this->do_download() ? Destination::DOWNLOAD : Destination::INLINE;

        $mpdf = Bootstrap::get_mpdf_instance();
        $mpdf->debug = WP_DEBUG;
        $mpdf->showImageErrors = true;
        $mpdf->CSSselectMedia = apply_filters('jesgs_pdf_css_media', 'screen'); // allow this to be overridden
        $mpdf->dpi = 96;
        try {
            $mpdf->WriteHTML($contents, 2);
            $mpdf->Output($filename, $destination);
        } catch (MpdfException $exception) {
            error_log($exception->getMessage());
        }
        exit();
    }
}