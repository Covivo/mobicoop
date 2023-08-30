<?php

namespace App\DataProvider\Entity\MobConnect\Response;

class IncentiveResponse extends MobConnectResponse
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    public function __construct($mobConnectResponse)
    {
        parent::__construct($mobConnectResponse);

        $this->_buildResponse();
    }

    /**
     * Get the value of id.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the value of title.
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Get the value of description.
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    private function _buildResponse()
    {
        if (property_exists($this->_content, 'id')) {
            $this->_setId($this->_content->id);
        }

        if (property_exists($this->_content, 'title')) {
            $this->_setTitle($this->_content->title);
        }

        if (property_exists($this->_content, 'description')) {
            $this->_setDescription($this->_content->description);
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
}
