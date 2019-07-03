<?php

namespace Charcoal\Admin\Widget;

use Charcoal\Model\ModelInterface;
use Charcoal\Translator\Translation;

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
     * @var boolean $showFooterChip
     */
    protected $showFooterChip = true;

    /**
     * @var Translation|string $chipTitle
     */
    protected $chipTitle;

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
            $this->setDynamicTemplate('widget_template', $this->cardTemplate());
            yield $obj;
        }
    }

    /**
     * Filter the object before its assigned to the row.
     *
     * This method is useful for classes using this trait.
     *
     * @param  ModelInterface $object           The current row's object.
     * @param  array          $objectProperties The $object's display properties.
     * @return array
     */
    protected function parseObjectRow(ModelInterface $object, array $objectProperties)
    {
        $row = $this->parseCollectionObjectRow($object, $objectProperties);
        $objProps = $row['objectProperties'];
        array_walk($objProps, function ($value) use (&$row) {
            $row['objectProperties'][$value['ident']] = $value['val'];

            if (!method_exists($row['object'], 'isChipSuccess')) {
                $row['isChipSuccess'] = $this->isChipSuccess($row['object']);
            }
        });

        return $row;
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

    /**
     * @param ModelInterface $object The model to determine success for.
     * @return boolean
     */
    public function isChipSuccess(ModelInterface $object)
    {
        if (is_callable([$object, 'isViewable'])) {
            return !!$object->isViewable();
        }

        if (is_callable([$object, 'active'])) {
            return !!$object->active();
        }
    }

    /**
     * @return boolean
     */
    public function showFooterChip()
    {
        return $this->showFooterChip;
    }

    /**
     * @param boolean $showFooterChip ShowFooterChip for CardCollectionWidget.
     * @return self
     */
    public function setShowFooterChip($showFooterChip)
    {
        $this->showFooterChip = $showFooterChip;

        return $this;
    }

    /**
     * @return Translation|string
     */
    public function chipTitle()
    {
        if (empty($this->chipTitle)) {
            return $this->defaultChipTitle();
        }

        return $this->chipTitle;
    }

    /**
     * @return string
     */
    private function defaultChipTitle()
    {
        return $this->translator()->translate('Active');
    }

    /**
     * @param Translation|string $chipTitle ChipTitle for CardCollectionWidget.
     * @return self
     */
    public function setChipTitle($chipTitle)
    {
        $this->chipTitle = $this->translator()->translation($chipTitle);

        return $this;
    }
}
