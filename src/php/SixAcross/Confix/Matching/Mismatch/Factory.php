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

	public static function valueNotExtant( string $message ) : Mismatch\ValueNotExtant
	{
		return static::mismatch( ValueNotExtant::class, $message );
	}

	public static function valuesNotExtant( string $message ) : Mismatch\ValuesNotExtant
	{
		return static::mismatch( ValuesNotExtant::class, $message );
	}

	public static function valueNotIntended( string $message ) : Mismatch\ValueNotIntended
	{
		return static::mismatch( ValueNotIntended::class, $message );
	}

	public static function valuesNotIntended( string $message ) : Mismatch\ValuesNotIntended
	{
		return static::mismatch( ValuesNotIntended::class, $message );
	}

	public static function mismatch( string $class, string $message ) : Mismatch
	{
		return new $class($message);
	}
}
