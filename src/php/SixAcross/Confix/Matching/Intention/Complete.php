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
	// maps - order does not matter, but all extant keys must exist in intent, and vice versa
	protected function matchMap( array $intent, $extant ) : ?MisMatch
	{
		$mismatches = parent::matchMap( $intent, $extant );

		foreach ( $extant as $key => $value ) {

			$mismatch = null;

			if ( ! array_key_exists( $key, $intent ) ) {

				$mismatch = new $this->mismatch(
					"Extant key '{$key}' is not in intent. "
				);

				$mismatches = $mismatches
					? $mismatches->setNext( $mismatch )
					: $mismatch;
			}
		}

		return $mismatches;
	}

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
