<?php
declare(strict_types=1);

namespace SixAcross\Confix\Matching;


trait MismatchTrait /* implements Mismatch */
{
	public readonly Mismatch $next;
	protected array $path = [];

	public function __construct(
		public readonly string $message = '',
	) {}

	public function getMessage() : string
	{
		return $this->message;
	}

	protected static function describePath( array $path ) : string
	{
		$exported = [];
		foreach ( $path as $value ) {
			$exported[] = var_export( $value, true );
		}
		return empty( $path ) ? '' : 'At path '. implode( ' ', $exported ) .', ';
	}

	public function __toString()
	{
		$result = implode( "\n", array_filter([
			$this->describePath( $this->path ) . $this->message,
			(string) ( $this->getNext() ?: '' ),
		]) );

		return $result;
	}

	public function setNext( ?Mismatch $next ) : self
	{
		if ( ! $next ) { return $this; }

		if ( isset( $this->next ) ) {
			$this->next->setNext($next);

		} else {
			$this->next = $next;
		}

		return $this;
	}

	public function getNext() : ?Mismatch
	{
		return $this->next ?? null;
	}

	public function asArray() : array
	{
		$mismatches = isset($this->next) ? $this->next->asArray() : [];
		$mismatches = array_merge( [ $this ], $mismatches );
		return $mismatches;
	}

	public function getPath() : array
	{
		return $this->path;
	}

	public function prependPathElement( string | int $key )
	{
		$this->path = array_values( array_merge( [ $key ], $this->path ) );

		if ( isset( $this->next ) ) {
			$this->next->prependpathElement($key);
		}

		return $this;
	}
}
