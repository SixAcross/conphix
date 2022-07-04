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

	// lists - (integer) keys need not match, order does not matter
	protected function matchList( array $intent, $extant ) : ?Mismatch
	{
		if ( ! ( is_array($extant) and array_is_list($extant) ) ) {
			return $this->mismatches->type(
				"Intended value is a list, but extant value is not. "
			);
		}

		$mismatches = null;
		if ( count($intent) > count($extant) ) {
			$mismatches = $this->mismatches->notExtant( sprintf(
				'%s intended value(s) of a list are not extant. ',
				count($intent) - count($extant),
			) );
			return $mismatches;

		} elseif ( count($intent) < count($extant) ) {
			$mismatches = $this->mismatches->notIntended( sprintf(
				'%s extant value(s) of a list are not in intent. ',
				count($extant) - count($intent),
			) );
		}

		$unmatched_intent = $intent;
		$unmatched_extant = $extant;

		// attempt to match in the exact order first
		$in_order = true;
		foreach ( $unmatched_intent as $index => $intent_item ) {

			if ( ! array_key_exists( $index, $unmatched_extant ) ) {
				break;
			}

			$mismatch = ( new static( $intent_item ) )
				->match( $unmatched_extant[ $index ] );

			// in the event of any mismatch, we can't be sure
			//    whether the mismatch should be a Type or Value Mismatch,
			//    or a pair of NotIntended + NotExtant Mismatches.
			// We call it out of order and give up.
			if ( $mismatch ) {
				$in_order = false;
				break;

			} else {
				unset(
					$unmatched_intent[$index],
					$unmatched_extant[$index],
				);
			}
		}

		// now try to match every remaining intent to every extant value, no matter the order.
		foreach ( $unmatched_intent as $intent_index => $intent_item ) {
			foreach ( $unmatched_extant as $extant_index => $extant_item ) {

				$mismatch = ( new static( $intent_item ) )->match( $extant_item );

				if ( ! $mismatch ) {
					unset(
						$unmatched_intent[$intent_index],
						$unmatched_extant[$extant_index]
					);
				}

			}
		}

		if ( count($unmatched_intent) >0 && count($unmatched_extant) >0 ) {

			$mismatch = $this->mismatches->listValues(
				'One or more list values do not match. '
			);

			$mismatches = $mismatches
				? $mismatches->setNext( $mismatch )
				: $mismatch;
		}

		if ( ! $in_order ) {
			$mismatch = $this->mismatches->order(
				'Extant list values are not in the intended order. '
			);

			$mismatches = $mismatches
				? $mismatches->setNext( $mismatch )
				: $mismatch;
		}

		return $mismatches;
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
			is_scalar($value) => var_export( $value, true ),
			is_object($value) => get_class($value),
			default => gettype($value),
		};
	}

}

