<?php

namespace Charcoal\Admin\Support;

/**
 * Object Label Support Trait
 */
trait ObjectLabelTrait
{
    /**
     * @param object $object The object to get the label from.
     * @param string $label  The label to return.
     * @return string
     */
    public function getObjectAdminLabel($object, $label)
    {
        $metadata = $object->metadata();

        if (!empty($metadata->admin['labels'])) {
            $labels = $metadata->admin['labels'];
        }

        if (!empty($labels[$label])) {
            return (string)$this->translator->translation($labels[$label]);
        }

        return '';
    }
}
