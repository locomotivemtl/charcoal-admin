<?php

namespace Charcoal\Admin\Widget;

// From 'charcoal-admin'
use Charcoal\Admin\AdminWidget;
use Charcoal\Admin\Ui\CollectionContainerInterface;
use Charcoal\Admin\Ui\CollectionContainerTrait;

/**
 * The table widget displays a collection in a tabular (table) format.
 */
class SearchWidget extends AdminWidget implements CollectionContainerInterface
{
    use CollectionContainerTrait;

    /**
     * @var array $properties
     */
    protected $properties;

    /**
     * @var $propertiesOptions
     */
    protected $propertiesOptions;

    /**
     * @param array $data The search widget data.
     * @return self
     */
    public function setData(array $data)
    {
        if (isset($data['obj_type'])) {
            $this->setObjType($data['obj_type']);
            unset($data['obj_type']);
        }

        $objData = $this->dataFromObject();
        $data    = array_merge_recursive($objData, $data);

        parent::setData($data);

        return $this;
    }

    /**
     * Fetch metadata from current obj_type
     * @return array List of metadata.
     */
    public function dataFromObject()
    {
        $obj             = $this->proto();
        $metadata        = $obj->metadata();
        $adminMetadata   = isset($metadata['admin']) ? $metadata['admin'] : null;
        $collectionIdent = $this->collectionIdent();
        if (!$collectionIdent) {
            $collectionIdent = isset($adminMetadata['default_list']) ? $adminMetadata['default_list'] : '';
        }

        if (isset($adminMetadata['lists'][$collectionIdent])) {
            return $adminMetadata['lists'][$collectionIdent];
        } else {
            return [];
        }
    }

    /**
     * Sets and returns properties
     *
     * Manages which to display, and their order, as set in object metadata
     *
     * @return array
     */
    public function properties()
    {
        if ($this->properties === null) {
            $model = $this->proto();
            $props = $model->metadata()->properties();

            $collectionIdent = $this->collectionIdent();
            if ($collectionIdent) {
                $metadata      = $model->metadata();
                $adminMetadata = isset($metadata['admin']) ? $metadata['admin'] : null;

                if (isset($adminMetadata['lists'][$collectionIdent]['properties'])) {
                    // Flipping to have property ident as key
                    $listProperties = array_flip($adminMetadata['lists'][$collectionIdent]['properties']);
                    // Replacing values of listProperties from index to actual property values
                    $props = array_replace($listProperties, $props);
                    // Get only the keys that are in listProperties from props
                    $props = array_intersect_key($props, $listProperties);
                }
            }

            $this->properties = $props;
        }

        return $this->properties;
    }

    /**
     * Retrieve the property keys to search in the collection.
     *
     * @return array
     */
    public function propertiesIdents()
    {
        $metadata = $this->proto()->metadata();
        if (isset($metadata['admin']['lists'])) {
            $adminMetadata   = $metadata['admin'];
            $collectionIdent = $this->collectionIdent();
            if (isset($adminMetadata['lists'][$collectionIdent]['properties'])) {
                return $adminMetadata['lists'][$collectionIdent]['properties'];
            }
        }

        return [];
    }

    /**
     * Retrieve the widget's data options for JavaScript components.
     *
     * @return array
     */
    public function widgetDataForJs()
    {
        return [
            'obj_type'   => $this->objType(),
            'properties' => $this->propertiesIdents()
        ];
    }
}
