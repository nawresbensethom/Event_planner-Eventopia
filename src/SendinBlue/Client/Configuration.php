<?php

namespace SendinBlue\Client;

/**
 * Configuration class for SendinBlue client
 */
class Configuration
{
    private static $defaultConfiguration;
    
    protected $apiKeys = [];
    protected $apiKeyPrefix = [];
    protected $accessToken = '';
    protected $username = '';
    protected $password = '';
    protected $host = 'https://api.sendinblue.com/v3';
    protected $userAgent = 'SendinBlue API Client/PHP';
    protected $debug = false;
    protected $verifySSL = true;
    protected $timeout = 60;
    
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Gets the default configuration instance
     *
     * @return Configuration
     */
    public static function getDefaultConfiguration()
    {
        if (self::$defaultConfiguration === null) {
            self::$defaultConfiguration = new Configuration();
        }

        return self::$defaultConfiguration;
    }
    
    /**
     * Sets the detault configuration instance
     *
     * @param Configuration $config An instance of the Configuration Object
     *
     * @return void
     */
    public static function setDefaultConfiguration(Configuration $config)
    {
        self::$defaultConfiguration = $config;
    }

    /**
     * Gets the API key
     *
     * @param string $apiKeyIdentifier API key identifier (authentication scheme)
     *
     * @return string API key or token
     */
    public function getApiKey($apiKeyIdentifier)
    {
        return isset($this->apiKeys[$apiKeyIdentifier]) ? $this->apiKeys[$apiKeyIdentifier] : null;
    }

    /**
     * Sets the API key
     *
     * @param string $apiKeyIdentifier API key identifier (authentication scheme)
     * @param string $key              API key or token
     *
     * @return $this
     */
    public function setApiKey($apiKeyIdentifier, $key)
    {
        $this->apiKeys[$apiKeyIdentifier] = $key;
        return $this;
    }
} 