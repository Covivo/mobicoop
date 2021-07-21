<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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
 **************************/

namespace Mobicoop\Bundle\MobicoopBundle\Api\Service;

use Mobicoop\Bundle\MobicoopBundle\Import\Entity\Redirect;
use Mobicoop\Bundle\MobicoopBundle\Communication\Entity\Contact;
use Mobicoop\Bundle\MobicoopBundle\Solidary\Entity\Structure;
use Mobicoop\Bundle\MobicoopBundle\Solidary\Entity\Subject;
use TypeError;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Mobicoop\Bundle\MobicoopBundle\Travel\Entity\TravelMode;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Matching;
use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity\PTDeparture;
use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity\PTArrival;
use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity\PTCompany;
use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity\PTLine;
use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity\PTStep;
use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity\PTLeg;
use Mobicoop\Bundle\MobicoopBundle\Image\Entity\Image;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Direction;
use Mobicoop\Bundle\MobicoopBundle\ExternalJourney\Entity\ExternalJourneyProvider;
use Mobicoop\Bundle\MobicoopBundle\Community\Entity\Community;
use Mobicoop\Bundle\MobicoopBundle\Community\Entity\CommunityUser;
use Mobicoop\Bundle\MobicoopBundle\Article\Entity\Article;
use Mobicoop\Bundle\MobicoopBundle\Article\Entity\Section;
use Mobicoop\Bundle\MobicoopBundle\Article\Entity\Paragraph;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ad;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ask;
use Mobicoop\Bundle\MobicoopBundle\Journey\Entity\Journey;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\MyAd;
use Mobicoop\Bundle\MobicoopBundle\Permission\Entity\Permission;
use Mobicoop\Bundle\MobicoopBundle\Communication\Entity\Message;
use Mobicoop\Bundle\MobicoopBundle\Communication\Entity\Recipient;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;
use Mobicoop\Bundle\MobicoopBundle\ExternalJourney\Entity\ExternalJourney;
use Mobicoop\Bundle\MobicoopBundle\Match\Entity\Mass;
use Mobicoop\Bundle\MobicoopBundle\Match\Entity\MassCarpool;
use Mobicoop\Bundle\MobicoopBundle\Match\Entity\MassJourney;
use Mobicoop\Bundle\MobicoopBundle\Match\Entity\MassMatching;
use Mobicoop\Bundle\MobicoopBundle\Match\Entity\MassMatrix;
use Mobicoop\Bundle\MobicoopBundle\Match\Entity\MassPerson;
use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity\PTAccessibilityStatus;
use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity\PTJourney;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Proposal;
use Mobicoop\Bundle\MobicoopBundle\Communication\Entity\ContactType;
use Mobicoop\Bundle\MobicoopBundle\Community\Entity\MCommunity;
use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity\PTLineStop;
use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity\PTLocality;
use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity\PTStop;
use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity\PTTripPoint;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\Event\Entity\Event;
use Mobicoop\Bundle\MobicoopBundle\Image\Entity\Icon;
use Mobicoop\Bundle\MobicoopBundle\Price\Entity\Price;
use Mobicoop\Bundle\MobicoopBundle\RelayPoint\Entity\RelayPoint;
use Mobicoop\Bundle\MobicoopBundle\RelayPoint\Entity\RelayPointType;
use Mobicoop\Bundle\MobicoopBundle\Payment\Entity\BankAccount;
use Mobicoop\Bundle\MobicoopBundle\Payment\Entity\PaymentItem;
use Mobicoop\Bundle\MobicoopBundle\Payment\Entity\PaymentPayment;
use Mobicoop\Bundle\MobicoopBundle\Payment\Entity\PaymentPeriod;
use Mobicoop\Bundle\MobicoopBundle\Payment\Entity\PaymentWeek;
use Mobicoop\Bundle\MobicoopBundle\Payment\Entity\ValidationDocument;
use Mobicoop\Bundle\MobicoopBundle\RelayPoint\Entity\RelayPointMap;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\Block;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\ProfileSummary;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\PublicProfile;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\Review;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\ReviewDashboard;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\SsoConnection;
use Mobicoop\Bundle\MobicoopBundle\I18n\Entity\Language;
use Mobicoop\Bundle\MobicoopBundle\Editorial\Entity\Editorial;

/**
 * Custom deserializer service.
 * Used because deserialization of nested array of objects doesn't work yet...
 * Should be dumped when deserialization will work !
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class Deserializer
{
    const DATETIME_FORMAT = \DateTime::ISO8601;
    const SETTER_PREFIX = "set";

    /**
     * Deserialize an object.
     *
     * @param string $class The expected class of the object
     * @param array $data   The array to deserialize
     * @return array|User|Address|Proposal|Matching|PTJourney|ExternalJourney|Event|Image|PTTripPoint|PTLineStop|ExternalJourneyProvider|Mass|MassPerson|Community|Article|Permission|null
     */
    public function deserialize(string $class, array $data)
    {
        switch ($class) {
            case User::class:
                return $this->deserializeUser($data);
                break;
            case Address::class:
                return $this->deserializeAddress($data);
                break;
            case Event::class:
                return $this->deserializeEvent($data);
                break;
            case Image::class:
                return $this->deserializeImage($data);
                break;
            case Ad::class:
                return $this->deserializeAd($data);
                break;
            case MyAd::class:
                return $this->deserializeMyAd($data);
                break;
            case Proposal::class:
                return $this->deserializeProposal($data);
                break;
            case Journey::class:
                return $this->deserializeJourney($data);
                break;
            case PTJourney::class:
                return $this->deserializePTJourney($data);
                break;
            case PTTripPoint::class:
                return $this->deserializePTTripPoint($data);
                break;
            case PTLineStop::class:
                return $this->deserializePTLineStop($data);
                break;
            case ExternalJourneyProvider::class:
                return $this->deserializeExternalJourneyProvider($data);
                break;
            case ExternalJourney::class:
                return $data;
                break;
            case Mass::class:
                return $this->deserializeMass($data);
                break;
            case MassPerson::class:
                return $this->deserializeMassPerson($data);
                break;
            case MassMatching::class:
                return $this->deserializeMassMatching($data);
                break;
            case Community::class:
                return $this->deserializeCommunity($data);
                break;
            case CommunityUser::class:
                return $this->deserializeCommunityUser($data);
                break;
            case Article::class:
                return $this->deserializeArticle($data);
                break;
            case Permission::class:
                return $this->deserializePermission($data);
                break;
            case Message::class:
                return $this->deserializeMessage($data);
                break;
            case Recipient::class:
                return $this->deserializeRecipient($data);
                break;
            case Direction::class:
                return $this->deserializeDirection($data);
                break;
            case Contact::class:
                return $this->deserializeContact($data);
                break;
            case Subject::class:
                return $this->deserializeSubject($data);
                break;
            case Structure::class:
                return $this->deserializeStructure($data);
                break;
            case Price::class:
                return $this->deserializePrice($data);
                break;
            case Redirect::class:
                return $this->deserializeRedirect($data);
                break;
            case RelayPoint::class:
                return $this->deserializeRelayPoint($data) ;
                break;
            case BankAccount::class:
                return $this->deserializeBankAccount($data) ;
                break;
            case PaymentItem::class:
                return $this->deserializePaymentItem($data) ;
                break;
            case PaymentPayment::class:
                return $this->deserializePaymentPayment($data) ;
                break;
            case PaymentPeriod::class:
                return $this->deserializePaymentPeriod($data) ;
                break;
            case PaymentWeek::class:
                return $this->deserializePaymentWeek($data) ;
                break;
            case Ask::class:
                return $this->deserializeAsk($data);
                break;
            case Block::class:
                return $this->deserializeBlock($data);
                break;
            case MCommunity::class:
                return $this->deserializeMCommunity($data);
                break;
            case ValidationDocument::class:
                return $this->deserializeValidationDocument($data);
                break;
            case SsoConnection::class:
                return $this->deserializeSsoConnection($data);
                break;
            case ProfileSummary::class:
                return $this->deserializeProfileSummary($data);
                break;
            case PublicProfile::class:
                return $this->deserializePublicProfile($data);
                break;
            case RelayPointMap::class:
                return $this->deserializeRelayPointMap($data) ;
                break;
            case ReviewDashboard::class:
                return $this->deserializeReviewDashboard($data) ;
                break;
            case ContactType::class:
                return $this->deserializeContactType($data) ;
                break;
            case Language::class:
                return $this->deserializeLanguage($data) ;
                break;
            case Editorial::class:
                return $this->deserializeEditorial($data) ;
                break;
            default:
                break;
        }
        return null;
    }

    private function deserializeUser(array $data): ?User
    {
        $user = new User();
        $user = $this->autoSet($user, $data);
        if (isset($data["@id"])) {
            $user->setIri($data["@id"]);
        }
        if (isset($data["addresses"])) {
            foreach ($data["addresses"] as $address) {
                $user->addAddress($this->deserializeAddress($address));
            }
        }
        if (isset($data["masses"])) {
            foreach ($data["masses"] as $mass) {
                $user->addMass($this->deserializeMass($mass));
            }
        }
        if (isset($data["images"])) {
            foreach ($data["images"] as $image) {
                $user->addImage($this->deserializeImage($image));
            }
        }
        if (isset($data["bankAccounts"])) {
            $bankAccounts = [];
            foreach ($data["bankAccounts"] as $bankAccount) {
                $bankAccounts[] = $this->deserializeBankAccount($bankAccount);
            }
            $user->setBankAccounts($bankAccounts);
        }
        if (isset($data["language"])) {
            $user->setLanguage($this->deserializeLanguage($data['language']));
        }
        return $user;
    }

    private function deserializeAddress(array $data): ?Address
    {
        $address = new Address();
        $address = $this->autoSet($address, $data);
        if (isset($data["@id"])) {
            $address->setIri($data["@id"]);
        }
        return $address;
    }

    private function deserializeEvent(array $data): ?Event
    {
        $event = new Event();
        $event = $this->autoSet($event, $data);
        if (isset($data["@id"])) {
            $event->setIri($data["@id"]);
        }
        if (isset($data["address"])) {
            $event->setAddress($this->deserializeAddress($data['address']));
        }
        if (isset($data["images"])) {
            foreach ($data["images"] as $image) {
                $event->addImage($this->deserializeImage($image));
            }
        }
        return $event;
    }

    private function deserializeImage(array $data): ?Image
    {
        $image = new Image();
        $image = $this->autoSet($image, $data);
        if (isset($data["@id"])) {
            $image->setIri($data["@id"]);
        }
        return $image;
    }

    private function deserializeAd(array $data): ?Ad
    {
        $ad = new Ad();
        $ad = $this->autoSet($ad, $data);
        if (isset($data["outwardWaypoints"])) {
            $ad->setOutwardWaypoints($data["outwardWaypoints"]);
        }
        return $ad;
    }

    private function deserializeMyAd(array $data): ?MyAd
    {
        $myAd = new MyAd();
        $myAd = $this->autoSet($myAd, $data);
        return $myAd;
    }

    private function deserializeProposal(array $data): ?Proposal
    {
        $proposal = new Proposal();
        $proposal = $this->autoSet($proposal, $data);
        if (isset($data["@id"])) {
            $proposal->setIri($data["@id"]);
        }
        if (isset($data["user"])) {
            $proposal->setUser($this->deserializeUser($data['user']));
        }
        if (isset($data["travelModes"])) {
            foreach ($data["travelModes"] as $travelMode) {
                $proposal->addTravelMode($this->deserializeTravelMode($travelMode));
            }
        }
        if (isset($data["proposalLinked"]) && is_array($data["proposalLinked"])) {
            $proposal->setProposalLinked($this->deserializeProposal($data['proposalLinked']));
        }
        return $proposal;
    }

    private function deserializeJourney(array $data): ?Journey
    {
        $journey = new Journey();
        $journey = $this->autoSet($journey, $data);
        return $journey;
    }

    private function deserializeTravelMode(array $data): ?TravelMode
    {
        $travelMode = new TravelMode();
        $travelMode = $this->autoSet($travelMode, $data);
        if (isset($data["@id"])) {
            $travelMode->setIri($data["@id"]);
        }
        return $travelMode;
    }

    private function deserializeDirection(array $data): ?Direction
    {
        $direction = new Direction();
        $direction = $this->autoSet($direction, $data);
        if (isset($data["@id"])) {
            $direction->setIri($data["@id"]);
        }
        if (isset($data["points"])) {
            $points = [];
            foreach ($data["points"] as $address) {
                $points[] = $this->deserializeAddress($address);
            }
            $direction->setPoints($points);
        }
        if (isset($data["directPoints"])) {
            $direction->setDirectPoints($data["directPoints"]);
        }
        return $direction;
    }

    private function deserializePTJourney(array $data): ?PTJourney
    {
        $PTJourney = new PTJourney();
        $PTJourney = $this->autoSet($PTJourney, $data);
        if (isset($data["ptdeparture"])) {
            $PTJourney->setPTDeparture($this->deserializePTDeparture($data["ptdeparture"]));
        }
        if (isset($data["ptarrival"])) {
            $PTJourney->setPTArrival($this->deserializePTArrival($data["ptarrival"]));
        }
        if (isset($data["ptlegs"])) {
            $nblegs = 0;
            foreach ($data["ptlegs"] as $ptleg) {
                $nblegs++;
                $PTJourney->addPTLeg($this->deserializePTLeg($ptleg, $nblegs));
            }
        }
        return $PTJourney;
    }

    private function deserializePTTripPoint(array $data): ?PTTripPoint
    {
        $PTTripPoint = new PTTripPoint();
        $PTTripPoint = $this->autoSet($PTTripPoint, $data);
        if (isset($data["locality"])) {
            $PTTripPoint->setLocality($this->deserializeLocality($data["locality"]));
        }
        return $PTTripPoint;
    }

    private function deserializeLocality(array $data): ?PTLocality
    {
        $PTLocality = new PTLocality();
        $PTLocality = $this->autoSet($PTLocality, $data);
        return $PTLocality;
    }

    private function deserializePTLineStop(array $data): ?PTLineStop
    {
        $PTLineStop = new PTLineStop(1);
        $PTLineStop = $this->autoSet($PTLineStop, $data);
        if (isset($data["line"])) {
            $PTLineStop->setLine($this->deserializePTLine($data["line"]));
        }
        if (isset($data["stop"])) {
            $PTLineStop->setStop($this->deserializePTStop($data["stop"]));
        }
        return $PTLineStop;
    }

    private function deserializePTDeparture(array $data): ?PTDeparture
    {
        $PTDeparture = new PTDeparture();
        $PTDeparture = $this->autoSet($PTDeparture, $data);
        if (isset($data["address"])) {
            $PTDeparture->setAddress($this->deserializeAddress($data["address"]));
        }
        return $PTDeparture;
    }

    private function deserializePTArrival(array $data): ?PTArrival
    {
        $PTArrival = new PTArrival();
        $PTArrival = $this->autoSet($PTArrival, $data);
        if (isset($data["address"])) {
            $PTArrival->setAddress($this->deserializeAddress($data["address"]));
        }
        return $PTArrival;
    }

    private function deserializePTLeg(array $data, int $id): ?PTLeg
    {
        $PTLeg = new PTLeg($id);
        $PTLeg = $this->autoSet($PTLeg, $data);
        if (isset($data["ptdeparture"])) {
            $PTLeg->setPTDeparture($this->deserializePTDeparture($data["ptdeparture"]));
        }
        if (isset($data["ptarrival"])) {
            $PTLeg->setPTArrival($this->deserializePTArrival($data["ptarrival"]));
        }
        if (isset($data["travelMode"])) {
            $PTLeg->setTravelMode($this->deserializeTravelMode($data["travelMode"]));
        }
        if (isset($data["ptline"])) {
            $PTLeg->setPTLine($this->deserializePTLine($data["ptline"]));
        }
        if (isset($data["ptsteps"])) {
            $nbsteps = 0;
            foreach ($data["ptsteps"] as $ptstep) {
                $nbsteps++;
                $PTLeg->addPTStep($this->deserializePTStep($ptstep, $nbsteps));
            }
        }
        return $PTLeg;
    }

    private function deserializePTLine(array $data): ?PTLine
    {
        $PTLine = new PTLine();
        $PTLine = $this->autoSet($PTLine, $data);
        if (isset($data["ptcompany"])) {
            $PTLine->setPTCompany($this->deserializePTCompany($data["ptcompany"]));
        }
        return $PTLine;
    }

    private function deserializePTStop(array $data): ?PTStop
    {
        $PTStop = new PTStop(1);
        $PTStop = $this->autoSet($PTStop, $data);
        if (isset($data["accessibilityStatus"])) {
            $PTStop->setAccessibilityStatus($this->deserializePTAccessibilityStatus($data["accessibilityStatus"]));
        }
        return $PTStop;
    }

    private function deserializePTAccessibilityStatus(array $data): ?PTAccessibilityStatus
    {
        $PTAccessibilityStatus = new PTAccessibilityStatus(1);
        $PTAccessibilityStatus = $this->autoSet($PTAccessibilityStatus, $data);
        return $PTAccessibilityStatus;
    }

    private function deserializePTCompany(array $data): ?PTCompany
    {
        $PTCompany = new PTCompany();
        $PTCompany = $this->autoSet($PTCompany, $data);
        return $PTCompany;
    }

    private function deserializePTStep(array $data, int $id): ?PTStep
    {
        $PTStep = new PTStep($id);
        $PTStep = $this->autoSet($PTStep, $data);
        if (isset($data["ptdeparture"])) {
            $PTStep->setPTDeparture($this->deserializePTDeparture($data["ptdeparture"]));
        }
        if (isset($data["ptarrival"])) {
            $PTStep->setPTArrival($this->deserializePTArrival($data["ptarrival"]));
        }
        return $PTStep;
    }

    private function deserializeMass(array $data): ?Mass
    {
        $mass = new Mass();
        $mass = $this->autoSet($mass, $data);
        if (isset($data["@id"])) {
            $mass->setIri($data["@id"]);
        }
        if (isset($data["persons"])) {
            foreach ($data["persons"] as $person) {
                $mass->addPerson($this->deserializeMassPerson($person));
            }
        }
        if (isset($data["massMatrix"])) {
            $mass->setMassMatrix($this->deserializeMassMatrix($data["massMatrix"]));
        }
        return $mass;
    }

    private function deserializeMassMatrix(array $data): ?MassMatrix
    {
        $massMatrix = new MassMatrix();
        $massMatrix = $this->autoSet($massMatrix, $data);
        if (isset($data["@id"])) {
            $massMatrix->setIri($data["@id"]);
        }
        if (isset($data["originalsJourneys"])) {
            foreach ($data["originalsJourneys"] as $massJourney) {
                $massMatrix->addOriginalsJourneys($this->deserializeMassJourney($massJourney));
            }
        }
        if (isset($data["carpools"])) {
            foreach ($data["carpools"] as $carpool) {
                $massMatrix->addCarpools($this->deserializeMassCarpool($carpool));
            }
        }
        return $massMatrix;
    }

    private function deserializeMassJourney(array $data): ?MassJourney
    {
        $originalJourney = new MassJourney();
        $originalJourney = $this->autoSet($originalJourney, $data);
        if (isset($data["@id"])) {
            $originalJourney->setIri($data["@id"]);
        }
        return $originalJourney;
    }

    private function deserializeMassCarpool(array $data): ?MassCarpool
    {
        $massCarpool = new MassCarpool();
        $massCarpool = $this->autoSet($massCarpool, $data);
        if (isset($data["@id"])) {
            $massCarpool->setIri($data["@id"]);
        }
        return $massCarpool;
    }
    private function deserializeMassPerson(array $data): ?MassPerson
    {
        $massPerson = new MassPerson();
        $massPerson = $this->autoSet($massPerson, $data);
        if (isset($data["@id"])) {
            $massPerson->setIri($data["@id"]);
        }
        if (isset($data["personalAddress"])) {
            $massPerson->setPersonalAddress($this->deserializeAddress($data["personalAddress"]));
        }
        if (isset($data["workAddress"])) {
            $massPerson->setWorkAddress($this->deserializeAddress($data["workAddress"]));
        }
        if (isset($data["direction"])) {
            $massPerson->setDirection($this->deserializeDirection($data["direction"]));
        }
        if (isset($data["matchingsAsDriver"])) {
            foreach ($data["matchingsAsDriver"] as $matchingsAsDriver) {
                $massPerson->addMatchingsAsDriver($this->deserializeMassMatching($matchingsAsDriver));
            }
        }
        if (isset($data["matchingsAsPassenger"])) {
            foreach ($data["matchingsAsPassenger"] as $matchingsAsPassenger) {
                $massPerson->addMatchingsAsPassenger($this->deserializeMassMatching($matchingsAsPassenger));
            }
        }
        return $massPerson;
    }

    private function deserializeMassMatching(array $data): ?MassMatching
    {
        $massMatching = new MassMatching();
        $massMatching = $this->autoSet($massMatching, $data);
        if (isset($data["@id"])) {
            $massMatching->setIri($data["@id"]);
        }
        return $massMatching;
    }

    private function deserializeExternalJourneyProvider(array $data): ?ExternalJourneyProvider
    {
        $provider = new ExternalJourneyProvider();
        $provider = $this->autoSet($provider, $data);
        return $provider;
    }

    private function deserializeCommunityUser(array $data): ?CommunityUser
    {
        $communityUser = new communityUser();
        $communityUser = $this->autoSet($communityUser, $data);
        if (isset($data["@id"])) {
            $communityUser->setIri($data["@id"]);
        }
        if (isset($data["community"]) && is_array($data["community"])) {
            $communityUser->setCommunity($this->deserializeCommunity($data["community"]));
        }
        if (isset($data["user"]) && is_array($data["user"])) {
            $communityUser->setUser($this->deserializeUser($data["user"]));
        }
        if (isset($data["admin"])) {
            $communityUser->setAdmin($this->deserializeUser($data["admin"]));
        }
        return $communityUser;
    }

    private function deserializeCommunity(array $data): ?Community
    {
        $community = new Community();
        $community = $this->autoSet($community, $data);
        if (isset($data["@id"])) {
            $community->setIri($data["@id"]);
        }
        if (isset($data["user"])) {
            $community->setUser($this->deserializeUser($data["user"]));
        }
        if (isset($data["address"])) {
            $community->setAddress($this->deserializeAddress($data['address']));
        }
        if (isset($data["images"])) {
            foreach ($data["images"] as $image) {
                $community->addImage($this->deserializeImage($image));
            }
        }
        if (isset($data["proposals"])) {
            foreach ($data["proposals"] as $proposal) {
                $community->addProposal($this->deserializeProposal($proposal));
            }
        }
        if (isset($data["communityUsers"]) && is_array($data["communityUsers"])) {
            foreach ($data["communityUsers"] as $communityUser) {
                if (!is_null($communityUser) && is_array($communityUser)) {
                    $community->addCommunityUser($this->deserializeCommunityUser($communityUser));
                }
            }
        }
        if (isset($data["communitySecurities"]) && is_array($data["communitySecurities"]) && count($data["communitySecurities"]) > 0) {
            $community->setSecured(true);
        }
        return $community;
    }

    private function deserializeArticle(array $data): ?Article
    {
        $article = new Article();
        $article = $this->autoSet($article, $data);
        if (isset($data["@id"])) {
            $article->setIri($data["@id"]);
        }
        if (isset($data["sections"])) {
            foreach ($data["sections"] as $section) {
                $article->addSection($this->deserializeSection($section));
            }
        }
        return $article;
    }

    private function deserializeSection(array $data): ?Section
    {
        $section = new Section();
        $section = $this->autoSet($section, $data);
        if (isset($data["@id"])) {
            $section->setIri($data["@id"]);
        }
        if (isset($data["paragraphs"])) {
            foreach ($data["paragraphs"] as $paragraph) {
                $section->addParagraph($this->deserializeParagraph($paragraph));
            }
        }
        return $section;
    }

    private function deserializeParagraph(array $data): ?Paragraph
    {
        $paragraph = new Paragraph();
        $paragraph = $this->autoSet($paragraph, $data);
        if (isset($data["@id"])) {
            $paragraph->setIri($data["@id"]);
        }
        return $paragraph;
    }

    private function deserializePermission(array $data): ?Permission
    {
        $permission = new Permission();
        $permission = $this->autoSet($permission, $data);
        if (isset($data["@id"])) {
            $permission->setIri($data["@id"]);
        }
        return $permission;
    }

    private function deserializeMessage(array $data): ?Message
    {
        $message = new Message();
        $message = $this->autoSet($message, $data);
        if (isset($data["@id"])) {
            $message->setIri($data["@id"]);
        }
        if (isset($data["user"])) {
            $message->setUser($this->deserializeUser($data["user"]));
        }
        if (isset($data["recipients"])) {
            foreach ($data["recipients"] as $recipient) {
                $message->addRecipient($this->deserializeRecipient($recipient));
            }
        }
        if (isset($data["message"]) && is_array($data["message"])) {
            $message->setMessage($this->deserializeMessage($data["message"]));
        }

        return $message;
    }

    private function deserializeRecipient(array $data): ?Recipient
    {
        $recipient = new Recipient();
        $recipient = $this->autoSet($recipient, $data);
        if (isset($data["@id"])) {
            $recipient->setIri($data["@id"]);
        }
        if (isset($data["user"])) {
            $recipient->setUser($this->deserializeUser($data["user"]));
        }
        if (isset($data["message"]) && is_array($data["message"])) {
            $recipient->setMessage($this->deserializeMessage($data["message"]));
        }
        return $recipient;
    }

    private function deserializeContact(array $data) : ?Contact
    {
        $contact = new Contact();
        $contact = $this->autoSet($contact, $data);

        return $contact;
    }

    private function deserializeSubject(array $data) : ?Subject
    {
        $contact = new Subject();
        $contact = $this->autoSet($contact, $data);

        return $contact;
    }

    private function deserializeStructure(array $data) : ?Structure
    {
        $contact = new Structure();
        $contact = $this->autoSet($contact, $data);

        return $contact;
    }

    private function deserializePrice(array $data) : ?Price
    {
        $price = new Price();
        $price = $this->autoSet($price, $data);

        return $price;
    }

    private function deserializeRedirect(array $data) : ?Redirect
    {
        $redirect = new Redirect();
        $redirect = $this->autoSet($redirect, $data);

        return $redirect;
    }

    private function deserializeRelayPoint(array $data): ?RelayPoint
    {
        $relayPoint = new RelayPoint();
        $relayPoint = $this->autoSet($relayPoint, $data);
        if (isset($data["@id"])) {
            $relayPoint->setIri($data["@id"]);
        }
        // if (isset($data["user"])) {
        //     $relayPoint->setUser($this->deserializeUser($data["user"]));
        // }
        if (isset($data["address"])) {
            $relayPoint->setAddress($this->deserializeAddress($data['address']));
        }
        // if (isset($data["images"])) {
        //     foreach ($data["images"] as $image) {
        //         $relayPoint->addImage($this->deserializeImage($image));
        //     }
        // }
        // if (isset($data["community"])) {
        //     $relayPoint->setCommunity($this->deserializeCommunity($data["community"]));
        // }
        // if (isset($data["structure"])) {
        //     $relayPoint->setStructure($this->deserializeStructure($data["structure"]));
        // }
        if (isset($data["relayPointType"])) {
            $relayPoint->setRelayPointType($this->deserializeRelayPointType($data['relayPointType']));
        }

        return $relayPoint;
    }

    private function deserializeRelayPointType(array $data) : ?RelayPointType
    {
        $relayPointType = new RelayPointType();
        $relayPointType = $this->autoSet($relayPointType, $data);
        if (isset($data["images"])) {
            foreach ($data["images"] as $image) {
                $relayPointType->addImage($this->deserializeImage($image));
            }
        }
        if (isset($data["icon"])) {
            $relayPointType->setIcon($this->deserializeIcon($data['icon']));
        }
        return $relayPointType;
    }

    private function deserializeIcon(array $data) : ?Icon
    {
        $icon = new Icon();
        $icon = $this->autoSet($icon, $data);

        return $icon;
    }

    private function deserializeBankAccount(array $data) : ?BankAccount
    {
        $bankAccount = new BankAccount();
        $bankAccount = $this->autoSet($bankAccount, $data);
        if (isset($data["address"])) {
            $bankAccount->setAddress($this->deserializeAddress($data['address']));
        }

        return $bankAccount;
    }

    private function deserializePaymentItem(array $data) : ?PaymentItem
    {
        $paymentItem = new PaymentItem();
        if (isset($data["origin"])) {
            $paymentItem->setOrigin($this->deserializeAddress($data['origin']));
        }
        if (isset($data["destination"])) {
            $paymentItem->setDestination($this->deserializeAddress($data['destination']));
        }
        
        $paymentItem = $this->autoSet($paymentItem, $data);

        return $paymentItem;
    }

    private function deserializePaymentPayment(array $data) : ?PaymentPayment
    {
        $paymentPayment = new PaymentPayment();
        $paymentPayment = $this->autoSet($paymentPayment, $data);

        return $paymentPayment;
    }

    private function deserializePaymentPeriod(array $data) : ?PaymentPeriod
    {
        $paymentPeriod = new PaymentPeriod();
        $paymentPeriod = $this->autoSet($paymentPeriod, $data);

        return $paymentPeriod;
    }

    private function deserializePaymentWeek(array $data) : ?PaymentWeek
    {
        $paymentWeek = new PaymentWeek();
        $paymentWeek = $this->autoSet($paymentWeek, $data);

        return $paymentWeek;
    }

    private function deserializeAsk(array $data) : ?Ask
    {
        $ask = new Ask();
        $ask = $this->autoSet($ask, $data);

        return $ask;
    }

    private function deserializeBlock(array $data) : ?Block
    {
        $block = new Block();
        $block = $this->autoSet($block, $data);

        return $block;
    }

    private function deserializeMCommunity(array $data): ?MCommunity
    {
        $mCommunity = new MCommunity();
        $mCommunity = $this->autoSet($mCommunity, $data);
        return $mCommunity;
    }

    private function deserializeValidationDocument(array $data): ?ValidationDocument
    {
        $validationDocument = new ValidationDocument();
        $validationDocument = $this->autoSet($validationDocument, $data);
        return $validationDocument;
    }

    private function deserializeSsoConnection(array $data): ?SsoConnection
    {
        $ssoconnection = new SsoConnection();
        $ssoconnection = $this->autoSet($ssoconnection, $data);
        return $ssoconnection;
    }

    private function deserializeProfileSummary(array $data): ?ProfileSummary
    {
        $profileSummary = new ProfileSummary();
        $profileSummary = $this->autoSet($profileSummary, $data);
        return $profileSummary;
    }

    private function deserializePublicProfile(array $data): ?PublicProfile
    {
        $publicProfile = new PublicProfile();
        $publicProfile = $this->autoSet($publicProfile, $data);

        if (isset($data["profileSummary"])) {
            $publicProfile->setProfileSummary($this->deserializeProfileSummary($data['profileSummary']));
        }

        return $publicProfile;
    }

    private function deserializeRelayPointMap(array $data): ?RelayPointMap
    {
        $relayPointMap = new RelayPointMap();
        $relayPointMap = $this->autoSet($relayPointMap, $data);

        if (isset($data["address"])) {
            $relayPointMap->setAddress($this->deserializeAddress($data['address']));
        }
       
        if (isset($data["relayPointType"])) {
            $relayPointMap->setRelayPointType($this->deserializeRelayPointType($data['relayPointType']));
        }

        return $relayPointMap;
    }

    private function deserializeReviewDashboard(array $data): ?ReviewDashboard
    {
        $reviewDashboard = new ReviewDashboard();
        $reviewDashboard = $this->autoSet($reviewDashboard, $data);
        return $reviewDashboard;
    }

    private function deserializeContactType(array $data): ?ContactType
    {
        $contactType = new ContactType();
        $contactType = $this->autoSet($contactType, $data);
        return $contactType;
    }

    private function deserializeLanguage(array $data): ?Language
    {
        $language = new Language();
        $language = $this->autoSet($language, $data);
        if (isset($data["users"])) {
            foreach ($data["users"] as $user) {
                $user->addUser($this->deserializeUser($user));
            }
        }
        return $language;
    }

    private function deserializeEditorial(array $data): ?Editorial
    {
        $editorial = new Editorial();
        $editorial = $this->autoSet($editorial, $data);
        if (isset($data["images"])) {
            foreach ($data["images"] as $image) {
                $editorial->addImage($this->deserializeImage($image));
            }
        }
        return $editorial;
    }

    private function autoSet($object, $data)
    {
        $phpDocExtractor = new PhpDocExtractor();
        $reflectionExtractor = new ReflectionExtractor();
        $listExtractors = array($reflectionExtractor);
        $typeExtractors = array($phpDocExtractor);
        $descriptionExtractors = array($phpDocExtractor);
        $accessExtractors = array($reflectionExtractor);

        $propertyInfo = new PropertyInfoExtractor(
            $listExtractors,
            $typeExtractors,
            $descriptionExtractors,
            $accessExtractors
        );

        $properties = $propertyInfo->getProperties(get_class($object));
        foreach ($properties as $property) {
            if (isset($data[$property])) {
                $setter = self::SETTER_PREFIX.ucwords($property);
                if (method_exists($object, $setter)) {
                    // we try to set the property
                    try {
                        // it works !!!
                        $object->$setter($data[$property]);
                    } catch (TypeError $error) {
                        // fail... it must be an object or array property, we will treat it manually
                        $type = null;
                        if (!is_null($propertyInfo->getTypes(get_class($object), $property)[0])) {
                            $type = $propertyInfo->getTypes(get_class($object), $property)[0]->getClassName();
                        }
                        switch ($type) {
                            case "DateTime":
                            case "DateTimeInterface":
                                try {
                                    $catchedValue = \DateTime::createFromFormat(self::DATETIME_FORMAT, $data[$property]);
                                    $object->$setter($catchedValue);
                                } catch (\Error $e) {
                                }
                                break;
                            default: break;
                        }
                    }
                }
            }
        }
        return $object;
    }
}
