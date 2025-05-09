<?php

/**
 * Copyright (c) 2025, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 */

namespace App\DataProvider\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\DataProvider\Ressource\Hook;
use App\DataProvider\Ressource\StripeHook;
use App\Payment\Service\PaymentManager;
use Psr\Log\LoggerInterface;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Security;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class StripeHookDataPersister implements ContextAwareDataPersisterInterface
{
    private $paymentManager;
    private $security;
    private $requestStack;
    private $logger;
    private $_webhookSecret;

    public function __construct(PaymentManager $paymentManager, RequestStack $requestStack, Security $security, LoggerInterface $logger, string $webhookSecret)
    {
        $this->paymentManager = $paymentManager;
        $this->security = $security;
        $this->requestStack = $requestStack;
        $this->logger = $logger;
        $this->_webhookSecret = $webhookSecret;
    }

    public function __invoke(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->headers->get('stripe-signature');

        return $this->persist('stripe_webhook');
    }

    public function supports($data, array $context = []): bool
    {
        return 'stripe_webhook' === $data && 'stripe_webhook' === $context['collection_operation_name'];
    }

    public function persist($data, array $context = [])
    {
        try {
            $request = $this->requestStack->getCurrentRequest();
            if (!$request) {
                throw new BadRequestHttpException('Invalid request.');
            }

            $payload = $request->getContent();
            $signature = $request->headers->get('stripe-signature');

            $decodedPayload = json_decode($payload, true);
            $this->logger->info($decodedPayload['type']);

            if (!$this->_checkWebhookSecret($signature, $payload)) {
                return new Response('Invalid webhook signature', Response::HTTP_OK);
            }

            switch ($decodedPayload['type']) {
                case StripeHook::TYPE_ACCOUNT_UPDATED:
                    $this->logger->info($payload);
                    $this->_treatValidatedHook($decodedPayload);

                    break;

                case StripeHook::TYPE_PAYMENT_SUCCEEDED:
                    $this->logger->info($payload);
                    $this->_treatPaymentSucceedHook($decodedPayload);

                    break;

                default:
                    break;
            }

            // Retourner une réponse HTTP 200 OK
            return new Response('Webhook received', Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error('Error processing webhook: '.$e->getMessage());

            return new Response('Error processing webhook', Response::HTTP_BAD_REQUEST);
        }
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }

    protected function _checkWebhookSecret($signature, $payload): bool
    {
        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                $this->_webhookSecret
            );
        } catch (SignatureVerificationException $e) {
            $this->logger->error('Invalid webhook signature: '.$e->getMessage());

            return false;
        }

        return true;
    }

    private function _treatPaymentSucceedHook($decodedPayload)
    {
        if (isset($decodedPayload['data']['object']['payment_link'])) {
            $hook = new StripeHook();
            $hook->setStatus(Hook::STATUS_SUCCESS);
            $hook->setRessourceId($decodedPayload['data']['object']['payment_link']);

            $this->paymentManager->handleHookPayIn($hook);
        }
    }

    private function _treatValidatedHook($decodedPayload)
    {
        if (isset($decodedPayload['data']['object']['individual']['verification']['status'])
            && StripeHook::VALIDATION_SUCCEEDED == $decodedPayload['data']['object']['individual']['verification']['status']
            && isset($decodedPayload['data']['object']['individual']['verification']['document']['front'])
            && !is_null($decodedPayload['data']['object']['individual']['verification']['document']['front'])) {
            $hook = new StripeHook();
            $hook->setStatus(Hook::STATUS_SUCCESS);
            $hook->setRessourceId($decodedPayload['data']['object']['individual']['verification']['document']['front']);

            $this->paymentManager->handleHookValidation($hook);
        }
    }
}
