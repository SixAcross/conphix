<?php
declare(strict_types=1);

namespace SixAcross\Confix\Matching;


interface MisMatch
{
	public function getMessage() : string;

	public function __toString();

	public function setNext( ?Mismatch $next ) : MisMatch;

	public function getNext() : ?MisMatch;
}
