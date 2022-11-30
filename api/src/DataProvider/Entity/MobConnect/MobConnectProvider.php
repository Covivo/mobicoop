<?php

namespace App\DataProvider\Entity\MobConnect;

use App\DataProvider\Entity\Response as ProviderResponse;
use App\DataProvider\Service\DataProvider;
use App\Incentive\Service\MobConnectMessages;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

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

        switch ($response->getCode()) {
            case 200:
                return json_decode($responseValue);

            case 400:
                throw new BadRequestHttpException($responseValue);

            case 401:
                throw new AccessDeniedHttpException($responseValue);

            case 403:
                throw new HttpException(Response::HTTP_FORBIDDEN, $responseValue);

            case 404:
                throw new NotFoundHttpException($responseValue);

            case 412:
                throw new PreconditionFailedHttpException($responseValue);

            case 415:
                throw new UnsupportedMediaTypeHttpException($responseValue);

            case 422:
                throw new UnprocessableEntityHttpException($responseValue);

            default:
                throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'The MobConnect API response is unknown!');
        }
    }
}
