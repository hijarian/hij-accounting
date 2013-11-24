<?php
/** hijarian 24.11.13 23:53 */

namespace pagination;


class DisabledPaginationElement
{
	public $sign;
	public $classes = [];

	public function __construct($sign)
	{
		$this->sign = $sign;
	}

	public function render()
	{
		$classes = $this->makeClasses();
		$sign = $this->sign;
		return sprintf('<li%s><a>%s</a></span>', $classes, $sign); // no href - browser will render link unclickable
	}

	public function __toString()
	{
		return $this->render();
	}

	private function makeClasses()
	{
		return empty($this->classes)
			? ''
			: sprintf(' class="%s"', implode(' ', $this->classes));
	}
}