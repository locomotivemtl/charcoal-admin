<?php

namespace Charcoal\Admin\Script\Tools;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use Pimple\Container;

use Charcoal\Admin\AdminScript;

/**
 *
 */
class ResizeImagesScript extends AdminScript
{
    /**
     * @var string
     */
    private $basePath;

    /**
     * @var integer
     */
    private $maxWidth;

    /**
     * @var integer
     */
    private $maxHeight;

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
            'width' => [
                'longPrefix' => 'width',
                'description' => 'Maximum width, in pixels.',
                'defaultValue' => 1920
            ],
            'height' => [
                'longPrefix' => 'height',
                'description'   => 'Maximum height, in pixels.',
                'defaultValue'  => 0
            ],
            'dir' => [
                'longPrefix' => 'dir',
                'description' => 'Directory (relative) to process.',
                'defaultValue' => 'www/uploads/'
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

        $this->setVerbose($climate->arguments->get('verbose'));
        $this->maxWidth = $climate->arguments->get('width');
        $this->maxHeight = $climate->arguments->get('height');
        $dir = $climate->arguments->get('dir');
        $this->dir = realpath($this->basePath.$dir);
        if (!$this->dir) {
            $climate->error('Invalid directory');
            return $response;
        }

        $climate->underline()->out(
            sprintf('Resize images ("%s")', $dir)
        );

        if ($this->verbose()) {
            $climate->orange('WARNING: this script is destructive and can easily be misused to lose image data.');
            $climate->orange('Make sure you know what you are doing and have a backup in case things go wrong.');

            $climate->out('Directory: '.$this->dir);
            $climate->out('Max width: '.$this->maxWidth);
            $climate->out('Max height: '.$this->maxHeight);

            $input = $climate->input('Continue?');
            $input->accept(['y', 'n'], true);
            $res = $input->prompt();
            if ($res !== 'y') {
                return $response;
            }
        }

        if ($this->maxWidth <= 0 && $this->maxHeight <= 0) {
            $climate->error('Nothing to do.');
            return $response;
        }

        $identifyCmd = $this->findCmd('identify');
        $mogrifyCmd = $this->findCmd('mogrify');
        if (!$identifyCmd || !$mogrifyCmd) {
            $climate->error('Can not find imagegick binaries');
        }

        if ($this->maxWidth > 0) {
            $cmdName = sprintf(
                'cd %s && \
            find -type f \( \
                -name "*.jpg" -o \
                -name "*.JPG" -o \
                -name "*.jpeg" -o \
                -name "*.JPEG" -o \
                -name "*.png" -o \
                -name "*.PNG" \
            \) -exec \
                %s -format "%s" {} \; | \
                awk \'$1 > \'%s\' { sub(/^[^ ]* [^ ]* /, ""); print }\' | \
                tr \'\n\' \'\0\' | \
                xargs -0  %s -resize %sx;',
                $this->dir,
                $identifyCmd,
                '%w %h %i',
                $this->maxWidth,
                $mogrifyCmd,
                $this->maxWidth
            );
            if ($this->verbose()) {
                $climate->out($cmdName);
            }
            $climate->out(shell_exec($cmdName));
        }

        if ($this->maxHeight > 0) {
            $cmdName = sprintf(
                'cd %s && \
            find -type f \( \
                -name "*.jpg" -o \
                -name "*.JPG" -o \
                -name "*.jpeg" -o \
                -name "*.JPEG" -o \
                -name "*.png" -o \
                -name "*.PNG" \
            \) -exec %s -format "%s" {} \; | \
                awk \'$2 > \'%s\' { sub(/^[^ ]* [^ ]* /, ""); print }\' | \
                tr \'\n\' \'\0\' | \
                xargs -0  %s -resize x%s;',
                $this->dir,
                $identifyCmd,
                '%w %h %i',
                $this->maxHeight,
                $mogrifyCmd,
                $this->maxHeight
            );
            if ($this->verbose()) {
                $climate->out($cmdName);
            }
            $climate->out(shell_exec($cmdName));
        }


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
     * @param string $cmdName The binary name to search.
     * @return string
     */
    private function findCmd($cmdName)
    {
        $cmd = exec('type -p '.$cmdName);
        $cmd = str_replace($cmdName.' is ', '', $cmd);
        if (!$cmd) {
            $cmd = exec('where '.$cmdName);
        }
        if (!$cmd) {
            $cmd = exec('which '.$cmdName);
        }
        if (!$cmd) {
            return '';
        }
        return $cmd;
    }
}
