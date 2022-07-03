<?php
declare(strict_types=1);

namespace SixAcross\Confix\Matching\Intention;

use SixAcross\Confix\Matching\MisMatch;


/**
 * The Complete Intention expresses that maps and lists are complete.
 * In other words, all extant values must appear in intent.
 */
class Complete extends Standard
{

	// lists - (integer) keys don't matter, but order does. intent and extant lists must be the same length
	protected function matchList( array $intent, $extant ) : ?MisMatch
	{
		if ( count($intent) != count($extant) ) {
			return new $this->mismatch(
				"Extant list length does not match intent. "
			);
		}

		return parent::MatchList( $intent, $extant );
	}

}
