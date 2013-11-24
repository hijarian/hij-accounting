<?php
/** hijarian 23.11.13 11:37 */

namespace datatypes;

class EmptyValue
{
	public function __call($name, $arguments)
	{
		return '';
	}

	public function __get($attr)
	{
		return '';
	}
} 