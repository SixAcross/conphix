<?php
declare(strict_types=1);

namespace SixAcross\Confix\Matching\MisMatch;

use SixAcross\Confix\Matching\MisMatch;


trait GeneralTrait /* implements MisMatch */
{
	public readonly Mismatch $next;

	public function __construct(
		public readonly string $message,
		public readonly array  $path = [],
	) {}

	public function getMessage() : string
	{
		return (string) $this->message;
	}

	public function __toString()
	{
		$result = implode( "\n", array_filter([

			( empty( $this->path )
				? ''
				: 'At path '. implode( ' ', $this->path ) .', '
			)
				. $this->message,

			(string) ( $this->getNext() ?: '' ),

		]) );

		return $result;
	}

	public function setNext( ?Mismatch $next ) : self
	{
		if ( isset( $this->next ) ) {
			$this->next->setNext($next);

		} else {
			$this->next = $next;
		}

		return $this;
	}

	public function getNext() : ?MisMatch
	{
		return $this->next ?? null;
	}

	public function asArray() : array
	{
		$mismatches = isset($this->next) ? $this->next->asArray() : [];
		$mismatches = array_merge( [ $this ], $mismatches );
		return $mismatches;
	}
}
