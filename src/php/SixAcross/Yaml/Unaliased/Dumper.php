<?php
declare(strict_types=1);

namespace SixAcross\Yaml\Unaliased;

use Throwable;
use Symfony\Component\Yaml\Dumper as SymfonyDumper;


class Dumper extends SymfonyDumper
{
    protected bool $neutralized = false;
    
    
    public function dump(mixed $input, int $inline = 0, int $indent = 0, int $flags = 0): string
    {
        if ( $this->neutralized ) {
            return parent::dump( $input, $inline, $indent, $flags );
        }
        
        $tree = $input;  
        
        $tree = $this->neutralizeParsedTree( $tree );
        $this->neutralized = true;
        
        try {
            $yaml = parent::dump( $tree, $inline, $indent, $flags );
        } catch ( Throwable $x ) {
            $this->neutralized = false;
        }

        $yaml = $this->deneutralizeYamlString($yaml);
        $this->neutralized = false;
        
        return $yaml;
    }
    
    protected function neutralizeParsedTree( mixed $tree ) : mixed
    {
        $result = $tree;
        return $result;
    }
    
    protected function deneutralizeYamlString( string $output ) : string
    {
        $result = $output;
        
        // re-activate anchors
        #TODO: minify all whitespace between anchors and following tags to a single space
        preg_match_all( '@!confix/anchor/(\S+)@', $result, $matches );
        foreach ( $matches[0] as $idx => $match ) {
            $result = str_replace( 
                $matches[0][$idx], 
                '&'. base64_decode( 
                    str_replace( 
                        [ '_', '-', ],
                        [ '+', '=', ],
                        $matches[1][$idx] 
                      ) ), 
                $result 
              );
        }
        
        // re-activate aliases
        $result = str_replace( 'CONFIX_ALIAS_', '*', $result );
        
        // re-activate merge keys
        $result = str_replace( 'CONFIX_MERGE_KEY', '<<', $result );
        
        return $result;
    }
}
