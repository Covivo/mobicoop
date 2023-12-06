<?php

namespace App\Incentive\Resource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * An EEC instance.
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readEecInstance"}, "enable_max_depth"=true},
 *      },
 *      collectionOperations={
 *          "get"={
 *              "path"="/eec/eec_instance",
 *              "normalization_context"={"groups"={"readEecInstance"}, "skip_null_values"=true},
 *              "swagger_context" = {
 *                  "tags"={"Subscription"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Subscription"},
 *                  "summary"="Not implemented"
 *              }
 *          }
 *      }
 * )
 *
 * @author Olivier Fillol <olivier.fillol@mobicoop.org>
 */
class EecInstance
{
    public const DEFAULT_ID = '999999999999';

    /**
     * @var int The id of this CEE subscription
     *
     * @ApiProperty(identifier=true)
     */
    private $id = self::DEFAULT_ID;

    /**
     * @var bool
     *
     * @Groups({"readEecInstance"})
     */
    private $available = false;

    /**
     * @var null|\DateTimeInterface
     *
     * @Groups({"readEecInstance"})
     */
    private $expirationDate;

    public function getId(): int
    {
        return $this->id;
    }

    public function getAvailable(): bool
    {
        return $this->available;
    }

    public function isAvailable()
    {
        return $this->getAvailable();
    }

    /**
     * Set the value of available.
     */
    public function setAvailable(bool $available): self
    {
        $this->available = $available;

        return $this;
    }

    /**
     * Get the value of expirationDate.
     */
    public function getExpirationDate(): ?\DateTimeInterface
    {
        return $this->expirationDate;
    }

    /**
     * Set the value of expirationDate.
     */
    public function setExpirationDate(?\DateTimeInterface $expirationDate): self
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }
}
