<?php

namespace Charcoal\Admin\Widget;

use \InvalidArgumentException;

use \Charcoal\Admin\AdminWidget;

/**
 *
 */
class PaginationWidget extends AdminWidget
{
    /**
     * @var integer $page
     */
    private $page = 1;

    /**
     * @var integer $numPerPage
     */
    private $numPerPage = 0;

    /**
     * @var integer $numTotal
     */
    private $numTotal;

        /**
         * @param integer $page The page number, of the items to load.
         * @throws InvalidArgumentException If the argument is not a number or lower than 0.
         * @return PaginationWidget Chainable
         */
    public function setPage($page)
    {
        if (!is_numeric($page)) {
            throw new InvalidArgumentException(
                'Page must be an integer value.'
            );
        }
        if ($page < 1) {
            throw new InvalidArgumentException(
                'Page must be 1 or greater.'
            );
        }
        $this->page = (int)$page;
        return $this;
    }

    /**
     * @return integer
     */
    public function page()
    {
        return (int)$this->page;
    }

    /**
     * @return integer
     */
    public function pagePrev()
    {
        return max(1, ($this->page()-1));
    }

    /**
     * @return integer
     */
    public function pageNext()
    {
        return min($this->numPages(), ($this->page()+1));
    }

    /**
     * @param integer $numPerPage The number of items per page to load.
     * @throws InvalidArgumentException If the argument is not a number or lower than 0.
     * @return PaginationWidget Chainable
     */
    public function setNumPerPage($numPerPage)
    {
        if (!is_numeric($numPerPage)) {
            throw new InvalidArgumentException(
                sprintf('Num per page must be a numeric value. (%s sent)', gettype($numPerPage))
            );
        }
        $this->numPerPage = (int)$numPerPage;
        return $this;
    }

    /**
     * @return integer
     */
    public function numPerPage()
    {
        return (int)$this->numPerPage;
    }

    /**
     * @param integer $num The total number of items (to count pages).
     * @throws InvalidArgumentException If the argument is not a number or lower than 0.
     * @return PaginationWidget Chainable
     */
    public function setNumTotal($num)
    {
        if (!is_numeric($num)) {
            throw new InvalidArgumentException(
                'Num total must be an integer value.'
            );
        }
        $this->numTotal = (int)$num;
        return $this;
    }

    /**
     * @return integer
     */
    public function numTotal()
    {
        return $this->numTotal;
    }

    /**
     * Page generator
     *
     * @return array This is a generator
     */
    public function pages()
    {
        $i = 1;
        while ($i <= $this->numPages()) {
            yield [
                'active' => ($i == $this->page()),
                'num' => $i
            ];
            $i++;
        }
    }

    /**
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
     * @return boolean
     */
    public function showPagination()
    {
        return ($this->numPages() > 1);
    }

    /**
     * @return boolean
     */
    public function previousEnabled()
    {
        return ($this->page() > 1);
    }

    /**
     * @return boolean
     */
    public function nextEnabled()
    {
        return ($this->page() < $this->numPages());
    }
}
