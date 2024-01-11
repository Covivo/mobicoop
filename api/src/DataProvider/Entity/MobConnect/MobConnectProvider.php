<?php

namespace App\DataProvider\Entity\MobConnect;

use App\DataProvider\Entity\Response;
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

    private function __buildResource(string $resource, string $resource_id = null, ?string $tag = null): string
    {
        if (strpos($resource, RouteProvider::SUBSCRIPTION_ID_TAG) || strpos($resource, RouteProvider::INCENTIVE_ID_TAG)) {
            if (is_null($resource_id)) {
                throw new BadRequestHttpException(MobConnectMessages::SUBSCRIPTION_PARAMETER_MISSING);
            }

            $resource = str_replace(
                !is_null($tag) ? $tag : RouteProvider::SUBSCRIPTION_ID_TAG,
                $resource_id,
                $resource
            );
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

    protected function _createDataProvider(string $resource, string $resource_id = null, ?string $tag = null)
    {
        $this->_dataProvider = new DataProvider($this->_apiUri, RouteProvider::buildResource($resource, $resource_id, $tag));
    }

    protected function _getResponse(Response $response)
    {
        $responseValue = $response->getValue();

        $this->_logRequestResult($response->getCode(), $responseValue);

        return [
            'code' => $response->getCode(),
            'content' => $responseValue,
        ];
    }

    private function _logRequestResult(int $code, ?string $content)
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

        // $this->_loggerService->log('The mobConnect request response is: '.$code.' | '.(is_null($content) ? '' : $content), $logType, true);
    }
}
