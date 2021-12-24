<?php
declare(strict_types=1);
namespace SixAcross\Confix\Tests;


function executeCommand( 
    string $command,
    string $stdin = null,
  ) : array /* string, string, int */
{
    $process = proc_open(
        $command,
        [ [ 'pipe', 'r' ], [ 'pipe', 'w' ], [ 'pipe', 'w' ] ],
        $pipes
      );
    expect($process)->toBeResource();
    
    if ( is_string($stdin) ) {
        fwrite( $pipes[0], $stdin );
    }
    fclose($pipes[0]);
    
    $stderr = stream_get_contents($pipes[2]);
    $stdout = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    
    $exit_code = proc_close($process);
    
    return [ $stdout, $stderr, $exit_code ];
};
