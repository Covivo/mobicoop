<?php

namespace App\Tests\Mocks;

use App\DataProvider\Entity\MobConnect\Response\MobConnectSubscriptionResponse as ResponseMobConnectSubscriptionResponse;

class MobConnectSubscriptionResponseMock
{
    public static function getResponse(): ResponseMobConnectSubscriptionResponse
    {
        return new ResponseMobConnectSubscriptionResponse([
            'code' => 200,
            'content' => '{"id":  "jsdfluhgsdg65616"}',
        ]);
    }
}
