<?php

namespace App\Incentive\Resource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * An EEC instance. This resource gives general informations for the EEC service and the current instance.
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readEecInstance"}, "enable_max_depth"=true},
 *      },
 *      collectionOperations={
 *          "get"={
 *              "path"="/eec/instance",
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

    /**
     * @var null|string
     */
    private $ldSubscriptionsKey;

    /**
     * @var null|string
     */
    private $sdSubscriptionsKey;

    public function __construct(array $subscriptionKeys, string $expirationDate)
    {
        $this->setSdSubscriptionsKeys($subscriptionKeys);
        $this->setExpirationDate($expirationDate);
        $this->setAvailable($this->_isServiceOpened());
    }

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
    public function setExpirationDate(?string $expirationDate): self
    {
        if (!empty($expirationDate)) {
            $this->expirationDate = new \DateTime($expirationDate.' 23:59:59');
        }

        return $this;
    }

    /**
     * Get the value of ldSubscriptionsKey.
     */
    public function getLdSubscriptionsKey(): ?string
    {
        return $this->ldSubscriptionsKey;
    }

    /**
     * Set the value of ldSubscriptionsKey.
     */
    public function setLdSubscriptionsKey(string $ldSubscriptionsKey): self
    {
        $this->ldSubscriptionsKey = $ldSubscriptionsKey;

        return $this;
    }

    /**
     * Get the value of sdSubscriptionsKey.
     */
    public function getSdSubscriptionsKey(): ?string
    {
        return $this->sdSubscriptionsKey;
    }

    /**
     * Set the value of sdSubscriptionsKey.
     */
    public function setSdSubscriptionsKey(string $sdSubscriptionsKey): self
    {
        $this->sdSubscriptionsKey = $sdSubscriptionsKey;

        return $this;
    }

    public function setSdSubscriptionsKeys(array $subscriptionKeys): self
    {
        if (!empty($subscriptionKeys['ld'])) {
            $this->setLdSubscriptionsKey($subscriptionKeys['ld']);
        }

        if (!empty($subscriptionKeys['sd'])) {
            $this->setSdSubscriptionsKey($subscriptionKeys['sd']);
        }

        return $this;
    }

    public function areSubscriptionKeysAvailable(): bool
    {
        return !is_null($this->ldSubscriptionsKey) && !is_null($this->sdSubscriptionsKey);
    }

    private function _isServiceOpened(): bool
    {
        if (is_null($this->expirationDate) && $this->areSubscriptionKeysAvailable()) {
            return true;
        }

        if (!is_null($this->expirationDate) && $this->areSubscriptionKeysAvailable()) {
            $now = new \DateTime('now');

            return $now < $this->expirationDate;
        }

        return false;
    }
}
