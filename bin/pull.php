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

    $recurse = function( array $extant_array, array &$intent_array ) use ( &$recurse, $options ) {

        $keys = array_keys( $intent_array );
        
        foreach( $keys as $key ) {
          
            if ( is_array($extant_array[$key] ?? null) and 
                (   
                    is_array($intent_array[$key]) 
                  )
              ) {
                return $recurse( $extant_array[$key], $intent_array[$key] );
                
            } elseif ( array_key_exists( $key, $extant_array ) ) {
                $intent_array[$key] = $extant_array[$key] ?? null;
            }
            
        }
    };

    foreach ( $resources as $resource_index => $resource ) {
      
        $response = Requests::get( $resource['uri'] );
        
        $content = json_decode( 
            $response->body,
            true,
            512,
            JSON_THROW_ON_ERROR
          );
        
        $extant = [
            'status'  => $response->status_code,
            'content' => $content,
            'headers' => $response->headers->getAll(),
          ];
        
        $recurse( $extant, $confix['resources'][$resource_index] );
        
    }
    
    echo Yaml::dump($confix);
}

