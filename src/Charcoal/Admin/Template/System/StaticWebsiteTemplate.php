<?php

namespace Charcoal\Admin\Template\System;

use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\AdminTemplate;
use Charcoal\Admin\Support\Formatter;

/**
 *
 */
class StaticWebsiteTemplate extends AdminTemplate
{
    /**
     * @var string
     */
    private $basePath;

    /**
     * Retrieve the title of the page.
     *
     * @return \Charcoal\Translator\Translation|string|null
     */
    public function title()
    {
        if ($this->title === null) {
            $this->setTitle($this->translator()->translation('Static Website'));
        }

        return $this->title;
    }

    /**
     * Retrieve the secondary menu.
     *
     * @return \Charcoal\Admin\Widget\SecondaryMenuWidgetInterface|null
     */
    public function secondaryMenu()
    {
        if ($this->secondaryMenu === null) {
            $this->secondaryMenu = $this->createSecondaryMenu('system');
        }

        return $this->secondaryMenu;
    }

    /**
     * @return boolean
     */
    public function isStaticWebsiteEnabled()
    {
        return file_exists($this->basePath.'/www/static');
    }

    /**
     * @return \Generator
     */
    public function staticWebsiteFiles()
    {
        $files = $this->globRecursive($this->basePath.'cache/static', 'index.*');
        foreach ($files as $file) {
            yield [
                'file'      => $file,
                'name'      => dirname(str_replace($this->basePath.'cache/static/', '', $file)),
                'size'      => Formatter::formatBytes(filesize($file)),
                'mtime'     => date(DATE_ATOM, filemtime($file)),
                'generated' => date('Y-m-d H:i:s', filemtime($file)),
                'type'      => pathinfo($file, PATHINFO_EXTENSION)
            ];
        }
    }

    /**
     * @param Container $container Pimple DI Container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);
        $this->basePath = $container['config']['base_path'];
    }

    /**
     * @param string  $dir     Initial directory.
     * @param string  $pattern File patter.
     * @param integer $flags   Glob flags.
     * @return array
     */
    private function globRecursive($dir, $pattern, $flags = 0)
    {
        $files = glob($dir.'/'.$pattern, $flags);
        foreach (glob($dir.'/*', (GLOB_ONLYDIR|GLOB_NOSORT)) as $dir) {
            $files = array_merge($files, $this->globRecursive($dir, $pattern, $flags));
        }
        return $files;
    }
}
