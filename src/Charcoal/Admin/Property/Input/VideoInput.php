<?php

namespace Charcoal\Admin\Property\Input;

// From 'charcoal-admin'
use Charcoal\Admin\Property\Input\FileInput;

/**
 * Video Property Input
 */
class VideoInput extends FileInput
{
    /**
     * Retrieve list of default file type specifiers.
     *
     * @return string
     */
    public function getDefaultAccept()
    {
        return 'video/*';
    }

    /**
     * @return string|null
     */
    public function filePreview()
    {
        $value = $this->inputVal();
        if ($value) {
            return $this->view()->render('charcoal/admin/property/input/video/preview', $this);
        }

        return '';
    }
}
