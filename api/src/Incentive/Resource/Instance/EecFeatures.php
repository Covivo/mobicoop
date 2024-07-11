<?php

namespace App\Incentive\Resource\Instance;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EecFeatures
{
    /**
     * @var array
     */
    private $_originalConfiguration;

    /**
     * @var null|bool
     */
    private $available = true;

    /**
     * @var null|bool
     */
    private $ldAvailable = true;

    /**
     * @var null|bool
     */
    private $sdAvailable = true;

    public function __construct(array $configuration)
    {
        $this->_originalConfiguration = $configuration;

        $this->_build();
    }

    /**
     * Get the value of Available.
     */
    public function getAvailable(): ?bool
    {
        return $this->available;
    }

    public function isAvailable(): ?bool
    {
        return $this->getAvailable();
    }

    /**
     * Set the value of Available.
     */
    public function setAvailable(?bool $available): self
    {
        if (!is_null($available)) {
            $this->available = $available;
        }

        return $this;
    }

    /**
     * Get the value of ldAvailable.
     */
    public function getLdAvailable(): ?bool
    {
        return $this->ldAvailable;
    }

    public function isLdAvailable(): ?bool
    {
        return $this->getLdAvailable();
    }

    /**
     * Set the value of ldAvailable.
     */
    public function setLdAvailable(?bool $ldAvailable): self
    {
        $this->ldAvailable = $ldAvailable;

        return $this;
    }

    /**
     * Get the value of sdAvailable.
     */
    public function getSdAvailable(): ?bool
    {
        return $this->sdAvailable;
    }

    public function isSdAvailable(): ?bool
    {
        return $this->getSdAvailable();
    }

    /**
     * Set the value of sdAvailable.
     */
    public function setSdAvailable(?bool $sdAvailable): self
    {
        $this->sdAvailable = $sdAvailable;

        return $this;
    }

    private function _build(): void
    {
        if (!$this->_isOriginalConfigurationCompliant()) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'The configuration of the `Features` object for a EEC instance is not compliant');
        }

        $this->setAvailable($this->_originalConfiguration['available']);
        $this->setLdAvailable($this->_originalConfiguration['ldAvailable']);
        $this->setSdAvailable($this->_originalConfiguration['sdAvailable']);
    }

    private function _isOriginalConfigurationCompliant(): bool
    {
        return
            (
                array_key_exists('available', $this->_originalConfiguration)
                && (
                    is_null($this->_originalConfiguration['available'])
                    || is_bool($this->_originalConfiguration['available'])
                )
            )
            && (
                array_key_exists('ldAvailable', $this->_originalConfiguration)
                && (
                    is_null($this->_originalConfiguration['ldAvailable'])
                    || is_bool($this->_originalConfiguration['ldAvailable'])
                )
            )
            && (
                array_key_exists('sdAvailable', $this->_originalConfiguration)
                && (
                    is_null($this->_originalConfiguration['sdAvailable'])
                    || is_bool($this->_originalConfiguration['sdAvailable'])
                )
            );
    }
}
