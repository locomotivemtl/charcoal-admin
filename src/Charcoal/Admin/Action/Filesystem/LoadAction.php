<?php

namespace Charcoal\Admin\Action\Filesystem;

use Exception;
use InvalidArgumentException;

// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

// From Pimple
use Pimple\Container;

// From Pimple
use Slim\Http\Stream;

// From 'league/flysystem'
use League\Flysystem\FileNotFoundException;

// From 'charcoal-admin'
use Charcoal\Admin\AdminAction;

/**
 * Action: Create a streamed response for a given file.
 *
 * ## Required Parameters
 *
 * - `path` (_string_) — The stored file to retrieve.
 *
 * ## Optional Parameters
 *
 * - `disk` (_string_) [config.filesystem.default_connection] — The filesystem related to the given file.
 * - `name` (_string_) — The custom file name.
 * - `disposition` (_string_) ["inline"] — How the response should be treated.
 *   Available options: "inline", "attachment".

 * ## Response
 *
 * - {@see Psr\Http\Message\StreamInterface} - A streamed response for the given file.
 *
 * ## HTTP Status Codes
 *
 * - `200` — Successful; Streamed download
 * - `400` — Client error; Invalid or malformed parameters
 * - `401` — Unauthorized
 * - `403` — Forbidden
 * - `404` - File not found
 * - `500` — Server error; File could not be downloaded
 */
class LoadAction extends AdminAction
{
    const DISPOSITION_ATTACHMENT = 'attachment';
    const DISPOSITION_INLINE     = 'inline';

    /**
     * The request parameters.
     *
     * @var array
     */
    protected $params = [];

    /**
     * Collection of filesystem adapters.
     *
     * @var \League\Flysystem\FilesystemInterface[]
     */
    protected $filesystems;

    /**
     * Store the filesystem configset.
     *
     * @var \Charcoal\App\Config\FilesystemConfig
     */
    protected $filesystemConfig;

    /**
     * Determine if user authentication is required.
     *
     * @return boolean
     */
    protected function authRequired()
    {
        return true;
    }

    /**
     * Sets the action data.
     *
     * @param  array $data The action data.
     * @return self
     */
    public function setData(array $data)
    {
        $keys = $this->validDataFromRequest();
        $data = array_intersect_key($data, array_flip($keys));
        $this->mergeData($data);

        return $this;
    }

    /**
     * Sets the action data from a PSR Request object.
     *
     * @param  RequestInterface $request A PSR-7 compatible Request instance.
     * @return self
     */
    protected function setDataFromRequest(RequestInterface $request)
    {
        $keys = $this->validDataFromRequest();
        $data = $request->getParams($keys);
        $this->mergeData($data);

        return $this;
    }

    /**
     * Add data to action, replacing existing items with the same data key.
     *
     * @param  array $data The action data.
     * @return self
     */
    public function mergeData(array $data)
    {
        $this->params = array_replace($this->params, $data);

        return $this;
    }

    /**
     * Retrieve the list of parameters to extract from the HTTP request.
     *
     * @return string[]
     */
    protected function validDataFromRequest()
    {
        return [ 'disk', 'disposition', 'path', 'name' ];
    }

    /**
     * Get the associative array of request parameters.
     *
     * @param  array|null $keys Subset of keys to retrieve.
     * @return array|null
     */
    public function getParams(array $keys = null)
    {
        $params = $this->params;

        if ($keys) {
            $subset = [];
            foreach ($keys as $key) {
                if (array_key_exists($key, $params)) {
                    $subset[$key] = $params[$key];
                }
            }
            return $subset;
        }

        return $params;
    }

    /**
     * Get the request parameter value.
     *
     * @param  string $key     The parameter key.
     * @param  string $default The default value.
     * @return mixed  The parameter value.
     */
    public function getParam($key, $default = null)
    {
        $params = $this->params;
        if (is_array($params) && isset($params[$key])) {
            $result = $params[$key];
        } else {
            if (!is_string($default) && is_callable($default)) {
                $result = $default();
            } else {
                $result = $default;
            }
        }

        return $result;
    }

    /**
     * @param  RequestInterface  $request  A PSR-7 compatible Request instance.
     * @param  ResponseInterface $response A PSR-7 compatible Response instance.
     * @throws FileNotFoundException If the requested file is NOT a file.
     * @return ResponseInterface
     */
    public function run(RequestInterface $request, ResponseInterface $response)
    {
        unset($request);

        $translator = $this->translator();

        try {
            $disk = $this->getParam('disk', $this->filesystemConfig['default_connection']);
            $this->assertValidDisk($disk);

            $disp =  $this->getParam('disposition', self::DISPOSITION_INLINE);
            $this->assertValidDisposition($disp);

            $path = $this->getParam('path');
            $this->assertValidPath($path);

            $name = $this->getParam('name');
            $this->assertValidName($name);
        } catch (InvalidArgumentException $e) {
            $this->addFeedback('error', $e->getMessage());
            $this->setSuccess(false);

            return $response->withStatus(400);
        }

        $filesystem = $this->filesystems[$disk];

        try {
            $handler = $filesystem->get($path);
            if ($handler->isFile() === false) {
                throw new FileNotFoundException($path);
            }
        } catch (FileNotFoundException $e) {
            $this->addFeedback('error', $e->getMessage());
            $this->setSuccess(false);

            return $response->withStatus(404);
        }

        try {
            $filename    = isset($name) ? $name : basename($path);
            $disposition = $this->generateHttpDisposition($disp, $filename);
            $resource    = $handler->readStream();
            $stream      = new Stream($resource);

            $this->setMode($disp);

            $this->logger->debug(sprintf(
                '[Admin] %s "%s" from "%s" storage: %s',
                ($disp === self::DISPOSITION_ATTACHMENT ? 'Downloading' : 'Reading'),
                $filename,
                $disk,
                $path
            ));

            return $response->withHeader('Content-Type', $handler->getMimetype())
                            ->withHeader('Content-Length', $handler->getSize())
                            ->withHeader('Content-Disposition', $disposition)
                            ->withBody($stream);
        } catch (Exception $e) {
            $this->logger->error(sprintf(
                '[Admin] Failed to %s "%s" from "%s" storage: %s',
                ($disp === self::DISPOSITION_ATTACHMENT ? 'download' : 'read'),
                $filename,
                $disk,
                $path
            ));

            $this->addFeedback('error', $e->getMessage());
            $this->setSuccess(false);

            return $response->withStatus(500);
        }
    }

    /**
     * Generates a HTTP 'Content-Disposition' field-value.
     *
     * Note: Adapted from Symfony\HttpFoundation.
     *
     * @see https://github.com/symfony/http-foundation/blob/master/LICENSE
     *
     * @see   RFC 6266
     * @param  string $disposition      Either "inline" or "attachment".
     * @param  string $filename         A unicode string.
     * @param  string $filenameFallback A string containing only ASCII characters that
     *     is semantically equivalent to $filename. If the filename is already ASCII,
     *     it can be omitted, or just copied from $filename.
     * @throws InvalidArgumentException If the parameters are invalid.
     * @return string A string suitable for use as a Content-Disposition field-value.
     */
    public function generateHttpDisposition($disposition, $filename, $filenameFallback = '')
    {
        if (!in_array($disposition, [ self::DISPOSITION_ATTACHMENT, self::DISPOSITION_INLINE ])) {
            throw new InvalidArgumentException(sprintf(
                'The disposition must be either "%s" or "%s".',
                self::DISPOSITION_ATTACHMENT,
                self::DISPOSITION_INLINE
            ));
        }

        if ($filenameFallback === '') {
            $filenameFallback = $filename;
        }

        // filenameFallback is not ASCII.
        // if (!preg_match('/^[\x20-\x7e]*$/', $filenameFallback)) {
        //     throw new InvalidArgumentException('The filename fallback must only contain ASCII characters.');
        // }

        // percent characters aren't safe in fallback.
        if (strpos($filenameFallback, '%') !== false) {
            throw new InvalidArgumentException('The filename fallback cannot contain the "%" character.');
        }

        // path separators aren't allowed in either.
        if (strpos($filename, '/') !== false ||
            strpos($filename, '\\') !== false ||
            strpos($filenameFallback, '/') !== false ||
            strpos($filenameFallback, '\\') !== false) {
            throw new InvalidArgumentException(
                'The filename and the fallback cannot contain the "/" and "\\" characters.'
            );
        }

        $output = sprintf('%s; filename="%s"', $disposition, str_replace('"', '\\"', $filenameFallback));

        if ($filename !== $filenameFallback) {
            $output .= sprintf("; filename*=utf-8''%s", rawurlencode($filename));
        }

        return $output;
    }

    /**
     * Asserts that the filesystem connection is valid, throws an exception if not.
     *
     * @param  mixed $disk A filesystem connection identifier.
     * @throws InvalidArgumentException If the filesystem is not a string or NULL.
     * @return void
     */
    protected function assertValidDisk($disk)
    {
        $translator = $this->translator();

        if ($disk === null) {
            $message = $translator->translate(
                'Default filesystem [{{ defaultFilesystemConnection }}] is not defined',
                [
                    '{{ defaultFilesystemConnection }}' => 'config.filesystem.default_connection'
                ]
            );

            throw new InvalidArgumentException($message, 400);
        }

        if (!is_string($disk)) {
            $actualType = is_object($disk) ? get_class($disk) : gettype($disk);
            $message = $translator->translate(
                '{{ parameter }} must be a {{ expectedType }}, received {{ actualType }}',
                [
                    '{{ parameter }}'    => '"disk"',
                    '{{ expectedType }}' => 'string',
                    '{{ actualType }}'   => $actualType,
                ]
            );

            throw new InvalidArgumentException($message, 400);
        }

        if (!isset($this->filesystems[$disk])) {
            $message = $translator->translate('Filesystem identifier "{{ fsIdent }}" is not defined.', [
                '{{ fsIdent }}' => $disk
            ]);

            throw new InvalidArgumentException($message, 400);
        }
    }

    /**
     * Asserts that the path is valid, throws an exception if not.
     *
     * @param  mixed $path A file path.
     * @throws InvalidArgumentException If the path is not a string.
     * @return void
     */
    protected function assertValidPath($path)
    {
        $translator = $this->translator();

        if (empty($path) && !is_numeric($path)) {
            $message = $translator->translate(
                '{{ parameter }} required and must be a {{ expectedType }}',
                [
                    '{{ parameter }}'    => '"path"',
                    '{{ expectedType }}' => 'string',
                ]
            );

            throw new InvalidArgumentException($message, 400);
        } elseif (!is_string($path)) {
            $actualType = is_object($path) ? get_class($path) : gettype($path);
            $message = $translator->translate(
                '{{ parameter }} must be a {{ expectedType }}, received {{ actualType }}',
                [
                    '{{ parameter }}'    => '"path"',
                    '{{ expectedType }}' => 'string',
                    '{{ actualType }}'   => $actualType,
                ]
            );

            throw new InvalidArgumentException($message, 400);
        }
    }

    /**
     * Asserts that the custom file name is valid, throws an exception if not.
     *
     * @param  mixed $name A custom file name.
     * @throws InvalidArgumentException If the name is not a string or NULL.
     * @return void
     */
    protected function assertValidName($name)
    {
        if (!is_string($name) && $name !== null) {
            $actualType = is_object($name) ? get_class($name) : gettype($name);
            $message = $this->translator()->translate(
                '{{ parameter }} must be a {{ expectedType }}, received {{ actualType }}',
                [
                    '{{ parameter }}'    => '"name"',
                    '{{ expectedType }}' => 'string',
                    '{{ actualType }}'   => $actualType,
                ]
            );

            throw new InvalidArgumentException($message, 400);
        }
    }

    /**
     * Asserts that the response disposition is valid, throws an exception if not.
     *
     * @param  mixed $disposition A response disposition.
     * @throws InvalidArgumentException If the disposition is not a string or NULL.
     * @return void
     */
    protected function assertValidDisposition($disposition)
    {
        $translator = $this->translator();

        if (!in_array($disposition, [ self::DISPOSITION_ATTACHMENT, self::DISPOSITION_INLINE ])) {
            throw new InvalidArgumentException(sprintf(
                'The disposition must be either "%s" or "%s".',
                self::DISPOSITION_ATTACHMENT,
                self::DISPOSITION_INLINE
            ));
        }

        if (!is_string($disposition) && $disposition !== null) {
            $actualType = is_object($disposition) ? get_class($disposition) : gettype($disposition);
            $message = $translator->translate(
                '{{ parameter }} must be a {{ expectedType }}, received {{ actualType }}',
                [
                    '{{ parameter }}'    => '"disposition"',
                    '{{ expectedType }}' => 'string',
                    '{{ actualType }}'   => $actualType,
                ]
            );

            throw new InvalidArgumentException($message, 400);
        }
    }

    /**
     * Set dependencies from the service locator.
     *
     * @param  Container $container A service locator.
     * @return void
     */
    protected function setDependencies(Container $container)
    {
        parent::setDependencies($container);

        $this->filesystems = $container['filesystems'];
        $this->filesystemConfig = $container['filesystem/config'];
    }
}
