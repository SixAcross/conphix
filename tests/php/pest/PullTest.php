<?php
declare(strict_types=1);
namespace SixAcross\Confix\Tests;

use Symfony\Component\Yaml\Yaml;


it( 'accepts intent input on stdin when passed a dash as the input file argument. ', 
    function() {
        [ $stdout, $stderr, $exit_code ] = executeCommand(
            __DIR__ .'/../../../bin/pull.php - ',
            file_get_contents( __DIR__ .'/../../examples/person1.intent.yml' ),
          );
        
        expect($exit_code)->toBe(0);
    }
  );
    
it( 'produces intent output on stdout when passed a dash as the input file argument. ', 
    function() {
        [ $stdout, $stderr, $exit_code ] = executeCommand(
            __DIR__ .'/../../../bin/pull.php - ',
            file_get_contents( __DIR__ .'/../../examples/person1.intent.yml' ),
          );
        
        expect($exit_code)->toBe(0);
        expect($stderr   )->toBe('');
        $this->assertMatchesYamlSnapshot( Yaml::parse($stdout) );
    }
  );
  
it( 'produces non-intent output on stderr (only) when passed a dash as the input file argument. ',
    function() {
        [ $stdout, $stderr, $exit_code ] = executeCommand(
            __DIR__ .'/../../../bin/pull.php - ',
            "non-yaml garbage",
          );
        
        expect($exit_code )->toBe(255);
        expect($stdout    )->toBe('');
        expect($stderr    )->not()->toBeEmpty();
    }
  );


it( 'writes extant values to intent resources where those keys already appear in intent. ', 
    function() {
        [ $stdout, $stderr, $exit_code ] = executeCommand(
            __DIR__ .'/../../../bin/pull.php - ',
            file_get_contents( __DIR__ .'/../../examples/person1.intent.yml' ),
          );
        expect($exit_code)->toBe(0);
        expect($stderr   )->toBe('');
        $this->assertMatchesYamlSnapshot( Yaml::parse($stdout) );
    }
  );
  
it( 'does not write extant values to intent resources where those resources or keys do not appear in intent. ',
    function() {
        [ $stdout, $stderr, $exit_code ] = executeCommand(
            __DIR__ .'/../../../bin/pull.php - ',
            file_get_contents( __DIR__ .'/../../examples/person1.somevalues.intent.yml' ),
          );
        expect($exit_code)->toBe(0);
        expect($stderr   )->toBe('');
        $this->assertMatchesYamlSnapshot( Yaml::parse($stdout) );
    }
  );
  
it( 'writes extant values to intent for keys not appearing in intent when the --all-values option is passed. ', 
    function() {
        [ $stdout, $stderr, $exit_code ] = executeCommand(
            __DIR__ .'/../../../bin/pull.php --all-values - ',
            file_get_contents( __DIR__ .'/../../examples/person1.somevalues.intent.yml' ),
          );
        expect($exit_code)->toBe(0);
        expect($stderr   )->toBe('');
        $this->assertMatchesYamlSnapshot( Yaml::parse($stdout) );
    }
  );


