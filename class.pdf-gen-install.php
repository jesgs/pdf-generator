<?php
namespace JesGs\PDFGenerator;


use JesGs\PDFGenerator\Lib\Translatable;

class PdfGeneratorInstall
{


    /**
     * Current plugin DB version
     *
     * @var string
     */
    protected static $version;


    /**
     * What type is the object? Activation, deactivation or upgrade?
     *
     * @var string
     */
    protected $type;


    /**
     * Instance of Bootstrap class
     * @var \JesGs\PDFGenerator\Bootstrap
     */
    protected $bootstrap;


    /**
     * Instance of PdfGeneratorInstall
     * @var \JesGs\PDFGenerator\PdfGeneratorInstall
     */
    protected static $instance;


    /**
     * Get instance of
     *
     * @return PdfGeneratorInstall
     */
    public static function get_instance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    /**
     * Static function for plugin activation.
     *
     * @return void
     */
    public function do_activate()
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $this->activation_checks();

        $this->after_plugin_activation();
        flush_rewrite_rules(false);
    }



    /**
     * Activation checks
     */
    private function activation_checks()
    {
        global $wp_version;

        // Check for capability
        if ( !current_user_can('activate_plugins') ){
            wp_die( __('Sorry, you do not have sufficient permissions to activate this plugin.', PDFGEN_DOMAIN) );
        }

        // Get the capabilities for the administrator
        $role = get_role('administrator');

        // Must have admin privileges in order to activate.
        if ( empty($role) ) {
            wp_die( __('Sorry, you must be an Administrator in order to use PdfGenerator', PDFGEN_DOMAIN) );
        }

        if ( version_compare ($wp_version, '5.5', '<=')) {
            wp_die(
                'Sorry, only WordPress 5.5 and later are supported.'
                . ' Please upgrade to WordPress 5.5', 'Wrong Version'
            );
        }
    }


    /**
     * Run routines after plugin has been activated
     *
     * @return void
     */
    public function after_plugin_activation()
    {
        /**
         * jesgs_pdf_gen__after_plugin_activation
         * Allow other plugins to add to Pdf Generator's activation sequence.
         *
         * @return void
         */
        do_action('jesgs_pdf_gen__after_plugin_activation');
    }


    /**
     * Static function for plugin deactivation.
     *
     * @return void
     */
    public function do_deactivate()
    {
        delete_option('rewrite_rules');
        flush_rewrite_rules(false);
    }

    /**
     * Static function for upgrade
     *
     * @return void
     */
    public function do_upgrade()
    {
        flush_rewrite_rules(false);
    }
}