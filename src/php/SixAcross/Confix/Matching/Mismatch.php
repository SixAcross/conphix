<?php
declare(strict_types=1);

namespace SixAcross\Confix\Matching;


interface Mismatch
{
	public function getMessage() : string;

	public function __toString();

	public function setNext( ?Mismatch $next ) : Mismatch;

	public function getNext() : ?Mismatch;

	public function getPath() : array;

	public function prependPathElement( string | int $key );

}
