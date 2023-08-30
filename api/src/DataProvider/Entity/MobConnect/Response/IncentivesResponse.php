<?php

namespace App\DataProvider\Entity\MobConnect\Response;

use App\Incentive\Resource\Incentive;
use Doctrine\Common\Collections\ArrayCollection;

class IncentivesResponse extends MobConnectResponse
{
    /**
     * @var ArrayCollection
     */
    private $incentives;

    public function __construct($mobConnectResponse)
    {
        $this->incentives = new ArrayCollection();

        parent::__construct($mobConnectResponse);

        $this->_buildResponse();
    }

    /**
     * Get the value of incentivesResponse.
     */
    public function getIncentives(): ArrayCollection
    {
        return $this->incentives;
    }

    private function _buildResponse()
    {
        if (!is_array($this->getContent() && !empty($this->getContent()))) {
            foreach ($this->getContent() as $incentive) {
                $this->_addIncentive($incentive->id, $incentive->title, $incentive->description);
            }
        }
    }

    private function _addIncentive(string $id, string $title, string $description)
    {
        $this->incentives->add(new Incentive($id, $title, $description));
    }
}
