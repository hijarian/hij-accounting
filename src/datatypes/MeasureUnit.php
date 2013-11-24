<?php
/** hijarian 23.11.13 13:49 */

namespace datatypes;


class MeasureUnit
{
	public $id;
	public $name;

	public function __construct($id, $name)
	{
		$this->id = $id;
		$this->name = $name;
	}
}