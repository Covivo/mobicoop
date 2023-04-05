<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\MassCommunication\Admin\Service;

use App\Communication\Entity\Medium;
use App\Communication\Repository\MediumRepository;
use App\MassCommunication\CampaignProvider\SendinBlueProvider;
use App\MassCommunication\Entity\Campaign;
use App\MassCommunication\Entity\Delivery;
use App\MassCommunication\Entity\Recipient;
use App\MassCommunication\Entity\Sender;
use App\MassCommunication\Exception\CampaignException;
use App\MassCommunication\Ressource\MassCommunicationHook;
use App\User\Entity\User;
use App\User\Exception\UserNotFoundException;
use App\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Campaign manager service in administration context.
 */
class CampaignManager
{
    public const MAIL_PROVIDER_SENDINBLUE = 'SendinBlue';

    public const MODE_TEST = 1;
    public const MODE_PROD = 2;

    public const MODES = [
        self::MODE_TEST,
        self::MODE_PROD,
    ];

    private $templating;
    private $translator;
    private $entityManager;
    private $mediumRepository;
    private $userRepository;
    private $mailerProvider;
    private $mailerDomain;
    private $mailerIp;
    private $mailerReplyTo;
    private $mailerSenderEmail;
    private $mailerSenderName;
    private $massEmailProvider;
    private $massEmailProviderIpRange;
    private $massSmsProvider;

    /**
     * Constructor.
     */
    public function __construct(
        Environment $templating,
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        MediumRepository $mediumRepository,
        UserRepository $userRepository,
        string $mailTemplate,
        string $mailerProvider,
        array $mailerProviderIpRange,
        string $mailerApiKey,
        string $mailerClientName,
        int $mailerClientId,
        string $mailerClientTemplateId,
        string $mailerReplyTo,
        string $mailerSenderEmail,
        string $mailerSenderName,
        string $mailerDomain,
        string $mailerIp,
        string $smsProvider
    ) {
        $this->templating = $templating;
        $this->translator = $translator;
        $this->entityManager = $entityManager;
        $this->mediumRepository = $mediumRepository;
        $this->userRepository = $userRepository;
        $this->mailTemplate = $mailTemplate;
        $this->mailerProvider = $mailerProvider;
        $this->mailerClientName = $mailerClientName;
        $this->mailerDomain = $mailerDomain;
        $this->mailerReplyTo = $mailerReplyTo;
        $this->mailerSenderEmail = $mailerSenderEmail;
        $this->mailerSenderName = $mailerSenderName;
        $this->mailerIp = $mailerIp;

        switch ($mailerProvider) {
            case self::MAIL_PROVIDER_SENDINBLUE:
                $this->massEmailProvider = new SendinBlueProvider($mailerApiKey, $mailerClientId, $mailerSenderName, $mailerSenderEmail, $mailerReplyTo, $mailerClientTemplateId);

                break;
        }
        $this->massEmailProviderIpRange = $mailerProviderIpRange;

        switch ($smsProvider) {
            // none yet !
            default: $this->massSmsProvider = null;
        }
    }

    /**
     * Add a campaign.
     *
     * @param Campaign $campaign The campaign to add
     * @param User     $user     The user that adds the campaign
     *
     * @return Campaign The created campaign
     */
    public function addCampaign(Campaign $campaign, User $user)
    {
        $campaign->setMedium($this->mediumRepository->find(Medium::MEDIUM_EMAIL));
        $campaign->setUser($user);
        $campaign->setEmail($this->mailerSenderEmail);
        $campaign->setReplyTo($this->mailerReplyTo);
        $campaign->setFromName($this->mailerSenderName);
        $this->entityManager->persist($campaign);
        $this->entityManager->flush();

        return $campaign;
    }

    /**
     * Patch a campaign.
     *
     * @param Campaign $campaign The campaign to update
     * @param array    $fields   The updated fields
     *
     * @return Campaign The campaign updated
     */
    public function patchCampaign(Campaign $campaign, array $fields)
    {
        // persist the campaign
        $this->entityManager->persist($campaign);
        $this->entityManager->flush();

        // return the campaign
        return $campaign;
    }

    /**
     * Delete a campaign.
     *
     * @param Campaign $campaign The campaign to delete
     */
    public function deleteCampaign(Campaign $campaign)
    {
        $this->entityManager->remove($campaign);
        $this->entityManager->flush();
    }

    /**
     * Associate users to a campaign (complete Campaign information, and create deliveries only if selection).
     *
     * @param Campaign $campaign The campaign
     * @param iterable $users    The users
     * @param array    $filters  The filters if the filter type is 'filter'
     */
    public function associateUsers(Campaign $campaign, iterable $users, array $filters = [])
    {
        switch ($campaign->getFilterType()) {
            case Campaign::FILTER_TYPE_SELECTION:
                // remove selection if it exists before adding the new one
                $campaign->removeDeliveries();
                $campaign->setFilters(null);
                foreach ($users as $user) {
                    $delivery = new Delivery();
                    $delivery->setCampaign($campaign);
                    $delivery->setUser($user);
                    $delivery->setStatus(Delivery::STATUS_PENDING);
                    $this->entityManager->persist($delivery);
                }
                // force updated date
                $campaign->setAutoUpdatedDate();

                break;

            case Campaign::FILTER_TYPE_FILTER:
                // remove selection if it exists
                $campaign->removeDeliveries();
                $campaign->setFilters($this->stringFilters($filters));
                $campaign->setDeliveryCount(iterator_count($users));
                $this->entityManager->persist($campaign);

                break;
        }
        $this->entityManager->flush();
    }

    /**
     * Associate community users to a campaign (complete Campaign information, and create deliveries only if selection).
     *
     * @param Campaign $campaign The campaign
     * @param iterable $members  The members
     * @param array    $filters  The filters if the filter type is 'filter'
     */
    public function associateCommunityUsers(Campaign $campaign, iterable $members, array $filters = [])
    {
        switch ($campaign->getFilterType()) {
            case Campaign::FILTER_TYPE_SELECTION:
                // remove selection if it exists before adding the new one
                $campaign->removeDeliveries();
                $campaign->setFilters(null);
                foreach ($members as $member) {
                    $delivery = new Delivery();
                    $delivery->setCampaign($campaign);
                    $delivery->setUser($member->getUser());
                    $delivery->setStatus(Delivery::STATUS_PENDING);
                    $this->entityManager->persist($delivery);
                }
                // force updated date
                $campaign->setAutoUpdatedDate();

                break;

            case Campaign::FILTER_TYPE_FILTER:
                // remove selection if it exists
                $campaign->removeDeliveries();
                $campaign->setFilters($this->stringFilters($filters));
                $campaign->setDeliveryCount(iterator_count($members));
                $this->entityManager->persist($campaign);

                break;
        }
        $this->entityManager->flush();
    }

    /**
     * Send the campaign to the associated users, or to the creator if it's a test.
     *
     * @param Campaign $campaign The campaign
     * @param iterable $users    The users
     * @param int      $mode     The sending mode (test or prod)
     *
     * @return Campaign The campaign
     */
    public function send(Campaign $campaign, iterable $users, int $mode)
    {
        // the delivery count may have changed
        $campaign->setDeliveryCount(iterator_count($users));
        $this->entityManager->persist($campaign);
        $this->entityManager->flush();

        switch ($campaign->getMedium()->getId()) {
            case Medium::MEDIUM_EMAIL:
                return $this->sendMassEmail($campaign, $users, $mode);

                break;

            case Medium::MEDIUM_SMS:
                return $this->sendMassSms($campaign, $users, $mode);

                break;

            default:
                break;
        }

        return $campaign;
    }

    /**
     * Handle an unsubscribe webhook.
     *
     * @param Request $request The request that contains the data
     *
     * @return array An empty array
     */
    public function handleUnsubscribeHook(MassCommunicationHook $hook, Request $request)
    {
        switch ($this->mailerProvider) {
            case self::MAIL_PROVIDER_SENDINBLUE:
                // Sendinblue uses ip range
                if (ip2long($request->getClientIp()) > ip2long($this->massEmailProviderIpRange['maxIp']) || ip2long($request->getClientIp()) < ip2long($this->massEmailProviderIpRange['minIp'])) {
                    throw new \Exception('Unauthorized');
                }
                if (!$email = $hook->getEmail()) {
                    throw new \Exception('Missing email');
                }
                if (!$user = $this->userRepository->findOneBy(['email' => $email])) {
                    throw new UserNotFoundException('User not found');
                }

                // @var User $user
                $user->setNewsSubscription(false);
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                break;

            default:
                break;
        }

        return [];
    }

    /**
     * Send messages for a campaign by email.
     *
     * @param Campaign $campaign The campaign to send the messages for
     * @param iterable $users    The users
     * @param int      $mode     The sending mode (test or prod)
     *
     * @return Campaign the campaign modified with the result of the send
     */
    private function sendMassEmail(Campaign $campaign, iterable $users, int $mode)
    {
        // first we construct the recipients array
        $recipients = [];

        switch ($campaign->getFilterType()) {
            case Campaign::FILTER_TYPE_SELECTION:
                foreach ($campaign->getDeliveries() as $delivery) {
                    // @var Delivery $delivery
                    $recipients[] = new Recipient($delivery->getUser()->getEmail(), $delivery->getUser()->getGivenName(), $delivery->getUser()->getFamilyName(), null, $delivery->getUser()->getUnsubscribeToken());
                }

                break;

            case Campaign::FILTER_TYPE_FILTER:
                /**
                 * @var User $user
                 */
                foreach ($users as $user) {
                    $recipients[] = new Recipient($user->getEmail(), $user->getGivenName(), $user->getFamilyName(), null, $user->getUnsubscribeToken());
                }

                break;
        }
        // then we send the message or test message
        switch ($mode) {
            case self::MODE_TEST:
                // we set the sender
                $sender = new Sender();
                $sender->setUser($campaign->getUser());

                // we check if we have already created a campaign provider
                if (is_null($campaign->getProviderCampaignId())) {
                    // we create the campaign on provider side
                    try {
                        $providerCampaign = $this->massEmailProvider->createCampaign($campaign->getName(), $sender, $campaign->getSubject(), $this->getFormedEmailBody($campaign->getBody()), $recipients);
                    } catch (\Exception $e) {
                        throw new CampaignException($e->getMessage());
                    }
                    // we set the campaign provider id
                    $campaign->setProviderCampaignId($providerCampaign['id']);
                }

                // we send the test email with the creator of the campaign as recipient
                $this->massEmailProvider->sendCampaignTest($campaign->getName(), $campaign->getProviderCampaignId(), [$campaign->getUser()->getEmail()]);

                // update the campaign if needed
                if (Campaign::STATUS_CREATED != $campaign->getStatus()) {
                    $campaign->setStatus(Campaign::STATUS_CREATED);
                    $this->entityManager->persist($campaign);
                    $this->entityManager->flush();
                }

                break;

            case self::MODE_PROD:
                $this->massEmailProvider->sendCampaign($campaign->getName(), $campaign->getProviderCampaignId());
                $campaign->setStatus(Campaign::STATUS_SENT);
                $this->entityManager->persist($campaign);
                $this->entityManager->flush();

                break;
        }

        return $campaign;
    }

    /**
     * Send messages for a campaign by sms.
     *
     * @param Campaign $campaign The campaign to send the messages for
     * @param iterable $users    The users
     * @param int      $mode     The sending mode (test or prod)
     *
     * @return Campaign the campaign modified with the result of the send
     */
    private function sendMassSms(Campaign $campaign, iterable $users, int $mode)
    {
        // first we construct the recipients array
        $recipients = [];

        switch ($campaign->getFilterType()) {
            case Campaign::FILTER_TYPE_SELECTION:
                foreach ($campaign->getDeliveries() as $delivery) {
                    // @var Delivery $delivery
                    $recipients[] = new Recipient($delivery->getUser()->getEmail(), $delivery->getUser()->getGivenName(), $delivery->getUser()->getFamilyName(), $delivery->getUser()->getTelephone());
                }

                break;

            case Campaign::FILTER_TYPE_FILTER:
                /**
                 * @var User $user
                 */
                foreach ($users as $user) {
                    $recipients[] = new Recipient($user()->getEmail(), $user()->getGivenName(), $user()->getFamilyName(), $user->getTelephone());
                }

                break;
        }
        // then we send the message or test message
        // TODO : finish !
        switch ($mode) {
            case self::MODE_TEST:
                break;

            case self::MODE_PROD:
                break;
        }

        return $campaign;
    }

    /**
     * Converts an array of filters to an url-friendly string of filters.
     *
     * @param array $filters The array of filters as key=>value
     *
     * @return string The filters as a string
     */
    private function stringFilters(array $filters)
    {
        $stringFilters = '';

        foreach ($filters as $filter => $value) {
            // value may be an array itself
            if (is_array($value)) {
                foreach ($value as $key => $val) {
                    if (!is_int($key)) {
                        $stringFilters .= $filter.'['.$key.']='.$val.'&';
                    } else {
                        $stringFilters .= $filter.'='.$val.'&';
                    }
                }
            } else {
                $stringFilters .= $filter.'='.$value.'&';
            }
        }

        return substr($stringFilters, 0, -1);
    }

    /**
     * Create a well-formed body for email send.
     * Note : the context variables should be present in the template.
     *
     * @param string $body The initial body
     *
     * @return string The templated body
     */
    private function getFormedEmailBody(?string $body): string
    {
        $encodedBody = json_decode($body, true);
        $arrayForTemplate = [];
        foreach ($encodedBody as $parts) {
            if ('image' == $parts['type']) {
                $arrayForTemplate[] = [
                    'type' => $parts['type'],
                    'content' => $parts['src'],
                    'position' => $parts['position'],
                ];
            } else {
                $arrayForTemplate[] = [
                    'type' => $parts['type'],
                    'content' => $parts['value'],
                    'position' => $parts['position'],
                ];
            }
        }

        return $this->templating->render(
            $this->mailTemplate,
            ['arrayForTemplate' => $arrayForTemplate]
        );
    }
}
