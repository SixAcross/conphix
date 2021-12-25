<?php
declare(strict_types=1);
namespace SixAcross\Confix\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

abstract class Intent extends Command
{
    protected function configure()
    {
        $result = parent::configure( ...func_get_args() );
        
        $this->getDefinition()->addArgument( new InputArgument(
            'intent_file',
            InputArgument::REQUIRED,
            "The intent file. Pass a dash '-' to pass the intent content on stdin. ",
          ) );
          
        return $result;
    }
}
