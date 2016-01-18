<?php

namespace Charcoal\Admin;

use \InvalidArgumentException;

// From `charcoal-core`
use \Charcoal\Config\AbstractConfig;

/**
*
*/
class Config extends AbstractConfig
{
    const DEFAULT_BASE_PATH = 'admin';

    /**
    * @var string $_basePath
    */
    private $basePath = self::DEFAULT_BASE_PATH;

    /**
    * The default data is defined in a JSON file.
    *
    * @return array
    */
    public function defaults()
    {
        $file_content = file_get_contents(realpath(__DIR__.'/../../../config').'/admin.config.default.json');
        $config = json_decode($file_content, true);
        return $config;
    }

    /**
    * @param string $path
    * @throws InvalidArgumentException
    * @return Config Chainable
    */
    public function setBasePath($path)
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException(
                'Path must be a string'
            );
        }
        // Can not be empty
        if ($path == '') {
            throw new InvalidArgumentException(
                'Path can not be empty'
            );
        }
        $this->basePath = $path;
        return $this;
    }

    public function basePath()
    {
        return $this->basePath;
    }

}
