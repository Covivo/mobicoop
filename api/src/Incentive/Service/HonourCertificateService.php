<?php

namespace App\Incentive\Service;

use App\User\Entity\User;

class HonourCertificateService
{
    public const HONOUR_CERTIFICATE_PATH = __DIR__.'/../Resource/certificates';
    public const HONOUR_CERTIFICATE_EXTENSION = '.txt';
    public const LONG_DISTANCE_SPECIFIC_PATH = '/long-distance-honour-certificate';
    public const SHORT_DISTANCE_SPECIFIC_PATH = '/short-distance-honour-certificate';

    /**
     * @var User
     */
    private $_driver;

    /**
     * @var bool
     */
    private $_longDistance;

    /**
     * @var string
     */
    private $_certificate;

    public function __construct()
    {
    }

    public function generateHonourCertificate(bool $longDistance = true): string
    {
        $this->_longDistance = $longDistance;

        $this->_getCertificate();
        $this->_parseCertificate();

        return $this->_certificate;
    }

    public function setDriver(User $driver): self
    {
        $this->_driver = $driver;

        return $this;
    }

    private function _getCertificate()
    {
        $filename = self::HONOUR_CERTIFICATE_PATH;

        switch ($this->_longDistance) {
            case false:
                $filename .= self::SHORT_DISTANCE_SPECIFIC_PATH;

                break;

            default:
                $filename .= self::LONG_DISTANCE_SPECIFIC_PATH;

                break;
        }

        $this->_certificate = file_get_contents($filename.self::HONOUR_CERTIFICATE_EXTENSION);

        return $this->_certificate;
    }

    private function _parseCertificate()
    {
        $now = new \DateTime();

        $fields = [
            'NomConducteur' => $this->_driver->getFamilyName(),
            'PrenomConducteur' => $this->_driver->getGivenName(),
            'AdresseConducteur' => !is_null($this->_driver->getHomeAddress()) ? $this->_driver->getHomeAddress()->getStreetAddress() : null,
            'ComplementAdresseConducteur' => null,
            'CodePostalConducteur' => !is_null($this->_driver->getHomeAddress()) ? $this->_driver->getHomeAddress()->getPostalCode() : null,
            'VilleConducteur' => !is_null($this->_driver->getHomeAddress()) ? $this->_driver->getHomeAddress()->getAddressLocality() : null,
            'PaysConducteur' => !is_null($this->_driver->getHomeAddress()) ? $this->_driver->getHomeAddress()->getAddressCountry() : null,
            'TelephoneConducteur' => $this->_driver->getTelephone(),
            'CourrielConducteur' => $this->_driver->getEmail(),
            'DateAttestationHonneur' => $now->format('d-m-Y'),
        ];

        foreach ($fields as $field => $value) {
            $this->_certificate = str_replace('{{'.$field.'}}', $value, $this->_certificate);
        }

        return $this->_certificate;
    }
}
