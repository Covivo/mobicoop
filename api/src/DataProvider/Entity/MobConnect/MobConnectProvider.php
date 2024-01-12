<?php

namespace App\DataProvider\Entity\MobConnect;

use App\DataProvider\Entity\Response;
use App\DataProvider\Service\DataProvider;
use App\User\Entity\User;

/**
 * MobConnect provider.
 *
 * @author Olivier FILLOL <olivier.fillol@mobicoop.org>
 */
abstract class MobConnectProvider
{
    /**
     * @var string
     */
    protected $_apiUri;

    /**
     * The Data provider.
     *
     * @var DataProvider
     */
    protected $_dataProvider;

    /**
     * The authenticated user.
     *
     * @var User
     */
    protected $_user;

    protected function _buildHeaders(string $token = null): array
    {
        $headers = [];

        if (!is_null($token)) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        return $headers;
    }

    protected function _createDataProvider(string $resource, string $resource_id = null, ?string $tag = null)
    {
        $this->_dataProvider = new DataProvider($this->_apiUri, RouteProvider::buildResource($resource, $resource_id, $tag));
    }

    protected function _getResponse(Response $response)
    {
        $responseValue = $response->getValue();

        return [
            'code' => $response->getCode(),
            'content' => $responseValue,
        ];
    }
}
