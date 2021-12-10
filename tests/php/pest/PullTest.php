<?php
declare(strict_types=1);
namespace SixAcross\Confix\Tests;

use Symfony\Component\Yaml\Yaml;


it('Pulls the values for person #1 from the api. ', function() {

    exec( 
        __DIR__ .'/../../../bin/pull.php '. __DIR__ .'/../../examples/person1.yml', 
        $output_lines, 
        $exit_code 
      );
    expect($exit_code)->toBeEmpty();
    $output = Yaml::parse( implode( "\n", $output_lines ) );
    
    $this->assertMatchesYamlSnapshot($output);
    
});
