<?php

namespace Charcoal\Admin\Script\Tools;

// From 'pimple/pimple'
use Pimple\Container;

// From 'psr/http-message'
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;

// From 'charcoal-admin'
use Charcoal\Admin\AdminScript;

/**
 * Copy the Admin assets to a given destination.
 */
class CopyAssetsScript extends AdminScript
{
    /**
     * @var string
     */
    private $dir;

    /**
     * @return array
     */
    public function defaultArguments()
    {
        $arguments = [
            'dir' => [
                'longPrefix' => 'dir',
                'description' => 'Directory to copy files into; relative to the base path.',
                'defaultValue' => 'www/assets/admin/'
            ]
        ];

        $arguments = array_merge(parent::defaultArguments(), $arguments);
        return $arguments;
    }

    /**
     * @param RequestInterface  $request  PSR-7 request.
     * @param ResponseInterface $response PSR-7 response.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        unset($request);

        $climate = $this->climate();
        $climate->arguments->parse();

        $dirArg = $climate->arguments->get('dir');
        $dirPath = $this->basePath.$dirArg;
        $this->dir = realpath($dirPath);
        if (!$this->dir) {
            $climate->orange('Directory does not exist. Creating it…');
            mkdir($dirPath, null, true);
            $this->dir = realpath($dirPath);
        }

        $climate->underline()->out(
            sprintf('Copying admin assets into "%s".', $dirArg)
        );

        $climate->orange(
            'WARNING: this script is destructive and will overwrite existing files in the specified directory.'
        );
        $climate->orange('Make sure you know what you are doing and have a backup in case things go wrong.');

        $climate->out('Directory: '.$this->dir);

        $input = $climate->input('Continue?');
        $input->accept(['y', 'n'], true);
        $res = $input->prompt();
        if ($res !== 'y') {
            return $response;
        }

        /**
         * @todo Store the Charcoal Admin package base directory somewhere.
         * @see \Charcoal\Admin\Config::L50 for similar relative path usage.
         */
        $assetsDirectory = realpath(__DIR__.'/../../../../../assets/dist');

        $this->copy($assetsDirectory, $this->dir);

        return $response;
    }

    /**
     * @param Container $container Pimple DI Container.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->basePath = $container['config']['basePath'];
    }

    /**
     * Copy a file, or recursively copy a folder and its contents
     *
     * @author      Aidan Lister <aidan@php.net>
     * @version     1.0.1
     * @link        http://aidanlister.com/2004/04/recursively-copying-directories-in-php/
     * @param       string  $source      Source path.
     * @param       string  $dest        Destination path.
     * @param       integer $permissions New folder creation permissions.
     * @return      boolean     Returns true on success, false on failure.
     */
    private function copy($source, $dest, $permissions = 0755)
    {
        // Check for symlinks
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }

        // Simple copy for a file
        if (is_file($source)) {
            return copy($source, $dest);
        }

        // Make destination directory
        if (!is_dir($dest)) {
            mkdir($dest, $permissions, true);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Deep copy directories
            $this->copy($source.'/'.$entry, $dest.'/'.$entry, $permissions);
        }

        // Clean up
        $dir->close();
        return true;
    }
}
