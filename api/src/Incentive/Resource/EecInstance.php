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
     * @var null|\DateTimeInterface
     *
     * @Groups({"readEecInstance"})
     */
    private $ldExpirationDate;

    /**
     * @var null|\DateTimeInterface
     *
     * @Groups({"readEecInstance"})
     */
    private $sdExpirationDate;

    /**
     * @var null|string
     */
    private $ldSubscriptionsKey;

    /**
     * @var null|string
     */
    private $sdSubscriptionsKey;

    /**
     * @var array
     */
    private $configuration;

    public function __construct(array $subscriptionKeys, array $instanceConfiguration)
    {
        $this->configuration = $instanceConfiguration;

        $this->setSdSubscriptionsKeys($subscriptionKeys);
        $this->setExpirationDate();
        $this->setLdExpirationDate();
        $this->setSdExpirationDate();
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

    public function isLdSubscriptionAvailable(): bool
    {
        return !$this->isDateExpired($this->ldExpirationDate);
    }

    public function isSdSubscriptionAvailable(): bool
    {
        return !$this->isDateExpired($this->sdExpirationDate);
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
    public function setExpirationDate(): self
    {
        $this->expirationDate = $this->getDate($this->configuration['expirationDate']);

        return $this;
    }

    /**
     * Get the value of ldExpirationDate.
     */
    public function getLdExpirationDate(): ?\DateTimeInterface
    {
        return $this->ldExpirationDate;
    }

    /**
     * Set the value of ldExpirationDate.
     */
    public function setLdExpirationDate(): self
    {
        $this->ldExpirationDate = $this->getDate($this->configuration['subscriptions']['ld']['expirationDate']);

        return $this;
    }

    /**
     * Get the value of sdExpirationDate.
     */
    public function getSdExpirationDate(): ?\DateTimeInterface
    {
        return $this->sdExpirationDate;
    }

    /**
     * Set the value of sdExpirationDate.
     */
    public function setSdExpirationDate(): self
    {
        $this->sdExpirationDate = $this->getDate($this->configuration['subscriptions']['sd']['expirationDate']);

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

        if (!empty($this->configuration['subscriptions']['ld']['key'])) {
            $this->setLdSubscriptionsKey($this->configuration['subscriptions']['ld']['key']);
        }

        if (!empty($subscriptionKeys['sd'])) {
            $this->setSdSubscriptionsKey($subscriptionKeys['sd']);
        }

        if (!empty($this->configuration['subscriptions']['sd']['key'])) {
            $this->setLdSubscriptionsKey($this->configuration['subscriptions']['sd']['key']);
        }

        return $this;
    }

    public function areSubscriptionKeysAvailable(): bool
    {
        return !is_null($this->ldSubscriptionsKey) && !is_null($this->sdSubscriptionsKey);
    }

    private function getDate(?string $date): ?\DateTime
    {
        return !empty($date)
            ? new \DateTime($date.' 23:59:59') : null;
    }

    private function isDateExpired(?\DateTimeInterface $date): bool
    {
        if (is_null($date)) {
            return false;
        }

        $now = new \DateTime('now');

        return $now > $date;
    }

    private function _isServiceOpened(): bool
    {
        if (
            is_null($this->expirationDate)
            && is_null($this->ldExpirationDate)
            && is_null($this->sdExpirationDate)
            && $this->areSubscriptionKeysAvailable()
        ) {
            return true;
        }

        if (
            $this->areSubscriptionKeysAvailable()
            && (
                !is_null($this->expirationDate)
                || (!is_null($this->ldExpirationDate) && !is_null($this->sdExpirationDate))
                || (!is_null($this->ldExpirationDate) && is_null($this->sdExpirationDate))
                || (is_null($this->ldExpirationDate) && !is_null($this->sdExpirationDate))
            )
        ) {
            if (!is_null($this->expirationDate)) {
                return !$this->isDateExpired($this->expirationDate);
            }

            if (!is_null($this->ldExpirationDate) && !is_null($this->sdExpirationDate)) {
                return !$this->isDateExpired($this->ldExpirationDate) && !$this->isDateExpired($this->sdExpirationDate);
            }

            if (
                !is_null($this->ldExpirationDate) && is_null($this->sdExpirationDate)
                || (is_null($this->ldExpirationDate) && !is_null($this->sdExpirationDate))
            ) {
                return true;
            }
        }

        return false;
    }
}
