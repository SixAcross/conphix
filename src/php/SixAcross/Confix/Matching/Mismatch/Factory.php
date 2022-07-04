<?php
declare(strict_types=1);

namespace SixAcross\Confix\Matching\Mismatch;

use SixAcross\Confix\Matching\Mismatch;


class Factory
{
	public static function value( string $message ) : Mismatch\Value
	{
		return static::mismatch( Value::class, $message );
	}

	public static function listValues( string $message ) : Mismatch\ListValues
	{
		return static::mismatch( ListValues::class, $message );
	}

	public static function type( string $message ) : Mismatch\Type
	{
		return static::mismatch( Type::class, $message );
	}

	public static function length( string $message ) : Mismatch\Length
	{
		return static::mismatch( Length::class, $message );
	}

	public static function order( string $message ) : Mismatch\Order
	{
		return static::mismatch( Order::class, $message );
	}

	public static function notExtant( string $message ) : Mismatch\NotExtant
	{
		return static::mismatch( NotExtant::class, $message );
	}

	public static function notIntended( string $message ) : Mismatch\NotIntended
	{
		return static::mismatch( NotIntended::class, $message );
	}

	public static function mismatch( string $class, string $message ) : Mismatch
	{
		return new $class($message);
	}
}
