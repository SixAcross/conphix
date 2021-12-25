<?php
declare(strict_types=1);
namespace SixAcross\Confix;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;
use SixAcross\Confix\Command;



class Application extends SymfonyApplication
{
    public function __construct()
    {
        $result = parent::__construct( ...func_get_args() );
        
        $this->setCommandLoader( new FactoryCommandLoader([
            'pull'        => fn() => new Command\Pull,
          ] ) );
        
        return $result;
    }

}
