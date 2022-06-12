<?php
declare(strict_types=1);

namespace SixAcross\Confix\Matching\IntentMatcher;

use Exception;

use SixAcross\Confix\Matching\IntentMatcher;
use SixAcross\Confix\Matching\MisMatch;


class Main implements IntentMatcher
{
	public function __construct( public readonly MisMatch $mismatch )
	{
	}

	public function withMisMatch( MisMatch $mismatch )
	{
		return new static($mismatch);
	}

	public function match( $intent, $extant, array $path = [] ) : ?MisMatch
	{
		$matcher = match(true) {
			is_array($intent) and   array_is_list($intent)  => $this->matchList(...),
			is_array($intent) and ! array_is_list($intent)  => $this->matchMap(...),
			is_object($intent)                              => $this->matchObject(...),
			default                                         => $this->matchScalar(...),
		};
		return $matcher( $intent, $extant, $path );

	}

	// maps - order doesn't matter, but intent keys must match
	protected function matchMap( array $intent, $extant, array $path = [] ) : ?MisMatch
	{
		$mismatches = null;
		foreach ( $intent as $key => $value ) {

			$mismatch = null;

			if ( ! array_key_exists( $key, $extant ) ) {
				$mismatch = new $this->mismatch(
					"Intended key '{$key}' is not extant. ",
					$path
				);

			} else {
				$mismatch = self::match(
					$intent[$key],
					$extant[$key],
					array_merge( $path, [ $key ] ),
				);
			}

			if ( $mismatch ) {
				$mismatches = $mismatches
					? $mismatches->setNext( $mismatch )
					: $mismatch;
			}
		}

		return $mismatches;
	}

	// lists - (integer) keys don't matter, but order does
	protected function matchList( array $intent, $extant, array $path = [] ) : ?MisMatch
	{
		if ( ! ( is_array($extant) and array_is_list($extant) ) ) {
			return new $this->mismatch(
				"Intended value is a list, but extant value is not. ",
				$path
			);
		}

		$matched_count = 0;
		$intent_count = count($intent);
		foreach ( $intent as $index => $intent_item ) {

			$matched = false;
			while ( ! $matched ) {

				if ( count($extant) <1 ) {
					return new $this->mismatch(
						"Only {$matched_count} extant item(s) matched {$intent_count} intended item(s) in an ordered list. ",
						$path
					);
				}

				$extant_item = array_shift($extant);

				$matched = ! $mismatch = self::match(
					$intent_item,
					$extant_item,
					array_merge( $path, [ $index ] ),
				);

				// We ignore mismatches here,
				//	because the intent might match later extant items...
			}

			$matched_count++;

		}
	}

	protected function matchObject( object $intent, $extant, array $path = [] ) : ?MisMatch
	{
		if ( $intent instanceof IntentMatcher ) {
			$intent->match( $intent, $extant, $path );

		} else {

			if ( $intent != $extant ) {
				return new $this->mismatch(
					sprintf(
						'Intended object %s does not match extant %s . ',
						$this->describe($intent),
						$this->describe($extant),
					),
					$path
				);
			}

		}
	}

	protected function matchScalar( $intent, $extant, array $path = [] ) : ?MisMatch
	{
		if ( $intent != $extant ) {
			return new $this->mismatch(
				sprintf(
					'Intended value %s does not match extant %s . ',
					$this->describe($intent),
					$this->describe($extant),
				),
				$path
			);
		}
		
		return null;
	}

	protected function describe( $value ) : string
	{
		return match (true) {
			is_scalar($value) => (string) $value,
			is_object($value) => get_class($value),
			default => gettype($value),
		};
	}

}

