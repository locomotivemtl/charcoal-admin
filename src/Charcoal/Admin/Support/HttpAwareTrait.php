<?php

namespace Charcoal\Admin\Support;

use RuntimeException;

// From PSR-7
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * PSR-7 Awareness
 */
trait HttpAwareTrait
{
    /**
     * Store the HTTP request object.
     *
     * @var RequestInterface
     */
    protected $httpRequest;

    /**
     * Store the HTTP response object.
     *
     * @var ResponseInterface
     */
    protected $httpResponse;

    /**
     * Retrieve the HTTP request.
     *
     * @throws RuntimeException If the HTTP request was not previously set.
     * @return RequestInterface
     */
    public function httpRequest()
    {
        if ($this->httpRequest === null) {
            throw new RuntimeException(sprintf(
                'PSR-7 HTTP Request is not defined for "%s"',
                get_class($this)
            ));
        }

        return $this->httpRequest;
    }

    /**
     * Determine if a HTTP request object is set.
     *
     * @return boolean
     */
    public function hasHttpRequest()
    {
        return $this->httpRequest instanceof RequestInterface;
    }

    /**
     * Set an HTTP request object.
     *
     * @param  RequestInterface $request A PSR-7 compatible Request instance.
     * @return void
     */
    protected function setHttpRequest(RequestInterface $request)
    {
        $this->httpRequest = $request;
    }

    /**
     * Retrieve the HTTP response.
     *
     * @throws RuntimeException If the HTTP response was not previously set.
     * @return ResponseInterface
     */
    public function httpResponse()
    {
        if ($this->httpResponse === null) {
            throw new RuntimeException(sprintf(
                'PSR-7 HTTP Response is not defined for "%s"',
                get_class($this)
            ));
        }

        return $this->httpResponse;
    }

    /**
     * Determine if a HTTP response object is set.
     *
     * @return boolean
     */
    public function hasHttpResponse()
    {
        return $this->httpResponse instanceof ResponseInterface;
    }

    /**
     * Set an HTTP response object.
     *
     * @param  ResponseInterface $response A PSR-7 compatible Response instance.
     * @return void
     */
    protected function setHttpResponse(ResponseInterface $response)
    {
        $this->httpResponse = $response;
    }

    /**
     * Update the action's {@see ResponseInterface} with the specified status code and,
     * optionally, reason phrase.
     *
     * @param integer $code         The 3-digit integer result code to set.
     * @param string  $reasonPhrase The reason phrase to use with the
     *     provided status code; if none is provided, implementations MAY
     *     use the defaults as suggested in the HTTP specification.
     * @return self
     */
    protected function updateHttpResponseStatus($code, $reasonPhrase = '')
    {
        $this->httpResponse = $this->httpResponse->withStatus($code, $reasonPhrase);

        return $this;
    }

    /**
     * Is this response successful?
     *
     * @return boolean
     */
    protected function isHttpResponseSuccessful()
    {
        $response = $this->httpResponse();

        return ($response->isSuccessful() || $response->isInformational());
    }
}
