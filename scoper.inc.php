<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

return [
    // The prefix configuration. If a non null value will be used, a random prefix will be generated.
    'prefix' => 'JesGs\\PDFGenerator',
    // For more see: https://github.com/humbug/php-scoper#finders-and-paths
    'finders' => [
        Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->notName('/LICENSE|.*\\.md|.*\\.dist|Makefile|composer\\.json|composer\\.lock/')
            ->exclude([
                'doc',
                'test',
                'test_old',
                'tests',
                'Tests',
                'vendor-bin',
            ])
            ->in('vendor/mpdf')
            ->in('vendor/myclabs')
            ->in('vendor/paragonie')
            ->in('vendor/psr')
            ->in('vendor/setasign')
            ->name('*.php')
    ],

    'whitelist-global-constants' => true,
    'whitelist-global-classes'   => true,
    'whitelist-global-functions' => true,

    // For more see: https://github.com/humbug/php-scoper#patchers
    'patchers' => [
        function (string $filePath, string $prefix, string $contents): string {
            // Change the contents here.
	        if ( false !== strpos( $filePath, 'vendor/mpdf/mpdf/src/FpdiTrait.php' ) ) {
		        $find    = 'use \setasign\Fpdi\FpdiTrait';
		        $replace = 'use \JesGs\PDFGenerator\setasign\Fpdi\FpdiTrait';
		        return str_replace( $find, $replace, $contents );
	        }

            return $contents;
        },
    ],
    // Fore more see https://github.com/humbug/php-scoper#whitelist
    'whitelist' => [
        'WP*',
        '\WP*',
        '\WP_REST_*',
        'JesGS*',
        '\JesGS*',
    ],
];
