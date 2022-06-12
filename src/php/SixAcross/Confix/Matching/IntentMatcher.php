<?php
declare(strict_types=1);

namespace SixAcross\Confix\Matching;


interface IntentMatcher
{
	public function match( $intent, $extant ) : ?MisMatch ;
}
