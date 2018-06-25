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
 * Version:     0.0.1
 * Author:      Jess Green
 * Author URI: https://www.jessgreen.io/
 * Text Domain: pdf-generator
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */
namespace JesGs\PDFGenerator;

use Mpdf\Mpdf;
use JesGs\PDFGenerator\Lib\Translatable;

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

require_once PDFGEN_ABSPATH . 'class.pdf-view.php';
require_once PDFGEN_ABSPATH . 'class.pdf-gen-install.php';
require_once PDFGEN_ABSPATH . 'lib/translatable.php';
require_once PDFGEN_ABSPATH . 'lib/template-functions.php';

$install = PdfGeneratorInstall::get_instance();
register_activation_hook(__FILE__, array($install, 'do_activate'));
register_deactivation_hook(__FILE__, array($install, 'do_deactivate'));


add_action('plugins_loaded', array(__NAMESPACE__ . '\Bootstrap', 'load_plugin'));

class Bootstrap
{
    const PDF_ENDPOINT = 'pdf';

    /**
     * @var \JesGs\PDFGenerator\Bootstrap
     */
    private static $instance = null;


    /**
     * Load the plugin
     */
    public static function load_plugin()
    {
        self::$instance = new self();
    }


    /**
     * Bootstrap constructor.
     */
    protected function __construct()
    {
        if (!class_exists('Mpdf\Mpdf')) {
            require_once PDFGEN_ABSPATH . 'vendor/autoload.php';
        } else {
            // check Mpdf version
            // admin notice about another version of Mpdf being installed
             if (version_compare(Mpdf::VERSION, '7.1.0', '>')) {
                 wp_die(Translatable::get('old_mpdf'));
             }
        }

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
        if (!in_array(get_query_var(self::PDF_ENDPOINT), ['view', 'download'])) {
            return $default_template;
        }

        $template = locate_template([
            self::PDF_ENDPOINT . '/' . basename($default_template),
        ], false, false);

        if (!$template) {
            $template = PDFGEN_ABSPATH . 'templates/single.php';
        }

        add_filter('body_class', function ($classes) {
            $classes[] = 'pdf-view';
            return $classes;
        });

        if (filter_input(INPUT_GET, 'test')) {
            return $template;
        }

        $mpdfView = PdfView::get_instance();
        $mpdfView->process_template_markup($template);

        return '';
    }
}