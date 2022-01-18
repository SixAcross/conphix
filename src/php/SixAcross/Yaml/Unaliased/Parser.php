<?php
declare(strict_types=1);

namespace SixAcross\Yaml\Unaliased;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Parser as SymfonyParser;
use Symfony\Component\Yaml\Exception\ParseException;


class Parser extends SymfonyParser
{
    public function parse(string $value, int $flags = 0): mixed
    {
        $yaml  = $value;
        $yaml  = $this->neutralizeYamlString( $yaml, $flags );
        $tree  = parent::parse( $yaml, $flags );
        $tree  = $this->finalizeParsedTree($tree);
        return $tree;
    }
    
    protected function neutralizeYamlString( string $yaml, int $flags = 0 ) : string
    {
        $result = $yaml;
        
        // neutralize anchors
        // anchors are neutralized by converting them to tags
        // if a tag follows the anchor, the tag is encoded into the anchor tag
        preg_match_all( $pattern = "/(?<=\s\\&)\S+(\s+!\S+)?/", $result, $matches );
        foreach ( $matches[0] as $match ) {
          
            if ( ! ( Yaml::PARSE_CUSTOM_TAGS & $flags ) ) {
                throw new ParseException('Yaml::PARSE_CUSTOM_TAGS flag is required. ');
            }
            
            $result = str_replace( 
                '&'. $match, 
                '!confix/anchor/'
                    . str_replace( 
                        [ '+', '=', ],
                        [ '_', '-', ],
                        base64_encode($match) 
                      ), 
                $result 
              );
        }
        
        // neutralize aliases
        $result = preg_replace( '/(?<=\s)\\*(?=[a-z])/i', 'CONFIX_ALIAS_', $result );
        
        // neutralize merge keys
        $result = preg_replace( '/(?<=\s)<<(\s*:)/i', 'CONFIX_MERGE_KEY\1', $result );
        
        return $result;
    }
    
    protected function finalizeParsedTree( mixed $output ) : mixed
    {
        $result = $output;
        return $result;
    }
}
