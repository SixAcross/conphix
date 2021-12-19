#!/usr/bin/php
<?php
declare(strict_types=1);
namespace SixAcross\Confix\Tests;

use Symfony\Component\Yaml\Yaml;
use WpOrg\Requests\Requests;

require_once __DIR__ .'/../vendor/autoload.php';

$args = (array) $argv;

array_shift($args); #__FILE__
$confix_file = array_shift($args);

{

    if ( $confix_file === '-' ) { $confix_file = 'php://stdin'; }
    
    $confix = Yaml::parse( file_get_contents( $confix_file ) );
    $resources = $confix['resources'];

    foreach ( $resources as $resource_index => $resource ) {
      
        $response = Requests::get( $resource['uri'] );
        
        $content = json_decode( 
            $response->body,
            true,
            512,
            JSON_THROW_ON_ERROR
          );

        $confix['resources'][$resource_index]['content'] = $content;
        
        $extant = [
            'status'  => $response->status_code,
            'content' => $content,
            'headers' => $response->headers->getAll(),
          ];
        
        
    }
    
    echo Yaml::dump($confix);
}

