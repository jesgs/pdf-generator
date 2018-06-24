<?php
namespace JesGs\PDFGenerator\Lib;

class Translatable
{

    /**
     * Array of translated strings
     * @var array
     */
    protected static $messages = [];


    /**
     * Get the translated message
     *
     * @param string $message
     *
     * @return string
     */
    public static function get($message)
    {
        if (empty(self::$messages)) {
            self::$messages = require PDFGEN_ABSPATH . 'lib/lang.php';
        }

        if (!isset(self::$messages[$message])) {
            return '';
        }

        return self::$messages[$message];
    }
}