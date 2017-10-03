<?php

namespace Charcoal\Admin\Widget;

use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Source\Pagination;
use Charcoal\Source\PaginationInterface;

// From 'charcoal-admin'
use Charcoal\Admin\AdminWidget;

/**
 *
 */
class PaginationWidget extends AdminWidget
{
    /**
     * The pager object.
     *
     * @var PaginationInterface
     */
    private $pager;

    /**
     * The total number of items.
     *
     * @var integer
     */
    private $numTotal;

    /**
     * Retrieve the Paginationb object.
     *
     * @return PaginationInterface
     */
    protected function pager()
    {
        if ($this->pager === null) {
            $this->pager = $this->createPagination();
        }

        return $this->pager;
    }

    /**
     * Create the Pagination object.
     *
     * @return PaginationInterface
     */
    protected function createPagination()
    {
        $pagination = new Pagination();
        return $pagination;
    }

    /**
     * Set the page number.
     *
     * @param  integer $page The current page. Pages should start at 1.
     * @return PaginationWidget Chainable
     */
    public function setPage($page)
    {
        $this->pager()->setPage($page);

        return $this;
    }

    /**
     * Retrieve the page number.
     *
     * @return integer
     */
    public function page()
    {
        return $this->pager()->page();
    }

    /**
     * Retrieve the previous page number.
     *
     * @return integer
     */
    public function pagePrev()
    {
        return max(1, ($this->page() - 1));
    }

    /**
     * Retrieve the next page number.
     *
     * @return integer
     */
    public function pageNext()
    {
        return min($this->numPages(), ($this->page() + 1));
    }

    /**
     * Set the number of results per page.
     *
     * @param  integer $count The number of results to return, per page.
     *     Use 0 to request all results.
     * @return PaginationWidget Chainable
     */
    public function setNumPerPage($count)
    {
        $this->pager()->setNumPerPage($count);

        return $this;
    }

    /**
     * Retrieve the number of results per page.
     *
     * @return integer
     */
    public function numPerPage()
    {
        return $this->pager()->numPerPage();
    }

    /**
     * Set the total number of items (for counting pages).
     *
     * @param  integer $total The total number of items.
     * @throws InvalidArgumentException If the argument is not a number or lower than 0.
     * @return PaginationWidget Chainable
     */
    public function setNumTotal($total)
    {
        if (!is_numeric($total)) {
            throw new InvalidArgumentException(
                'Total Number must be numeric.'
            );
        }

        $total = (int)$total;
        if ($total < 0) {
            throw new InvalidArgumentException(
                'Total Number must be greater than zero.'
            );
        }

        $this->numTotal = $total;

        return $this;
    }

    /**
     * Retrieve the total number of items (for counting pages).
     *
     * @return integer
     */
    public function numTotal()
    {
        return $this->numTotal;
    }

    /**
     * Yield each page.
     *
     * @return array|Generator
     */
    public function pages()
    {
        $i = 1;
        while ($i <= $this->numPages()) {
            yield [
                'active' => ($i == $this->page()),
                'num'    => $i
            ];
            $i++;
        }
    }

    /**
     * Retrieve the number of pages.
     *
     * @return integer
     */
    public function numPages()
    {
        if ($this->numPerPage() == 0) {
            return 1;
        }

        return ceil($this->numTotal() / $this->numPerPage());
    }

    /**
     * Determine if pagination can be displayed.
     *
     * @return boolean
     */
    public function showPagination()
    {
        return ($this->numPages() > 1);
    }

    /**
     * Determine if the "previous page" link can be displayed.
     *
     * @return boolean
     */
    public function previousEnabled()
    {
        return ($this->page() > 1);
    }

    /**
     * Determine if the "next page" link can be displayed.
     *
     * @return boolean
     */
    public function nextEnabled()
    {
        return ($this->page() < $this->numPages());
    }
}
