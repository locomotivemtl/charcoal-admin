<?php

namespace Charcoal\Admin\Action\Tinymce;

use Charcoal\Admin\AdminAction;
use Pimple\Container;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Action : Upload an image and return path.
 */
class UploadImageAction extends AdminAction
{
    const DEFAULT_PUBLIC_ACCESS = true;
    const DEFAULT_UPLOAD_PATH = 'uploads/tinymce/';
    const DEFAULT_FILESYSTEM = 'public';
    const DEFAULT_OVERWRITE = true;

    /**
     * Whether uploaded files should be accessible from the web root.
     *
     * @var boolean
     */
    private $publicAccess = self::DEFAULT_PUBLIC_ACCESS;

    /**
     * The relative path to the storage directory.
     *
     * @var string
     */
    private $uploadPath = self::DEFAULT_UPLOAD_PATH;

    /**
     * Whether existing destinations should be overwritten.
     *
     * @var boolean
     */
    private $overwrite = self::DEFAULT_OVERWRITE;

    /**
     * The base path for the Charcoal installation.
     *
     * @var string
     */
    private $basePath;

    /**
     * The path to the public / web directory.
     *
     * @var string
     */
    private $publicPath;

    /**
     * @var string
     */
    private $uploadedPath;

    /**
     * Inject dependencies from a DI Container.
     *
     * @param  Container $container A dependencies container instance.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->basePath   = $container['config']['base_path'];
        $this->publicPath = $container['config']['public_path'];
    }

    /**
     * Gets a psr7 request and response and returns a response.
     *
     * Called from `__invoke()` as the first thing.
     *
     * @param RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param ResponseInterface $response A PSR-7 compatible Response instance.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        $path = $request->getParam('upload_path');

        if (!!$path) {
            $this->setUploadPath($path);
        }

        $this->uploadedPath = $this->fileUpload($_FILES['file']);

        $this->setSuccess(!!$this->uploadedPath);

        return $response;
    }

    /**
     * Upload to filesystem.
     *
     * @param array $fileData The file data (from $_FILES, typically).
     * @throws \InvalidArgumentException If the FILES data argument is missing `name` or `tmp_name`.
     * @return string
     */
    public function fileUpload(array $fileData)
    {
        if (!isset($fileData['name'])) {
            throw new \InvalidArgumentException(
                'File data is invalid'
            );
        }

        $target = $this->uploadTarget($fileData['name']);

        $ret = move_uploaded_file($fileData['tmp_name'], $target);

        if ($ret === false) {
            $this->logger->warning(sprintf('Could not upload file %s', $target));

            return '';
        } else {
            $this->logger->notice(sprintf('File %s uploaded succesfully', $target));
            $basePath = $this->basePath();
            $target   = str_replace($basePath, '', $target);

            return $target;
        }
    }

    /**
     * @param string $filename Optional. The filename to save. If unset, a default filename will be generated.
     * @throws \Exception If the target path is not writeable.
     * @return string
     */
    public function uploadTarget($filename = null)
    {
        $basePath = $this->basePath();

        $dir      = $basePath.$this->uploadPath();
        $filename = ($filename) ? $this->sanitizeFilename($filename) : 'unnamed_file';

        error_log(var_export($dir, true));
        if (!file_exists($dir)) {
            // @todo: Feedback
            $this->logger->debug(
                'Path does not exist. Attempting to create path '.$dir.'.',
                [get_called_class().'::'.__FUNCTION__]
            );
            mkdir($dir, 0777, true);
        }
        if (!is_writable($dir)) {
            throw new \exception(
                'Error: upload directory is not writeable'
            );
        }

        $target = $dir.$filename;

        if ($this->fileExists($target)) {
            if ($this->overwrite() === true) {
                return $target;
            } else {
                $target = $dir.$this->generateUniqueFilename($filename);
                while ($this->fileExists($target)) {
                    $target = $dir.$this->generateUniqueFilename($filename);
                }
            }
        }

        return $target;
    }

    /**
     * Checks whether a file or directory exists.
     *
     * PHP built-in's `file_exists` is only case-insensitive on case-insensitive filesystem (such as Windows)
     * This method allows to have the same validation across different platforms / filesystem.
     *
     * @param  string  $file            The full file to check.
     * @param  boolean $caseInsensitive Case-insensitive by default.
     * @return boolean
     */
    public function fileExists($file, $caseInsensitive = true)
    {
        if (!$this->isAbsolutePath($file)) {
            $file = $this->basePath().$file;
        }

        if (file_exists($file)) {
            return true;
        }

        if ($caseInsensitive === false) {
            return false;
        }

        $files = glob(dirname($file).DIRECTORY_SEPARATOR.'*', GLOB_NOSORT);
        if ($files) {
            $pattern = preg_quote($file, '#');
            foreach ($files as $f) {
                if (preg_match("#{$pattern}#i", $f)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Determine if the given file path is am absolute path.
     *
     * Note: Adapted from symfony\filesystem.
     *
     * @see https://github.com/symfony/symfony/blob/v3.2.2/LICENSE
     *
     * @param  string $file A file path.
     * @return boolean Returns TRUE if the given path is absolute. Otherwise, returns FALSE.
     */
    protected function isAbsolutePath($file)
    {
        return strspn($file, '/\\', 0, 1)
            || (strlen($file) > 3
                && ctype_alpha($file[0])
                && substr($file, 1, 1) === ':'
                && strspn($file, '/\\', 2, 1))
            || null !== parse_url($file, PHP_URL_SCHEME);
    }

    /**
     * Sanitize a filename by removing characters from a blacklist and escaping dot.
     *
     * @param string $filename The filename to sanitize.
     * @return string The sanitized filename.
     */
    public function sanitizeFilename($filename)
    {
        // Remove blacklisted caharacters
        $blacklist = ['/', '\\', '\0', '*', ':', '?', '"', '<', '>', '|', '#', '&', '!', '`', ' '];
        $filename  = str_replace($blacklist, '_', $filename);

        // Avoid hidden file
        $filename = ltrim($filename, '.');

        return $filename;
    }

    /**
     * Generate a unique filename.
     *
     * @param  string|array $filename The filename to alter.
     * @throws \InvalidArgumentException If the given filename is invalid.
     * @return string
     */
    public function generateUniqueFilename($filename)
    {
        if (!is_string($filename) && !is_array($filename)) {
            throw new \InvalidArgumentException(sprintf(
                'The target must be a string or an array from [pathfino()], received %s',
                (is_object($filename) ? get_class($filename) : gettype($filename))
            ));
        }

        if (is_string($filename)) {
            $info = pathinfo($filename);
        } else {
            $info = $filename;
        }

        $filename = $info['filename'].'-'.uniqid();

        if (isset($info['extension']) && $info['extension']) {
            $filename .= '.'.$info['extension'];
        }

        return $filename;
    }

    /**
     * @return string
     */
    public function uploadPath()
    {
        return $this->uploadPath;
    }

    /**
     * Set the destination (directory) where uploaded files are stored.
     *
     * The path must be relative to the {@see self::basePath()},
     *
     * @param string $path The destination directory, relative to project's root.
     * @throws \InvalidArgumentException If the path is not a string.
     * @return self
     */
    public function setUploadPath($path)
    {
        if (!is_string($path)) {
            throw new \InvalidArgumentException(
                'Upload path must be a string'
            );
        }

        // Sanitize upload path (force trailing slash)
        $this->uploadPath = rtrim($path, '/').'/';

        return $this;
    }

    /**
     * Set whether uploaded files should be publicly available.
     *
     * @param boolean $public Whether uploaded files should be accessible (TRUE) or not (FALSE) from the web root.
     * @return self
     */
    public function setPublicAccess($public)
    {
        $this->publicAccess = !!$public;

        return $this;
    }

    /**
     * Determine if uploaded files should be publicly available.
     *
     * @return boolean
     */
    public function publicAccess()
    {
        return $this->publicAccess;
    }

    /**
     * Set whether existing destinations should be overwritten.
     *
     * @param boolean $overwrite Whether existing destinations should be overwritten (TRUE) or not (FALSE).
     * @return self
     */
    public function setOverwrite($overwrite)
    {
        $this->overwrite = !!$overwrite;

        return $this;
    }

    /**
     * Determine if existing destinations should be overwritten.
     *
     * @return boolean
     */
    public function overwrite()
    {
        return $this->overwrite;
    }

    /**
     * Retrieve the path to the storage directory.
     *
     * @return string
     */
    protected function basePath()
    {
        if ($this->publicAccess()) {
            return $this->publicPath;
        } else {
            return $this->basePath;
        }
    }

    /**
     * Default response stub.
     *
     * @return array
     */
    public function results()
    {
        return [
            'success'  => $this->success(),
            'location' => $this->uploadedPath
        ];
    }
}