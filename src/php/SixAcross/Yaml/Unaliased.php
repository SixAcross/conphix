<?php
declare(strict_types=1);

namespace SixAcross\Yaml;

use Symfony\Component\Yaml\Yaml;

use SixAcross\Yaml\Unaliased;


class Unaliased extends Yaml
{
    public static function parse(string $input, int $flags = 0): mixed
    {
        $yaml = new Unaliased\Parser();

        return $yaml->parse($input, $flags);
    }

    public static function dump(mixed $input, int $inline = 2, int $indent = 4, int $flags = 0): string
    {
        $yaml = new Unaliased\Dumper($indent);

        return $yaml->dump($input, $inline, 0, $flags);
    }
}
