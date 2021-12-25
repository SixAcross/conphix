<?php
declare(strict_types=1);
namespace SixAcross\Confix\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use WpOrg\Requests\Requests;


class Pull extends Intent
{
    protected static $defaultName = 'pull';
    

    protected function configure()
    {
        $this
            ->setDescription(
                'Fetch extant state of resources and write it back to resources in intent. '
              )
            ->addOption(
                'all-values',
                null,
                null, 
                'Write all extant values to intent resources, instead of just the values already in intent. ',
              );
              
        return parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options['--all-values'] = $input->getOption('all-values');
        $intent_file = $input->getArgument('intent_file');
    
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
                
                if ( is_array( $extant_array[$key] ?? null ) and 
                    (   
                        is_array( $intent_array[$key] ?? null ) or
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
          
            $response = Requests::get( $resource['url'] );
            
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
        
        return 0;

    }
}
