#!/usr/bin/php
<?php
declare(strict_types=1);
namespace SixAcross\Confix\Tests;

use Symfony\Component\Yaml\Yaml;
use WpOrg\Requests\Requests;

require_once __DIR__ .'/../vendor/autoload.php';

$args = (array) $argv;

array_shift($args); #__FILE__

$options['--all-values'] = false;
if ( reset($args) === '--all-values' ) {
    array_shift($args);
    $options['--all-values'] = true;
}   

$intent_file = array_shift($args);

{

    if ( $intent_file === '-' ) { $intent_file = 'php://stdin'; }
    
    $intent = Yaml::parse( file_get_contents( $intent_file ) );
    $resources = $intent['resources'];

    $recurse = function( array $extant_array, array &$intent_array ) use ( &$recurse, $options ) {

        if ( $options['--all-values'] ) { 
            $keys = array_keys( $extant_array );
        } else {
            $keys = array_keys( $intent_array );
        }
        
        foreach( $keys as $key ) {
          
            if ( is_array($extant_array[$key] ?? null) and 
                (   
                    is_array($intent_array[$key]) or
                    $options['--all-values']
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
        
        $recurse( $extant, $intent['resources'][$resource_index] );
        
    }
    
    echo Yaml::dump($intent);
}

