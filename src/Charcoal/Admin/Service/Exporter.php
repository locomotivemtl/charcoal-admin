<?php

namespace Charcoal\Admin\Service;

use \Exception;

use \SplTempFileObject;

// charcoal-core dependencie
use \Charcoal\Loader\CollectionLoader;

use Charcoal\Factory\FactoryInterface;

use \Charcoal\Translation\TranslationString;

// LeagueCSV
use League\Csv\Reader;
use League\Csv\Writer;

/**
 * Admin base exporter
 * Can export data for a given object
 * Uses a config similar to "lists" config.
 * In fact, it can actually use the "lists" config as it is
 * if the export data is a ident.
 *
 */
class Exporter
{
    /**
     * AppConfig (from dependencies.)
     * @var $appConfig
     */
    private $appConfig;

    /**
     * Output file name
     * @var string $filename
     */
    private $filename;

    /**
     * Obj type.
     * @var string $objType
     */
    private $objType;

    /**
     * Export ident for metadata
     * @var string $exportIdent
     */
    private $exportIdent;

    /**
     * Options
     * Booleans
     * @var boolean $convertBrToNewlines
     */
    private $convertBrToNewlines;

    /**
     * Options
     * Booleans
     * @var boolean $stripTags
     */
    private $stripTags;

    /**
     * Model factory
     * @var FactoryInterface $modelFactory
     */
    private $modelFactory;

    /**
     * Output properties
     * @var array $properties
     */
    private $properties;

    /**
     * CollectionConfig
     * @var array $collectionConfig
     */
    private $collectionConfig;

    /**
     * Object type proto
     * @var Model $proto
     */
    private $proto;

    /**
     * Actual object collection
     * @var Collection $collection
     */
    private $collection;

    /**
     * Property factory used
     * to display val.
     * @var PropertyFactory $propertyFactory
     */
    private $propertyFactory;

    /**
     * @param array $data Dependencies.
     * @throws Exception If missing dependencies.
     * @return Exporter Chainable
     */
    public function __construct(array $data)
    {
        if (!isset($data['config'])) {
            throw new Exception('AppConfig must be defined in the Exporter constructor.');
        }
        if (!isset($data['factory'])) {
            throw new Exception('Model Factory must be defined in the Exporter constructor.');
        }
        if (!isset($data['logger'])) {
            throw new Exception('You must set the logger in the Exporter Constructor.');
        }
        if (isset($data['obj_type'])) {
            $this->setObjType($data['obj_type']);
        }
        if (isset($data['export_ident'])) {
            $this->setExportIdent($data['export_ident']);
        }
        $this->setPropertyFactory($data['propertyFactory']);

        $this->logger = $data['logger'];
        $this->setModelFactory($data['factory']);
        $this->setAppConfig($data['config']);

        return $this;
    }
    /**
     * @param FactoryInterface $factory The property factory, to create properties.
     * @return TableWidget Chainable
     */
    protected function setPropertyFactory(FactoryInterface $factory)
    {
        $this->propertyFactory = $factory;
        return $this;
    }

    /**
     * @throws Exception If the property factory was not previously set / injected.
     * @return FactoryInterface
     */
    public function propertyFactory()
    {
        if ($this->propertyFactory === null) {
            throw new Exception(
                'Property factory is not set for table widget'
            );
        }
        return $this->propertyFactory;
    }

    /**
     * Init function
     * @return Exporter Chainable.
     */
    public function process()
    {
        $this->prepareOptions();
        $this->export();
        return $this;
    }

    /**
     * Object type proto
     * @throws Exception If no object type is defined.
     * @return mixed Object type proto | false.
     */
    private function proto()
    {
        if ($this->proto) {
            return $this->proto;
        }

        if (!$this->objType()) {
            throw new Exception('You must define an object type for the exporter.');
        }

        $this->proto = $this->modelFactory()->get($this->objType());
        return $this->proto;
    }

    /**
     * Object metadata
     * @return mixed Object metadata.
     */
    private function metadata()
    {
        $proto = $this->proto();
        return $this->proto()->metadata();
    }

    /**
     * Set all data from the metadata.
     * @throws Exception If no export ident is specified or found.
     * @throws Exception If no export data is found.
     * @throws Exception If no properties are defined.
     * @return Exporter Chainable.
     */
    private function prepareOptions()
    {
        $metadata = $this->metadata();

        // Can be override from the outside.
        if (!$this->exportIdent()) {
            $exportIdent = $this->exportIdent() ? : $this->metadata()->get('admin.default_export');
            if (!$exportIdent) {
                throw new Exception(
                    'No export ident defined for '.$this->objType().' in Charcoal\\Admin\\Helper\\Exporter'
                );
            }

            $this->setExportIdent($exportIdent);
        }

        $export = $metadata->get('admin.export.'.$this->exportIdent());
        if (!$export) {
            throw new Exception(
                'No export data defined for '.$this->objType().'
                 at ident '.$this->exportIdent().' in Charcoal\\Admin\\Helper\\Exporter'
            );
        }

        if (is_string($export)) {
            $export = $metadata->get('admin.lists.'.$export);
            if (!$export) {
                throw new Exception(
                    'No export data defined for '.$this->objType().' in Charcoal\\Admin\\Helper\\Exporter'
                );
            }
        }

        if (!isset($export['properties'])) {
            throw new Exception(
                'No properties defined to export '.$this->objType().' in Charcoal\\Admin\\Helper\\Exporter'
            );
        }

        if (isset($export['exporter_options'])) {
            $opts = $export['exporter_options'];
            if (isset($opts['convert_br_to_newlines'])) {
                $this->setConvertBrToNewlines($opts['convert_br_to_newlines']);
            }
            if (isset($opts['strip_tags'])) {
                $this->setStripTags($opts['strip_tags']);
            }
            if (isset($opts['filename'])) {
                $this->setFilename($opts['filename']);
            }
        }

        // Default filename.
        // Filename is not a requirement
        if (!$this->filename()) {
            $this->setFilename('export.csv');
        }

        // Properties to be exported
        // They will defined the file header and the rows.
        $this->setProperties($export['properties']);

        // Unnecessary for collection config.
        unset($export['properties']);
        unset($export['exporter_options']);

        // The rest is just collection config
        $this->setCollectionConfig($export);

        return $this;
    }

    /**
     * Actual object collection
     * @throws Exception If collection config is not set.
     * @return Collection Collection from the export config.
     */
    public function collection()
    {
        if ($this->collection) {
            return $this->collection;
        }

        if (!$this->collectionConfig()) {
            throw new Exception(
                'No collection config set for '.$this->objType().' in Charcoal\\Admin\\Helper\\Exporter'
            );
        }

        $collection = new CollectionLoader([
            'logger' => $this->logger,
            'factory' => $this->modelFactory()
        ]);

        $collection->setModel($this->proto());
        $collection->setData($this->collectionConfig());

        $this->collection = $collection->load();

        return $this->collection;
    }


    /**
     * @return array File headers.
     */
    private function fileHeaders()
    {
        $metadata = $this->metadata();
        $properties = $this->properties();

        $out = [];
        foreach ($properties as $p) {
            // $p = property ident.
            $prop = $metadata->get('properties.'.$p);
            if (!$prop) {
                continue;
            }

            if (isset($prop['label'])) {
                $label = new TranslationString($prop['label']);
                $label = (string)$label;
            } else {
                $label = ucfirst($p);
            }
            $out[] = $label;
        }

        return $out;
    }

    /**
     * CSV rows from collection
     * @return Array Rows with data from collection.
     */
    private function rows()
    {
        $collection = $this->collection();
        $properties = $this->properties();
        $metadata = $this->metadata();

        foreach ($collection as $c) {
            $row = [];
            foreach ($properties as $p) {
                // Use the property factory to get
                // the proper val to output in csv
                // as a string.
                $propertyMetadata = $metadata->get('properties.'.$p);
                $prop = $this->propertyFactory()->create($propertyMetadata['type']);
                $prop->setIdent($p);
                $prop->setData($propertyMetadata);
                $row[] = $this->stripContent($prop->displayVal($c->propertyValue($p)));
            }
            yield $row;
        }
    }

    /**
     * Export to CSV
     * @return void
     */
    public function export()
    {
        $headers = $this->fileHeaders();
        $rows = $this->rows();

        $writer = Writer::createFromFileObject(new SplTempFileObject());
        $writer->setNewline("\r\n");
        $writer->setOutputBOM(Writer::BOM_UTF8);
        $writer->insertOne($headers);

        foreach ($rows as $r) {
            $writer->insertOne($r);
        }

        $writer->output($this->filename());
    }


/**
 * SETTERS
 */

    /**
     * Porject app config
     * @param mixed $cfg Project'S app config.
     * @return Exporter Chainable.
     */
    private function setAppConfig($cfg)
    {
        $this->appConfig = $cfg;
        return $this;
    }

    /**
     * @param string $filename Output filename.
     * @return Exporter (chainable).
     */
    private function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * @param string $objType Object to be exported.
     * @return Exporter (chainable).
     */
    private function setObjType($objType)
    {
        $this->objType = $objType;
        return $this;
    }

    /**
     * Set the model factory
     * @param FactoryInterface $factory Model factory.
     * @return Exporter (chainable)
     */
    private function setModelFactory(FactoryInterface $factory)
    {
        $this->modelFactory = $factory;
        return $this;
    }

    /**
     * Convert br to newline?
     * @param boolean $bool Convert br to newline.
     * @return Exporter (chainable)
     */
    private function setConvertBrToNewlines($bool)
    {
        $this->convertBrToNewlines = $bool;
        return $this;
    }

    /**
     * Strip tags?
     * @param boolean $bool Strip tags.
     * @return Exporter (chainable)
     */
    private function setStripTags($bool)
    {
        $this->stripTags = $bool;
        return $this;
    }

    /**
     * The export config ident
     * Public - Called from the exportAction when a given
     * ident is provided
     * @param string $ident Config ident.
     * @return Exporter (chainable)
     */
    public function setExportIdent($ident)
    {
        $this->exportIdent = $ident;
        return $this;
    }

    /**
     * Output properties
     * @param array $properties Properties.
     * @return Exporter Chainable.
     */
    private function setProperties(array $properties)
    {
        $this->properties = $properties;
        return $this;
    }

    /**
     * Collection config
     * @param array $cfg Collection config.
     * @return Exporter Chainable.
     */
    private function setCollectionConfig(array $cfg)
    {
        $this->collectionConfig = $cfg;
        return $this;
    }


/**
 * GETTERS
 */

    /**
     * @return AppConfig AppConfig.
     */
    private function appConfig()
    {
        return $this->appConfig;
    }

    /**
     * @return string Desired filename.
     */
    private function filename()
    {
        return $this->filename;
    }

    /**
     * @return string Current object type.
     */
    private function objType()
    {
        return $this->objType;
    }

    /**
     * @return ModelFactory Model factory.
     */
    private function modelFactory()
    {
        return $this->modelFactory;
    }

    /**
     * @return boolean Convert to newlines.
     */
    private function convertBrToNewlines()
    {
        return $this->convertBrToNewlines;
    }

    /**
     * @return boolean Striptags.
     */
    private function stripTags()
    {
        return $this->stripTags;
    }

    /**
     * @return string Export ident.
     */
    private function exportIdent()
    {
        return $this->exportIdent;
    }

    /**
     * @return array Properties.
     */
    private function properties()
    {
        return $this->properties;
    }

    /**
     * @return array CollectionConfig.
     */
    private function collectionConfig()
    {
        return $this->collectionConfig;
    }


/**
 * UTILS
 */
    /**
     * Change BR into newlines.
     * @param  string $text Text.
     * @return string       Text with newlines.
     */
    private function brToNewline($text)
    {
        $breaks = ['<br />','<br>','<br/>'];
        $text = str_ireplace($breaks, "\r\n", $text);
        return $text;
    }

    /**
     * Clean output content.
     * @param  string $text Text to be stripped.
     * @return string       Stripped text.
     */
    private function stripContent($text)
    {
        if ($this->convertBrToNewlines()) {
            $text = $this->brToNewline($text);
        }
        if ($this->stripTags()) {
            $text = strip_tags($text);
        }
        return $text;
    }
}
