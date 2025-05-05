<?php

namespace SendinBlue\Client\Model;

/**
 * SendSmtpEmail
 */
class SendSmtpEmail
{
    /**
     * @var array
     */
    protected $to = [];

    /**
     * @var string
     */
    protected $htmlContent = '';

    /**
     * @var string
     */
    protected $subject = '';

    /**
     * @var array
     */
    protected $sender = [];

    /**
     * Constructor
     *
     * @param array $data Initial data
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $property => $value) {
            $method = 'set' . str_replace('_', '', ucwords($property, '_'));
            
            if (method_exists($this, $method)) {
                $this->$method($value);
            } else {
                $this->$property = $value;
            }
        }
    }

    /**
     * Set the recipients
     *
     * @param array $to
     * @return $this
     */
    public function setTo(array $to)
    {
        $this->to = $to;
        return $this;
    }

    /**
     * Get the recipients
     *
     * @return array
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Set the HTML content
     *
     * @param string $htmlContent
     * @return $this
     */
    public function setHtmlContent($htmlContent)
    {
        $this->htmlContent = $htmlContent;
        return $this;
    }

    /**
     * Get the HTML content
     *
     * @return string
     */
    public function getHtmlContent()
    {
        return $this->htmlContent;
    }

    /**
     * Set the subject
     *
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Get the subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set the sender
     *
     * @param array $sender
     * @return $this
     */
    public function setSender(array $sender)
    {
        $this->sender = $sender;
        return $this;
    }

    /**
     * Get the sender
     *
     * @return array
     */
    public function getSender()
    {
        return $this->sender;
    }
} 