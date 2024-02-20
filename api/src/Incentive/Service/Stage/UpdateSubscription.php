<?php

namespace App\Incentive\Service\Stage;

use App\Carpool\Entity\Ask;
use App\Incentive\Service\HonourCertificateService;
use App\Payment\Entity\CarpoolItem;

abstract class UpdateSubscription extends Stage
{
    /**
     * @var bool
     */
    protected $_pushOnlyMode;

    /**
     * @var HonourCertificateService
     */
    protected $_honorCertificateService;

    protected function _build()
    {
        $this->_setApiProvider();

        $this->_honorCertificateService = new HonourCertificateService();
    }

    protected function getCarpoolersNumber(?Ask $ask): int
    {
        if (is_null($ask)) {
            return 0;
        }

        $conn = $this->_em->getConnection();

        $sql = 'SELECT DISTINCT ci.debtor_user_id FROM carpool_item ci WHERE ci.ask_id = '.$ask->getId().'';

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return count($stmt->fetchAll(\PDO::FETCH_COLUMN)) + 1;
    }

    protected function getAddressesLocality(CarpoolItem $carpoolItem): array
    {
        $addresses = [
            'origin' => null,
            'destination' => null,
        ];

        $waypoints = $carpoolItem->getAsk()->getMatching()->getWaypoints();

        foreach ($carpoolItem->getAsk()->getMatching()->getWaypoints() as $waypoint) {
            if (0 === $waypoint->getPosition() && !$waypoint->isDestination()) {
                $addresses['origin'] = $waypoint->getAddress()->getAddressLocality();
            }
            if ($waypoint->isDestination()) {
                $addresses['destination'] = $waypoint->getAddress()->getAddressLocality();
            }
        }

        return $addresses;
    }
}
