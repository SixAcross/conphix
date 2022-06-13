<?php
declare(strict_types=1);

namespace SixAcross\Confix\Matching\Intention;

use SixAcross\Confix\Matching\MisMatch;
use SixAcross\Confix\Matching\MisMatch\General;
use SixAcross\Confix\Matching\Intention;


class Standard implements Intention
{
	public function __construct(
		public readonly mixed $intent,
		public readonly MisMatch $mismatch = new General,
	) { }

	public static function withMisMatch( MisMatch $mismatch )
	{
		return new static( intent: $this->intent, mismatch: $mismatch );
	}

	public function withIntent( mixed $intent ) : Intention
	{
		return new static( intent: $intent, mismatch: $this->mismatch );
	}

	public function match( $extant ) : ?MisMatch
	{
		return $this->matchIntent( $this->intent, $extant );
	}

	protected function matchIntent( $intent, $extant ) : ?MisMatch
	{
		$matcher = match(true) {
			is_array($intent) and   array_is_list($intent)  => $this->matchList(...),
			is_array($intent) and ! array_is_list($intent)  => $this->matchMap(...),
			is_object($intent)                              => $this->matchObject(...),
			default                                         => $this->matchScalar(...),
		};

		return $matcher( $intent, $extant );
	}

	// maps - order doesn't matter, but intent keys must match
	protected function matchMap( array $intent, $extant ) : ?MisMatch
	{
		if ( ! is_array($extant) ) {
			return new $this->mismatch(
				"Intended value is an map, but extant value is not. ",
				$path
			);
		}

		$mismatches = null;
		foreach ( $intent as $key => $value ) {

			$mismatch = null;

			if ( ! array_key_exists( $key, $extant ) ) {
				$mismatch = new $this->mismatch(
					"Intended key '{$key}' is not extant. ",
					$path
				);

			} else {
				$mismatch = (new static( $intent[$key] ))->match(
					$extant[$key]
				)?->prependPathElement($key);
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
	protected function matchList( array $intent, $extant ) : ?MisMatch
	{
		if ( ! ( is_array($extant) and array_is_list($extant) ) ) {
			return new $this->mismatch(
				"Intended value is a list, but extant value is not. "
			);
		}

		$matched_count = 0;
		$intent_count = count($intent);
		foreach ( $intent as $index => $intent_item ) {

			$matched = false;
			while ( ! $matched ) {

				if ( count($extant) <1 ) {
					$mismatch = new $this->mismatch(
						"Only {$matched_count} extant item(s) matched {$intent_count} intended item(s) in an ordered list. "
					);
					return $mismatch;
				}

				$extant_item = array_shift($extant);

				$mismatch = (new static( $intent_item ))->match(
					$extant_item
				);

				$matched = ! $mismatch;

				// We ignore mismatches here,
				//	because the intent might match later extant items...
			}

			$matched_count++;

		}
	}

	protected function matchObject( object $intent, $extant, array $path = [] ) : ?MisMatch
	{
		if ( $intent instanceof Intention ) {
			return $intent->match( $extant );

		} else {
			return $this->matchScalar( $intent, $extant );
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
				)
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

