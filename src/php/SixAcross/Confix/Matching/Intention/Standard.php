<?php
declare(strict_types=1);

namespace SixAcross\Confix\Matching\Intention;

use SixAcross\Confix\Matching\Mismatch;
use SixAcross\Confix\Matching\Intention;


class Standard implements Intention
{
	public function __construct(
		public readonly mixed $intent,
		public readonly Mismatch\Factory $mismatches = new Mismatch\Factory,
	) { }

	public function withIntent( mixed $intent ) : Intention
	{
		return new static( intent: $intent, mismatches: $this->mismatches );
	}

	public function match( $extant ) : ?Mismatch
	{
		return $this->matchIntent( $this->intent, $extant );
	}

	protected function matchIntent( $intent, $extant ) : ?Mismatch
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
	protected function matchMap( array $intent, $extant ) : ?Mismatch
	{
		if ( ! is_array($extant) ) {
			return $this->mismatches->type(
				"Intended value is a map, but extant value is not. "
			);
		}

		$keys = array_unique( array_merge( array_keys($intent), array_keys($extant) ) );

		$mismatches = null;
		foreach ( $keys as $key ) {

			$mismatch = null;

			if ( ! array_key_exists( $key, $extant ) ) {
				$mismatch = $this->mismatches->notExtant( sprintf(
					'Intended key %s is not extant. ',
					$this->describe($key)
				) );

			} elseif ( ! array_key_exists( $key, $intent ) ) {
				$mismatch = $this->mismatches->notIntended( sprintf(
					'Extant key %s is not in intent. ',
					$this->describe($key),
				) );

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
	protected function matchList( array $intent, $extant ) : ?Mismatch
	{
		if ( ! ( is_array($extant) and array_is_list($extant) ) ) {
			return $this->mismatches->type(
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

	protected function matchObject( object $intent, $extant, array $path = [] ) : ?Mismatch
	{
		if ( $intent instanceof Intention ) {
			return $intent->match( $extant );

		} else {
			return $this->matchScalar( $intent, $extant );
		}
	}

	protected function matchScalar( $intent, $extant, array $path = [] ) : ?Mismatch
	{
		if ( $intent != $extant ) {
			return $this->mismatches->value(
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

