<?php

namespace App\Incentive\Resource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Incentive\Interfaces\EecProviderInterface;
use App\Incentive\Resource\Provider\MobConnectProvider;
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
     * @var null|int
     *
     * @Groups("readEecInstance")
     */
    private $previousPeriodWithoutTravel;

    /**
     * @var null|\DateTimeInterface
     *
     * @Groups("readEecInstance")
     */
    private $beginDateOfPeriodWithoutJourney;

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
     * @var EecProviderInterface
     */
    private $provider;

    /**
     * @var int
     */
    private $ldMinimalDistance;

    public function __construct(array $instanceConfiguration, ?string $carpoolProofPrefix)
    {
        $this->_build($instanceConfiguration, $carpoolProofPrefix);
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

    public function isLongDistanceSubscriptionAvailable(): bool
    {
        return !$this->isDateExpired($this->ldExpirationDate);
    }

    public function isShortDistanceSubscriptionAvailable(): bool
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
    public function setExpirationDate(?string $expirationDate): self
    {
        $this->expirationDate = $this->getDate($expirationDate);

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
    public function setLdExpirationDate(?string $expirationDate): self
    {
        $this->ldExpirationDate = $this->getDate($expirationDate);

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
    public function setSdExpirationDate(?string $expirationDate): self
    {
        $this->sdExpirationDate = $this->getDate($expirationDate);

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

    public function setKeys(string $ldKey, string $sdKey): self
    {
        $this->setLdKey($ldKey);
        $this->setSdKey($sdKey);

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

    /**
     * Get the value of previousPeriodWithoutTravel.
     *
     * @return null|int
     */
    public function getPreviousPeriodWithoutTravel()
    {
        return $this->previousPeriodWithoutTravel;
    }

    public function getBeginDateOfPeriodWithoutJourney(): ?\DateTimeInterface
    {
        if (is_null($this->previousPeriodWithoutTravel)) {
            return new \DateTime('1900-01-01');
        }

        $now = new \DateTime();
        $beginDate = clone $now;

        return $beginDate->sub(new \DateInterval('P'.$this->previousPeriodWithoutTravel.'M'));
    }

    /**
     * Get the value of provider.
     */
    public function getProvider(): EecProviderInterface
    {
        return $this->provider;
    }

    /**
     * Get the value of ldMinimalDistance.
     */
    public function getLdMinimalDistance(): int
    {
        return $this->ldMinimalDistance;
    }

    /**
     * Set the value of ldMinimalDistance.
     */
    private function setLdMinimalDistance(int $ldMinimalDistance): self
    {
        $this->ldMinimalDistance = $ldMinimalDistance;

        return $this;
    }

    /**
     * Set the value of provider.
     */
    private function setProvider(array $provider): self
    {
        $this->provider = new MobConnectProvider($provider);

        return $this;
    }

    /**
     * Set the value of previousPeriodWithoutTravel.
     */
    private function setPreviousPeriodWithoutTravel(int $period): self
    {
        $this->previousPeriodWithoutTravel = $period;

        return $this;
    }

    private function setTabView(bool $tabView): self
    {
        $this->tabView = is_null($tabView) || false === $tabView ? false : true;

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

    private function _build(array $configuration, string $carpoolProofPrefix): self
    {
        $this->setKeys($configuration['subscriptions']['ld']['key'], $configuration['subscriptions']['sd']['key']);
        $this->setExpirationDate($configuration['expirationDate']);
        $this->setLdExpirationDate($configuration['subscriptions']['ld']['expirationDate']);
        $this->setSdExpirationDate($configuration['subscriptions']['sd']['expirationDate']);
        $this->setAvailable($this->_isServiceOpened());
        $this->setPreviousPeriodWithoutTravel($configuration['previousPeriodWithoutTravel']);
        $this->setTabView($configuration['tabView']);
        $this->setProvider($configuration['provider']);
        $this->setLdMinimalDistance($configuration['subscriptions']['ld']['minimalDistance']);

        $this->carpoolProofPrefix = $carpoolProofPrefix;

        return $this;
    }
}
