<?php
declare(strict_types=1);
namespace SixAcross\Confix\Tests;

use Symfony\Component\Yaml\Yaml;


it( 'accepts intent input on stdin when passed a dash as the input file argument. ', 
    function() {
        [ $stdout, $stderr, $exit_code ] = executeCommand(
            __DIR__ .'/../../../bin/discover.php - ',
            file_get_contents( __DIR__ .'/../../examples/people.intent.yml' ),
          );
        
        expect($exit_code)->toBe(0);
    }
  );
    
it( 'produces intent output on stdout when passed a dash as the input file argument. ', 
    function() {
        [ $stdout, $stderr, $exit_code ] = executeCommand( 
            __DIR__ .'/../../../bin/discover.php - ',
            file_get_contents( __DIR__ .'/../../examples/people.intent.yml' ),
          );
        
        expect($exit_code)->toBe(0);
        expect($stderr   )->toBe('');
        $this->assertMatchesYamlSnapshot( Yaml::parse($stdout) );
    }
  );
  
it( 'produces non-intent output on stderr (only) when passed a dash as the input file argument. ',
    function() {
        [ $stdout, $stderr, $exit_code ] = executeCommand( 
            __DIR__ .'/../../../bin/discover.php - ',
            "non-yaml garbage",
          );
        
        expect($exit_code )->toBe(255);
        expect($stdout    )->toBe('');
        expect($stderr    )->not()->toBeEmpty();
    }
  );


it( 'writes newly discovered resources to intent. ', 
    function() {
        [ $stdout, $stderr, $exit_code ] = executeCommand( 
            __DIR__ .'/../../../bin/discover.php - ',
            file_get_contents( __DIR__ .'/../../examples/people.intent.yml' ),
          );
        expect($exit_code)->toBe(0);
        expect($stderr   )->toBe('');
        $this->assertMatchesYamlSnapshot( Yaml::parse($stdout) );
    }
  );
  
it( 'does not write discovered resources to intent that already exist there. ',
    function() {
        [ $stdout, $stderr, $exit_code ] = executeCommand( 
            __DIR__ .'/../../../bin/discover.php - ',
            file_get_contents( __DIR__ .'/../../examples/people.some.intent.yml' ),
          );
        expect($exit_code)->toBe(0);
        expect($stderr   )->toBe('');
        $this->assertMatchesYamlSnapshot( Yaml::parse($stdout) );
    }
  );
  
