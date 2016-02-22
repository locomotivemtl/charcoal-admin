<?php

namespace Charcoal\Admin\Property\Display;

use \Charcoal\Admin\Property\AbstractPropertyDisplay;

/**
 * Audio Property Input
 */
class ImageDisplay extends AbstractPropertyDisplay
{
    protected $val;
    /**
     * Width and height of the image
     * Default to: auto
     * @var string $width (ex: 100px)
     * @var string $height (ex: 100px)
     */
    protected $width;
    protected $height;

    /**
     * Max-width and max-height of the image
     * Default: none
     * @var string $maxWidth (ex:100px)
     * @var string $maxHeight (ex:100px)
     */
    protected $maxWidth;
    protected $maxHeight;



    /**
     * Width setter
     * If integer specified, 'px' will be append to it
     * @param mixed $width CSS value
     */
    public function setWidth($width = 'auto')
    {
        if (is_numeric($width)) {
            $width .= 'px';
        }
        $this->width = $width;
    }

    /**
     * Width getter
     * Default @see $this->setWidth()
     * @return string
     */
    public function width()
    {
        if (!$this->width) {
            $this->setWidth();
        }
        return $this->width;
    }

    /**
     * Height setter
     * If integer specified, 'px' will be append to it
     * @param string $height CSS value
     */
    public function setHeight($height = 'auto')
    {
        if (is_numeric($height)) {
            $height .= 'px';
        }
        $this->height = $height;
    }

    /**
     * Height getter
     * Default @see $this->setHeight()
     * @return string
     */
    public function height()
    {
        if (!$this->height) {
            $this->setHeight();
        }
        return $this->height;
    }


    /**
     * Width setter
     * If integer specified, 'px' will be append to it
     * @param string $maxWidth CSS value
     */
    public function setMaxWidth($maxWidth = 'none')
    {
        if (is_numeric($maxWidth)) {
            $maxWidth .= 'px';
        }
        $this->maxWidth = $maxWidth;
    }

    /**
     * MaxWidth getter
     * Default @see $this->setMaxWidth()
     * @return string
     */
    public function maxWidth()
    {
        if (!$this->maxWidth) {
            $this->setMaxWidth();
        }
        return $this->maxWidth;
    }

    /**
     * Height setter
     * If integer specified, 'px' will be append to it
     * @param string $maxHeight CSS value
     */
    public function setMaxHeight($maxHeight = 'none')
    {
        if (is_numeric($maxHeight)) {
            $maxHeight .= 'px';
        }
        $this->maxHeight = $maxHeight;
    }

    /**
     * Height getter
     * Default @see $this->setMaxHeight()
     * @return string
     */
    public function maxHeight()
    {
        if (!$this->maxHeight) {
            $this->setMaxHeight();
        }
        return $this->maxHeight;
    }
}
