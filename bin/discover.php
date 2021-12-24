#!/usr/bin/php
<?php
declare(strict_types=1);
namespace SixAcross\Confix\Tests;

use Symfony\Component\Yaml\Yaml;
use WpOrg\Requests\Requests;
use JmesPath;


require_once __DIR__ .'/../vendor/autoload.php';

$args = (array) $argv;

array_shift($args); #__FILE__

$intent_file = array_shift($args);

{
    if ( $intent_file === '-' ) { $intent_file = 'php://stdin'; }
    
    $intent = Yaml::parse( file_get_contents( $intent_file ) );

    $existing_resource_urls = array_column( $intent['resources'] ?? [], 'url' );
    
    foreach ( $intent['resources'] as $resource ) {
        
        $jmespaths = (array) ( $resource['resource_urls'] ?? null );

        if ( empty( $jmespaths ) ) { continue; }
          
        $response = Requests::get( $resource['url'] );

        $content = json_decode( 
            $response->body,
            true,
            512,
            JSON_THROW_ON_ERROR
          );

        foreach ( $jmespaths as $jmespath ) {
          
            $discovered_urls = JmesPath\search( $jmespath, $content );
            
            foreach ( $discovered_urls as $discovered_url ) {
                if ( ! in_array( $discovered_url, $existing_resource_urls ) ) {
                    $intent['resources'][] = [ 'url' => $discovered_url, 'status' => null, 'content' => null, ];
                }
            }
            
        }
    
    }
    
    echo Yaml::dump( $intent, 99 );
    
}
