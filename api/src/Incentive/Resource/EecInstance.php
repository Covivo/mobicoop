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
     * @var bool
     *
     * @Groups("readEecInstance")
     */
    private $ldAvailable;

    /**
     * @var null|\DateTimeInterface
     *
     * @Groups({"readEecInstance"})
     */
    private $ldExpirationDate;

    /**
     * @var bool
     *
     * @Groups("readEecInstance")
     */
    private $sdAvailable;

    /**
     * @var null|\DateTimeInterface
     *
     * @Groups({"readEecInstance"})
     */
    private $sdExpirationDate;

    /**
     * @var null|string
     */
    private $ldKey;

    /**
     * @var null|string
     */
    private $sdKey;

    /**
     * @var ?bool
     *
     * @Groups("readEecInstance")
     */
    private $tabView;

    /**
     * @var null|string
     */
    private $carpoolProofPrefix;

    /**
     * @var array
     */
    private $configuration;

    public function __construct(array $instanceConfiguration, ?string $carpoolProofPrefix)
    {
        $this->configuration = $instanceConfiguration;
        $this->carpoolProofPrefix = $carpoolProofPrefix;

        $this->setKeys();
        $this->setExpirationDate();
        $this->setLdExpirationDate();
        $this->setSdExpirationDate();
        $this->setAvailable($this->_isServiceOpened());
        $this->setTabView();
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

    public function isLdAvailable(): bool
    {
        return !is_null($this->ldKey) && !$this->isDateExpired($this->getLdExpirationDate());
    }

    public function isSdAvailable(): bool
    {
        return !is_null($this->sdKey) && !$this->isDateExpired($this->getSdExpirationDate());
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
    public function getLdKey(): ?string
    {
        return $this->ldKey;
    }

    /**
     * Set the value of ldSubscriptionsKey.
     */
    public function setLdKey(?string $key): self
    {
        $this->ldKey = $key;

        return $this;
    }

    /**
     * Get the value of sdSubscriptionsKey.
     */
    public function getSdKey(): ?string
    {
        return $this->sdKey;
    }

    /**
     * Set the value of sdSubscriptionsKey.
     */
    public function setSdKey(?string $key): self
    {
        $this->sdKey = $key;

        return $this;
    }

    public function setKeys(): self
    {
        $this->setLdKey($this->configuration['subscriptions']['ld']['key']);
        $this->setSdKey($this->configuration['subscriptions']['sd']['key']);

        return $this;
    }

    public function isTabView(): bool
    {
        return $this->tabView;
    }

    /**
     * Get the value of carpoolProofPrefix.
     */
    public function getCarpoolProofPrefix(): ?string
    {
        return $this->carpoolProofPrefix;
    }

    private function setTabView(): self
    {
        $this->tabView =
            is_null($this->configuration)
            || !isset($this->configuration['tabView'])
            || is_null($this->configuration['tabView'])
            ? false : $this->configuration['tabView'];

        return $this;
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
            && (
                $this->isLdAvailable() && $this->isSdAvailable()
                || $this->isLdAvailable() && !$this->isSdAvailable()
                || !$this->isLdAvailable() && $this->isSdAvailable()
            )
        ) {
            return true;
        }

        if (
            !is_null($this->expirationDate)
            && (
                $this->isLdAvailable() && $this->isSdAvailable()
                || $this->isLdAvailable() && !$this->isSdAvailable()
                || !$this->isLdAvailable() && $this->isSdAvailable()
            )
        ) {
            return !$this->isDateExpired($this->expirationDate);
        }

        return false;
    }
}
