<?php

namespace App\DataProvider\Entity\MobConnect\Response;

use App\Incentive\Resource\Incentive;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpKernel\Exception\HttpException;

class IncentivesResponse extends MobConnectResponse
{
    private const EXCEPTION_CODES = [
        400 => 'The request is incorrect and cannot be processed',
        401 => 'Authentication is missing or invalid',
        403 => 'Access denied. The associated rights are insufficient',
        404 => 'The resource cannot be found',
        409 => 'A conflict exists between the request and the state of the resource',
        422 => 'The query is correct but the processing on the resource encounters semantic errors',
        500 => 'An internal error has occurred',
    ];

    /**
     * @var ArrayCollection
     */
    private $incentives;

    public function __construct($mobConnectResponse)
    {
        $this->incentives = new ArrayCollection();

        parent::__construct($mobConnectResponse);

        $this->_throwExceptions();

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
                $this->_addIncentive($incentive->id, $incentive->incentiveType, $incentive->title, $incentive->description);
            }
        }
    }

    private function _addIncentive(string $id, string $type, string $title, string $description)
    {
        $this->incentives->add(new Incentive($id, $type, $title, $description, null));
    }

    private function _throwExceptions()
    {
        if (array_key_exists($this->getCode(), self::EXCEPTION_CODES)) {
            throw new HttpException($this->getCode(), self::EXCEPTION_CODES[$this->getCode()]);
        }
    }
}
