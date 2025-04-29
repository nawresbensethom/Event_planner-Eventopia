<?php

namespace GuzzleHttp;

/**
 * GuzzleHttp Client
 */
class Client
{
    /**
     * @var array
     */
    protected $config;

    /**
     * Constructor
     *
     * @param array $config Client configuration settings
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Get the client configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Send an HTTP request
     *
     * @param string $method HTTP method
     * @param string $uri URI
     * @param array $options Request options
     * @return ResponseInterface
     */
    public function request($method, $uri, array $options = [])
    {
        // Simplified implementation
        return new Response(200);
    }
}

/**
 * Simple Response implementation
 */
class Response
{
    /**
     * @var int
     */
    protected $statusCode;

    /**
     * Constructor
     *
     * @param int $statusCode
     */
    public function __construct($statusCode = 200)
    {
        $this->statusCode = $statusCode;
    }

    /**
     * Get the status code
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
} 