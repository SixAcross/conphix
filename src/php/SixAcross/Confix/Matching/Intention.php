<?php
declare(strict_types=1);

namespace SixAcross\Confix\Matching;


interface Intention
{
	public function match( $extant ) : ?MisMatch ;

	public function withIntent( mixed $intent ) : Intention ;
}
