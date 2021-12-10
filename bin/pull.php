#!/usr/bin/php
<?php
declare(strict_types=1);
namespace SixAcross\Confix\Tests;

use Symfony\Component\Yaml\Yaml;

require_once __DIR__ .'/../vendor/autoload.php';

$args = (array) $argv;

array_shift($args); #__FILE__
$confix_file = array_shift($args);

{

    $confix = Yaml::parse( file_get_contents( $confix_file ) );
    $resources = $confix['resources'];

    foreach ( $resources as $resource_index => $resource ) {
        $content = json_decode( 
            file_get_contents( $resource['uri'] ),
            true,
            512,
            JSON_THROW_ON_ERROR
          );

        $confix['resources'][$resource_index]['content'] = $content;
    }
    
    echo Yaml::dump($confix);
}

