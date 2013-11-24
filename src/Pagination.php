<?php
use pagination\DisabledPaginationElement;
use pagination\PaginationElement;

/** hijarian 24.11.13 22:33
 */

class Pagination
{
	private $urlParams;

	private $pagesCount = 1;

	public $currentPage = 1;

	/** @var int How many pages to show before and after current */
	public $spread = 2;

	/** @var bool Whether to always show the left/right and last/first arrows */
	public $alwaysShowArrows = true;

	public function __construct($urlParams, $pageLength, $elementsCount)
	{
		$this->urlParams = $urlParams;
		$this->pagesCount = ceil($elementsCount / $pageLength);
	}

	/**
	 * @return \pagination\PaginationElement[] Scheme for rendering the pagination widget.
	 * [
	 *   [ '<<', bool ], go to first page (disabled on first page)
	 *   [ '<', bool ], go to previous page (disabled on first page)
	 *   [ int, true ], number of page -2
	 *   [ int, true ] number of page -1
	 *   [ int, false ] number of current page and marker that it should be disabled
	 *   [ int, true ] number of page +1
	 *   [ int, true ] number of page +2
	 *   [ '>', bool ] go to next page (disabled on last page)
	 *   [ '>>', bool ] go to last page (disabled on last page)
	 * ]
	 *
	 * Of course, we should be sure that if there's less than 5 pages we write correct numbers in pagination.
	 */
	public function getScheme()
	{
		if ($this->pagesCount < 2)
			return [];

		if ($this->isPageOutOfBound())
			return [$this->makeRewindElement()];

		$scheme = [];

		if ($this->currentPage > 1)
			$scheme[] = $this->makeRewindElement();

		if ($this->currentPage != 1)
			$scheme[] = $this->makePreviousPageElement();

		if ($this->pagesNumberNotGreaterThanShouldBeShown())
		{
			for ($i = 1; $i <= $this->pagesCount; ++$i)
			{
				$scheme[] = ($this->currentPage == $i)
					? $this->makeCurrentPageElement($i)
					: $this->makeUsualPageElement($i);
			}
		}
		else
		{
			$firstPageToShow = $this->currentPage - $this->spread;
			$lastPageToShow = $this->currentPage + $this->spread;

			if ($lastPageToShow > $this->pagesCount) {
				$firstPageToShow = $this->pagesCount - $this->spread * 2;
				$lastPageToShow = $this->pagesCount;
			}

			if ($firstPageToShow < 1) {
				$firstPageToShow = 1;
				$lastPageToShow = 1 + $this->spread * 2;
			}

			if ($firstPageToShow !== 1)
				$scheme[] = $this->makeDisabledPageElement('...');

			for ($i = $firstPageToShow; $i <= $lastPageToShow; ++$i)
			{
				$scheme[] = ($this->currentPage == $i)
					? $this->makeCurrentPageElement($i)
					: $this->makeUsualPageElement($i);
			}

			if ($lastPageToShow !== $this->pagesCount)
				$scheme[] = $this->makeDisabledPageElement('...');
		}

		if ($this->currentPage != $this->pagesCount)
			$scheme[] = $this->makeNextPageElement();

		if ($this->currentPage < $this->pagesCount)
			$scheme[] = $this->makeForwardElement();

		return $scheme;
	}

	/**
	 * @return bool
	 */
	private function isPageOutOfBound()
	{
		return $this->currentPage > $this->pagesCount or $this->currentPage < 1;
	}

	private function pagesNumberNotGreaterThanShouldBeShown()
	{
		$shouldBeShown = $this->spread * 2 + 1;
		return $this->pagesCount <= $shouldBeShown;
	}

	/**
	 * @return PaginationElement
	 */
	private function makeRewindElement()
	{
		$element = new PaginationElement($this->urlParams, 1, '<<');
		$element->classes = ['arrow'];
		return $element;
	}

	/**
	 * @param $i
	 * @return PaginationElement
	 */
	private function makeUsualPageElement($i)
	{
		return new PaginationElement($this->urlParams, $i, $i);
	}

	/**
	 * @param $i
	 * @return PaginationElement
	 */
	private function makeDisabledPageElement($i)
	{
		$element = new DisabledPaginationElement($i);
		$element->classes[] = 'unavailable';
		return $element;
	}

	/**
	 * @param $i
	 * @return PaginationElement
	 */
	private function makeCurrentPageElement($i)
	{
		$element = new DisabledPaginationElement($i);
		$element->classes[] = 'current';
		return $element;
	}

	private function makePreviousPageElement()
	{
		$element = new PaginationElement($this->urlParams, $this->currentPage - 1, '<');
		$element->classes[] = 'arrow';
		return $element;
	}

	private function makeNextPageElement()
	{
		$element = new PaginationElement($this->urlParams, $this->currentPage + 1, '>');
		$element->classes[] = 'arrow';
		return $element;
	}

	private function makeForwardElement()
	{
		$element = new PaginationElement($this->urlParams, $this->pagesCount, '>>');
		$element->classes[] = 'arrow';
		return $element;
	}
}