<?php

namespace Mobicoop\Bundle\MobicoopBundle\Territory\Entity;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;

class Territory implements ResourceInterface, \JsonSerializable
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $geoJsonDetail;

    /**
     * @var int
     */
    private $adminLevel;

    /**
     * @var string
     */
    private $minLatitude;

    /**
     * @var string
     */
    private $maxLatitude;

    /**
     * @var string
     */
    private $minLongitude;

    /**
     * @var string
     */
    private $maxLongitude;

    /**
     * @var \DateTimeInterface
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface
     */
    private $updatedDate;

    /**
     * Get the value of id.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set the value of id.
     *
     * @param mixed $id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name.
     *
     * @param mixed $name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of geoJsonDetail.
     *
     * @return string
     */
    public function getGeoJsonDetail()
    {
        return $this->geoJsonDetail;
    }

    /**
     * Set the value of geoJsonDetail.
     *
     * @return self
     */
    public function setGeoJsonDetail(string $geoJsonDetail)
    {
        $this->geoJsonDetail = $geoJsonDetail;

        return $this;
    }

    /**
     * Get the value of adminLevel.
     */
    public function getAdminLevel()
    {
        return $this->adminLevel;
    }

    /**
     * Set the value of adminLevel.
     *
     * @param mixed $adminLevel
     *
     * @return self
     */
    public function setAdminLevel($adminLevel)
    {
        $this->adminLevel = $adminLevel;

        return $this;
    }

    /**
     * Get the value of minLatitude.
     */
    public function getMinLatitude()
    {
        return $this->minLatitude;
    }

    /**
     * Set the value of minLatitude.
     *
     * @param mixed $minLatitude
     *
     * @return self
     */
    public function setMinLatitude($minLatitude)
    {
        $this->minLatitude = $minLatitude;

        return $this;
    }

    /**
     * Get the value of maxLatitude.
     */
    public function getMaxLatitude()
    {
        return $this->maxLatitude;
    }

    /**
     * Set the value of maxLatitude.
     *
     * @param mixed $maxLatitude
     *
     * @return self
     */
    public function setMaxLatitude($maxLatitude)
    {
        $this->maxLatitude = $maxLatitude;

        return $this;
    }

    /**
     * Get the value of minLongitude.
     */
    public function getMinLongitude()
    {
        return $this->minLongitude;
    }

    /**
     * Set the value of minLongitude.
     *
     * @param mixed $minLongitude
     *
     * @return self
     */
    public function setMinLongitude($minLongitude)
    {
        $this->minLongitude = $minLongitude;

        return $this;
    }

    /**
     * Get the value of maxLongitude.
     */
    public function getMaxLongitude()
    {
        return $this->maxLongitude;
    }

    /**
     * Set the value of maxLongitude.
     *
     * @param mixed $maxLongitude
     *
     * @return self
     */
    public function setMaxLongitude($maxLongitude)
    {
        $this->maxLongitude = $maxLongitude;

        return $this;
    }

    /**
     * Get the value of createdDate.
     *
     * @return \DateTimeInterface
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * Set the value of createdDate.
     *
     * @return self
     */
    public function setCreatedDate(\DateTimeInterface $createdDate)
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    /**
     * Get the value of updatedDate.
     *
     * @return \DateTimeInterface
     */
    public function getUpdatedDate()
    {
        return $this->updatedDate;
    }

    /**
     * Set the value of updatedDate.
     *
     * @return self
     */
    public function setUpdatedDate(\DateTimeInterface $updatedDate)
    {
        $this->updatedDate = $updatedDate;

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'geoJsonDetail' => $this->getGeoJsonDetail(),
            'adminLevel' => $this->getAdminLevel(),
            'minLatitude' => $this->getMinLatitude(),
            'maxLatitude' => $this->getMaxLatitude(),
            'minLongitude' => $this->getMinLongitude(),
            'maxLongitude' => $this->getMaxLongitude(),
            'createdDate' => $this->getCreatedDate(),
            'updatedDate' => $this->getUpdatedDate(),
        ];
    }
}
