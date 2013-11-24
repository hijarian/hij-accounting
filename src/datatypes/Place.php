<?php
/** hijarian 22.11.13 23:58 */
namespace datatypes;

class Place
{
	public $id;
	public $name;

	public function __construct($id, $name)
	{
		$this->id = $id;
		$this->name = $name;
	}

}