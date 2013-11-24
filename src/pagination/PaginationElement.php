<?php
/** hijarian 24.11.13 23:07 */

namespace pagination;

class PaginationElement
{
	/** @var string Name of the query variable holding the page number */
	public $pageVarName = 'page';

	/** @var array URL parameters of the page, to properly build the link preserving current context */
	public $urlParams;

	/** @var int What page this element should lead to. */
	public $pageNumber;

	/** @var string What should be printed on the pagination element */
	public $sign;

	/** @var array CSS classes to put to this element */
	public $classes = [];

	/**
	 * @param array $urlParams URL parametes of the page, to properly build the link preserving current context.
	 * @param int $pageNumber Number of the page.
	 * @param string $sign What should be printed on the pagination element.
	 */
	public function __construct($urlParams, $pageNumber, $sign)
	{
		$this->pageNumber = $pageNumber;
		$this->urlParams = $urlParams;
		$this->sign = $sign;
	}

	/**
	 * @return string
	 */
	public function render()
	{
		$href = $this->makeHref();
		$class = $this->makeClass();
		$sign = $this->sign;
		return sprintf('<li%s><a href="%s">%s</a></li>', $class, $href, $sign);
	}

	public function __toString()
	{
		return $this->render();
	}

	private function makeClass()
	{
		return empty($this->classes)
			? ''
			: sprintf(' class="%s"', implode(' ', $this->classes));
	}

	private function makeHref()
	{
		$params = array_merge(
			$this->urlParams,
			[$this->pageVarName => $this->pageNumber]
		);
		return '?' . http_build_query($params);
	}
} 