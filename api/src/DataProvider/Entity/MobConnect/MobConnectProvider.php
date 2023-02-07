<?php

namespace App\DataProvider\Entity\MobConnect;

use App\DataProvider\Entity\Response as ProviderResponse;
use App\DataProvider\Service\DataProvider;
use App\Incentive\Service\LoggerService;
use App\Incentive\Service\MobConnectMessages;
use App\User\Entity\User;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * MobConnect provider.
 *
 * @author Olivier FILLOL <olivier.fillol@mobicoop.org>
 */
abstract class MobConnectProvider
{
    public const SUBSCRIPTION_ID_TAG = '{SUBSCRIPTION_ID}';

    /**
     * The Data provider.
     *
     * @var DataProvider
     */
    protected $_dataProvider;

    /**
     * @var string
     */
    protected $_apiUri;

    /**
     * @var LoggerService
     */
    protected $_loggerService;

    /**
     * The authenticated user.
     *
     * @var User
     */
    protected $_user;

    private function __buildResource(string $resource, string $subscriptionId = null): string
    {
        if (strpos($resource, self::SUBSCRIPTION_ID_TAG)) {
            if (is_null($subscriptionId)) {
                throw new BadRequestHttpException(MobConnectMessages::SUBSCRIPTION_PARAMETER_MISSING);
            }

            $resource = str_replace(self::SUBSCRIPTION_ID_TAG, $subscriptionId, $resource);
        }

        return $resource;
    }

    protected function _buildHeaders(string $token = null): array
    {
        $headers = [];

        if (!is_null($token)) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        return $headers;
    }

    protected function _createDataProvider(string $resource, string $subscriptionId = null)
    {
        $this->_dataProvider = new DataProvider($this->_apiUri, $this->__buildResource($resource, $subscriptionId));
    }

    protected function _getResponse(ProviderResponse $response)
    {
        $responseValue = $response->getValue();

        $this->_logRequestResult($response->getCode(), $responseValue);

        return [
            'code' => $response->getCode(),
            'content' => $responseValue,
        ];
    }

    private function _logRequestResult(int $code, string $content)
    {
        switch ($code) {
            case 200:
            case 201:
            case 204:
                $logType = 'info';

                break;

            default:
                $logType = 'error';

                break;
        }

        $this->_loggerService->log('The mobConnect request response is: '.$code.' | '.$content, $logType, true);
    }
}
