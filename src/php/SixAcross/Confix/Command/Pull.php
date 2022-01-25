<?php
declare(strict_types=1);
namespace SixAcross\Confix\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SixAcross\Yaml\Unaliased as Yaml;
use WpOrg\Requests\Requests;


class Pull extends Intent
{
    protected static $defaultName = 'pull';
    protected InputInterface $input;
    

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
        $this->input = $input;
        $intent_file = $input->getArgument('intent_file');
    
        if ( $intent_file === '-' ) { $intent_file = 'php://stdin'; }
        
        $intent = Yaml::parse( file_get_contents( $intent_file ) );
        $resources = $intent['resources'];

        foreach ( $resources as $resource_index => $resource ) {
          
            $response = Requests::get( $resource['url'] );
            
            $content = json_decode( 
                $response->body,
                true,
                512,
                JSON_THROW_ON_ERROR
              );

            $extant = [
                'url'         => $response->url,
                'status_code' => $response->status_code,
                'content'     => $content,
                'headers'     => $response->headers->getAll(),
              ];
            
            $this->pullRecursively( $extant, $intent['resources'][$resource_index] );
            
        }
        
        unset( $this->input );
        
        $output->write( Yaml::dump($intent) );
        
        return 0;

    }
    
    protected function pullRecursively( array $extant_array, array &$intent_array ) 
    {
        $keys = array_unique( array_merge( 
            array_keys( $intent_array), 
            array_keys( $extant_array ),
          ) );
        
        foreach ( $keys as $key ) {
            
            // recurse if both intent and extant are compound values
            if ( 
                is_array( $extant_array[$key] ?? null ) and 
                is_array( $intent_array[$key] ?? null )
              ) 
            {
                $this->pullRecursively( $extant_array[$key], $intent_array[$key] );
            
            
            // update values where they exist in intent, or when passed the proper option 
            } elseif ( 
                array_key_exists( $key, $intent_array ) or 
                $this->input->getOption('all-values')
              ) 
            {
                $intent_array[$key] = $extant_array[$key] ?? null;
            
                
            // delete values missing from intent when passed the proper option 
            } elseif ( $this->input->getOption('all-values') ) {
                unset( $intent_array[$key] );
            }
        }
    }
        
}
