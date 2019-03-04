<?php

namespace Charcoal\Admin\Template\System\Object;

use Charcoal\Model\Service\CollectionLoader;
use Charcoal\Model\Service\MetadataLoader;
use Exception;
use ReflectionClass;
use ReflectionObject;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\AdminTemplate;
use Charcoal\Admin\Ui\DashboardContainerInterface;
use Charcoal\Admin\Ui\DashboardContainerTrait;
use Charcoal\Admin\Ui\ObjectContainerInterface;
use Charcoal\Admin\Ui\ObjectContainerTrait;

/**
 * Object Info Template
 */
class InfoTemplate extends AdminTemplate implements
    DashboardContainerInterface,
    ObjectContainerInterface
{
    use DashboardContainerTrait;
    use ObjectContainerTrait;

    /**
     * @var array
     */
    private $metadataFiles;

    /**
     * @return Generator
     */
    public function objProperties()
    {
        $ret = [];
        $properties = $this->obj()->metadata()->properties();
        foreach ($properties as $ident => $property) {
            $allSources = $this->getAllFiles($ident);
            $property['ident'] = $ident;
            $property['metadataSource'] = $this->getFirstFile($ident);
            $property['allSources'] = $allSources;
            $property['hasMoreSource'] = (count($allSources) > 1);
            $ret[] = $property;
        }
        usort($ret, function($a, $b) {
            $ret = strcmp($a['metadataSource'], $b['metadataSource']);
            if ($ret === 0) {
                return strcmp($a['ident'], $b['ident']);
            } else {
                return $ret;
            }
        });
        return $ret;
    }

    /**
     * @return string
     */
    public function className()
    {
        return get_class($this->obj());
    }

    /**
     * @return array
     */
    public function classHierarchy()
    {
        $ret = [];
        $ret = array_merge($ret, array_keys(class_parents($this->obj())));
        $ret = array_reverse($ret);
        return $ret;
    }

    /**
     * @return array
     */
    public function classTraits()
    {
        $traits = [];
        $hierarchy = $this->classHierarchy();
        foreach ($hierarchy as $className) {
            $reflection = new ReflectionClass($className);
            $traits = array_merge($traits, array_keys($reflection->getTraits()));
        }
        sort($traits);
        return $traits;
    }

    /**
     * @return array
     */
    public function classInterfaces()
    {
        $reflection = new ReflectionClass(get_class($this->obj()));
        $interfaces = array_keys($reflection->getInterfaces());
        sort($interfaces);
        return $interfaces;
    }

    /**
     * @return array
     */
    public function metadataFiles()
    {
        if ($this->metadataFiles === null) {
            $files = [];
            $reflector = new ReflectionObject($this->metadataLoader);
            $method = $reflector->getMethod('hierarchy');
            $method->setAccessible(true);
            $hierarchy = $method->invoke($this->metadataLoader, $this->objType());

            $method2 = $reflector->getMethod('loadMetadataFromSource');
            $method2->setAccessible(true);
            foreach ($hierarchy as $source) {
                $ret = $method2->invoke($this->metadataLoader, $source);
                if (!empty($ret)) {
                    $files[] = [
                        'name' => $source,
                        'metadata' => $ret
                    ];
                }
            }
            $this->metadataFiles = $files;
        }
        return $this->metadataFiles;
    }

    /**
     * @return string
     */
    public function sourceType()
    {
        return $this->obj()->source()->config()->type();
    }

    /**
     * @return string
     */
    public function sourceTable()
    {
        return $this->obj()->source()->table();
    }

    /**
     * @return string
     */
    public function sourceEntries()
    {
        $this->collectionLoader->setModel(get_class($this->obj()));
        return $this->collectionLoader->loadCount();
    }

    /**
     * Retrieve the list of parameters to extract from the HTTP request.
     *
     * @return string[]
     */
    protected function validDataFromRequest()
    {
        return array_merge([
            'obj_type', 'obj_id'
        ], parent::validDataFromRequest());
    }

    /**
     * @param Container $container DI container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        // Required ObjectContainerInterface dependencies
        $this->setModelFactory($container['model/factory']);
        $this->metadataLoader = $container['metadata/loader'];

        $this->dashboardBuilder = $container['dashboard/builder'];

        $this->collectionLoader = $container['model/collection/loader'];
    }

    /**
     * @return array
     */
    protected function createDashboardConfig()
    {
        return [];
    }

    /**
     * @param string $propertyIdent The property ident to retrieve.
     * @return string
     */
    private function getFirstFile($propertyIdent)
    {
        $all = $this->getAllFiles($propertyIdent);
        if (isset($all[0])) {
            return $all[0];
        } else {
            return '';
        }
    }

    /**
     * @param string $propertyIdent The property ident to retrieve.
     * @return array
     */
    private function getAllFiles($propertyIdent)
    {
        $ret = [];
        $files = $this->metadataFiles();
        foreach ($files as $val) {
            if (isset($val['metadata']['properties'][$propertyIdent])) {
                $ret[] = $val['name'];
            }
        }
        return $ret;
    }
}
