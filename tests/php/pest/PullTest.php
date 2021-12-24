<?php
declare(strict_types=1);
namespace SixAcross\Confix\Tests;

use Symfony\Component\Yaml\Yaml;


$exec_for_outputs = function( string $command ) {

        $process = proc_open(
            $command,
            [ [ 'pipe', 'r' ], [ 'pipe', 'w' ], [ 'pipe', 'w' ] ],
            $pipes
          );
        expect($process)->toBeResource();
        
        $stderr = stream_get_contents($pipes[2]);
        $stdout = stream_get_contents($pipes[1]);
        
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit_code = proc_close($process);
        
        return [ $stdout, $stderr, $exit_code ];
};


it( 'accepts intent input on stdin when passed a dash as the input file argument. ', 
    function() use ( $exec_for_outputs ) {
        [ $stdout, $stderr, $exit_code ] = $exec_for_outputs( 
            'cat '. __DIR__ .'/../../examples/person1.intent.yml | '. __DIR__ .'/../../../bin/pull.php - '
          );
        
        expect($exit_code)->toBe(0);
    }
  );
    
it( 'produces intent output on stdout when passed a dash as the input file argument. ', 
    function() use ( $exec_for_outputs ) {
        [ $stdout, $stderr, $exit_code ] = $exec_for_outputs( 
            'cat '. __DIR__ .'/../../examples/person1.intent.yml | '. __DIR__ .'/../../../bin/pull.php - '
          );
        
        expect($exit_code)->toBe(0);
        expect($stderr   )->toBe('');
        $this->assertMatchesYamlSnapshot( Yaml::parse($stdout) );
    }
  );
  
it( 'produces non-intent output on stderr (only) when passed a dash as the input file argument. ',
    function() use ( $exec_for_outputs ) {
        [ $stdout, $stderr, $exit_code ] = $exec_for_outputs( 
            'echo "non-yaml garbage" | '. __DIR__ .'/../../../bin/pull.php - '
          );
        
        expect($exit_code )->toBe(255);
        expect($stdout    )->toBe('');
        expect($stderr    )->not()->toBeEmpty();
    }
  );


it( 'writes extant values to intent resources where those keys already appear in intent. ', 
    function() use ( $exec_for_outputs ) {
        [ $stdout, $stderr, $exit_code ] = $exec_for_outputs( 
            'cat '. __DIR__ .'/../../examples/person1.intent.yml | '. __DIR__ .'/../../../bin/pull.php - '
          );
        expect($exit_code)->toBe(0);
        expect($stderr   )->toBe('');
        $this->assertMatchesYamlSnapshot( Yaml::parse($stdout) );
    }
  );
  
it( 'does not write extant values to intent resources where those resources or keys do not appear in intent. ',
    function() use ( $exec_for_outputs ) {
        [ $stdout, $stderr, $exit_code ] = $exec_for_outputs( 
            'cat '. __DIR__ .'/../../examples/person1.somevalues.intent.yml | '. __DIR__ .'/../../../bin/pull.php - '
          );
        expect($exit_code)->toBe(0);
        expect($stderr   )->toBe('');
        $this->assertMatchesYamlSnapshot( Yaml::parse($stdout) );
    }
  );
  
it( 'writes extant values to intent for keys not appearing in intent when the --all-values option is passed. ', 
    function() use ( $exec_for_outputs ) {
        [ $stdout, $stderr, $exit_code ] = $exec_for_outputs( 
            'cat '. __DIR__ .'/../../examples/person1.somevalues.intent.yml | '
                . __DIR__ .'/../../../bin/pull.php --all-values - '
          );
        expect($exit_code)->toBe(0);
        expect($stderr   )->toBe('');
        $this->assertMatchesYamlSnapshot( Yaml::parse($stdout) );
    }
  );


