<?php

namespace App\DataProvider\Entity\MobConnect\Response;

use Symfony\Component\HttpKernel\Exception\HttpException;

class IncentiveResponse extends MobConnectResponse
{
    private const EXCEPTION_CODES = [
        400 => 'The request is incorrect and cannot be processed',
        401 => 'Authentication is missing or invalid',
        403 => 'Access denied. The associated rights are insufficient',
        404 => 'The resource cannot be found',
        409 => 'A conflict exists between the request and the state of the resource',
        422 => 'The query is correct but the processing on the resource encounters semantic errors',
        500 => 'An internal error has occurred',
    ];

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $subscriptionLink;

    public function __construct($mobConnectResponse)
    {
        parent::__construct($mobConnectResponse);

        $this->_throwExceptions();

        $this->_buildResponse();
    }

    /**
     * Get the value of id.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Get the value of type.
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Get the value of title.
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Get the value of description.
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Get the value of link.
     */
    public function getSubscriptionLink(): ?string
    {
        return $this->subscriptionLink;
    }

    private function _buildResponse()
    {
        if (property_exists($this->_content, 'id')) {
            $this->_setId($this->_content->id);
        }

        if (property_exists($this->_content, 'incentiveType')) {
            $this->_setType($this->_content->incentiveType);
        }

        if (property_exists($this->_content, 'title')) {
            $this->_setTitle($this->_content->title);
        }

        if (property_exists($this->_content, 'description')) {
            $this->_setDescription($this->_content->description);
        }

        if (property_exists($this->_content, 'subscriptionLink')) {
            $this->_setSubscriptionLink($this->_content->subscriptionLink);
        }
    }

    /**
     * Set the value of id.
     */
    private function _setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set the value of type.
     */
    private function _setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Set the value of title.
     */
    private function _setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Set the value of description.
     */
    private function _setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set the value of link.
     */
    private function _setSubscriptionLink(string $subscriptionLink): self
    {
        $this->subscriptionLink = $subscriptionLink;

        return $this;
    }

    private function _throwExceptions()
    {
        if (array_key_exists($this->getCode(), self::EXCEPTION_CODES)) {
            throw new HttpException($this->getCode(), self::EXCEPTION_CODES[$this->getCode()]);
        }
    }
}
