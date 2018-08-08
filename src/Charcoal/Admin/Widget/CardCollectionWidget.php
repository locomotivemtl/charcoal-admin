<?php

namespace Charcoal\Admin\Widget;

/**
 * Class CardCollectionWidget
 */
class CardCollectionWidget extends TableWidget
{
    /**
     * @var integer $numColumns
     */
    protected $numColumns;

    /**
     * @var string $cardTemplate
     */
    protected $cardTemplate;

    /**
     * @return integer
     */
    public function numColumns()
    {
        return $this->numColumns;
    }

    /**
     * @param integer $numColumns NumColumns for CardCollectionWidget.
     * @return self
     */
    public function setNumColumns($numColumns)
    {
        $this->numColumns = $numColumns;

        return $this;
    }

    /**
     * @return float|integer
     */
    public function bsColRatio()
    {
        return abs(12 / ($this->numColumns() ?: 12));
    }

    /**
     * @return string
     */
    public function cardTemplate()
    {
        return $this->cardTemplate;
    }

    /**
     * @param string $cardTemplate CardTemplate for CardCollectionWidget.
     * @return self
     */
    public function setCardTemplate($cardTemplate)
    {
        $this->cardTemplate = $cardTemplate;

        return $this;
    }

    /**
     * @return \Generator
     */
    public function objectCardRow()
    {
        foreach ($this->objectRows() as $obj) {
            $GLOBALS['widget_template'] = $this->cardTemplate();
            yield $obj;
            $GLOBALS['widget_template'] = '';
        }
    }

    /**
     * Retrieve the widget's data options for JavaScript components.
     *
     * @return array
     */
    public function widgetDataForJs()
    {
        $data = array_merge_recursive(
            parent::widgetDataForJs(),
            [
                'card_template' => $this->cardTemplate(),
                'num_columns' => $this->numColumns()
            ]
        );

        return $data;
    }
}
