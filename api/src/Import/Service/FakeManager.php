<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

namespace App\Import\Service;

use App\Carpool\Ressource\Ad;
use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Waypoint;
use App\Carpool\Service\AdManager;
use App\Carpool\Service\ProposalManager;
use App\Geography\Entity\Address;
use App\Geography\Service\GeoSearcher;
use App\Geography\Service\GeoTools;
use App\User\Entity\User;
use App\User\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Faker manager service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class FakeManager
{
    const MIN_BIRTHDATE = "1940-01-01";
    const MAX_BIRTHDATE = "2001-01-01";
    const MIN_DATE = "2020-07-01";
    const MAX_DATE = "2021-07-01";
    const MIN_TIME = "05:00";
    const MAX_TIME = "23:45";
    const MIN_TIME_OUTWARD = "05:00";
    const MAX_TIME_OUTWARD = "10:00";
    const MIN_TIME_RETURN = "16:00";
    const MAX_TIME_RETURN = "22:00";
    const DOMAIN = "fake.org";
    const MIN_DISTANCE = 10000;         // min ad distance in metres
    const PUNCTUAL_FREQ = 0.3;          // punctual ad probability : 0 < random float < punctual_freq => punctual ad
    const ONEWAY_FREQ = 0.3;            // one way ad probability : 0 < random float < oneway_freq => one way ad
    const DRIVER_FREQ = 0.3;            // driver ad probability : 0 < random float < driver_freq => driver only ad
    const PASSENGER_FREQ = 0.5;         // passenger ad probability : driver_freq < random float < passenger_freq => passenger only ad; random float > passenger_freq => driver and passenger ad
    const NB_FREQ_1 = 0.7;              // number of ad probability : random_float < nb_freq_1 => 1 ad
    const NB_FREQ_2 = 0.9;              // number of ad probability : nb_freq_1 < random_float < nb_freq_2 => 2 ads, else 3 ads
    const MON_FREQ = 0.8;               // monday probability for regular ad : random_float < mon_freq => monday checked
    const TUE_FREQ = 0.8;               // tuesday probability for regular ad : random_float < tue_freq => tuesday checked
    const WED_FREQ = 0.6;               // wednesday probability for regular ad : random_float < wed_freq => wednesday checked
    const THU_FREQ = 0.8;               // thursday probability for regular ad : random_float < thu_freq => thursday checked
    const FRI_FREQ = 0.8;               // friday probability for regular ad : random_float < fri_freq => friday checked
    const SAT_FREQ = 0.3;               // saturday probability for regular ad : random_float < sat_freq => saturday checked
    const SUN_FREQ = 0.2;               // sunday probability for regular ad : random_float < sun_freq => sunday checked
    const BATCH = 50;                   // batch number for multi treatment

    private $entityManager;
    private $userManager;
    private $geoSearcher;
    private $geoTools;
    private $adManager;
    private $proposalManager;
    private $logger;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, UserManager $userManager, GeoSearcher $geoSearcher, GeoTools $geoTools, AdManager $adManager, ProposalManager $proposalManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
        $this->geoSearcher = $geoSearcher;
        $this->adManager = $adManager;
        $this->proposalManager = $proposalManager;
        $this->geoTools = $geoTools;
        $this->logger = $logger;
    }

    /**
     * Create fake users and proposals, on a given bbox
     *
     * @param integer $number_users     The number of users to create
     * @param float $min_lat            The minimum latitude for the user's address
     * @param float $min_lon            The minimum longitude for the user's address
     * @param float $max_lat            The maximum latitude for the user's address
     * @param float $max_lon            The maximum latitude for the user's address
     * @param int $split                Split the bbox for precise origin/destination :
     *                                  1 : no split
     *                                  >1 : split the bbox in $split equal parts (the origin in the first part, the destination in the last)
     * @param bool $truncate            Truncate dedicated entities before generating users and proposals
     * @return void
     */
    public function fakeUsers(int $number_users, float $min_lat, float $min_lon, float $max_lat, float $max_lon, int $split = 1, bool $truncate = false)
    {
        set_time_limit(3600);

        if ($truncate) {
            // clear existing users
            $conn = $this->entityManager->getConnection();
            $sql = "SET FOREIGN_KEY_CHECKS = 0;";
            $stmt = $conn->prepare($sql);
            $stmt->executeQuery();
            $sql = "
            TRUNCATE `address`;
            TRUNCATE `criteria`;
            TRUNCATE `direction`;
            TRUNCATE `matching`;
            TRUNCATE `proposal`;
            TRUNCATE `user`;
            TRUNCATE `user_auth_assignment`;
            TRUNCATE `user_notification`;
            TRUNCATE `waypoint`;";
            $stmt = $conn->prepare($sql);
            $stmt->executeQuery();
            $sql = "
            SET FOREIGN_KEY_CHECKS = 1;";
            $stmt = $conn->prepare($sql);
            $stmt->executeQuery();
        }

        $generated = 0;
        $emails = []; // used to avoid duplicates

        // 1 - we create the users and their proposals
        $this->logger->info('Start generating users | ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        while ($generated < $number_users) {

            // create the user
            $user = new User();
            $user->setStatus(User::STATUS_ACTIVE);
            $user->setGender(random_int(1, 3));
            $user->setBirthDate($this->getRandomDate(self::MIN_BIRTHDATE, self::MAX_BIRTHDATE));
            $user->setGivenName($this->getFakeFirstName($user->getGender()));
            $user->setFamilyName($this->getFakeLastName());
            $email = $this->normalize(strtolower($user->getGivenName())) . "." . $this->normalize(strtolower($user->getFamilyName())) . "." . sprintf("%'.05d", $this->randomFloat() * 100000) . "@" . self::DOMAIN;
            // skip if user already exists (by miracle)
            if (in_array($email, $emails)) {
                continue;
            }
            $user->setEmail($email);
            $user->setTelephone($this->generatePhoneNumber());
            $emails[] = $email;
            $user->setPassword(password_hash($user->getGivenName(), PASSWORD_ARGON2I));
            $user = $this->userManager->prepareUser($user);
            $user->setValidatedDate(new \DateTime());
            $user->setPhoneValidatedDate(new \DateTime());
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            // create the Ads

            // randomize number of ads for the current user
            $nbAds = 1;
            $rndAds = $this->randomFloat();
            if ($rndAds > self::NB_FREQ_1 && $nbAds <= self::NB_FREQ_2) {
                $nbAds = 2;
            } elseif ($rndAds > self::NB_FREQ_2) {
                $nbAds = 3;
            }
            for ($i=0;$i<$nbAds; $i++) {
                $ad = new Ad();
                $ad->setUser($user);
                $ad->setUserId($user->getId());
                $ad->setSearch(false);
                $ad->setOneWay($this->randomFloat()<=self::ONEWAY_FREQ ? true : false);
                $ad->setFrequency($this->randomFloat()<=self::PUNCTUAL_FREQ ? Criteria::FREQUENCY_PUNCTUAL : Criteria::FREQUENCY_REGULAR);
                $roleFreq = $this->randomFloat();
                $role = $roleFreq<self::PASSENGER_FREQ ? ($roleFreq <= self::DRIVER_FREQ ? Ad::ROLE_DRIVER : Ad::ROLE_PASSENGER) : Ad::ROLE_DRIVER_OR_PASSENGER;
                $ad->setRole($role);

                $ad->setPriceKm(0.06);
                $ad->setOutwardDriverPrice(0);

                if ($ad->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                    $ad->setOutwardDate($this->getRandomDate(self::MIN_DATE, self::MAX_DATE));
                    $ad->setOutwardTime($this->getRandomTime(self::MIN_TIME, self::MAX_TIME)->format("H:i"));
                } else {
                    $ad->setOutwardDate(\Datetime::createFromFormat('Y-m-d', self::MIN_DATE));
                    $ad->setOutwardLimitDate(\Datetime::createFromFormat('Y-m-d', self::MAX_DATE));
                    $mon = $this->randomFloat()<=self::MON_FREQ ? true : false;
                    $tue = $this->randomFloat()<=self::TUE_FREQ ? true : false;
                    $wed = $this->randomFloat()<=self::WED_FREQ ? true : false;
                    $thu = $this->randomFloat()<=self::THU_FREQ ? true : false;
                    $fri = $this->randomFloat()<=self::FRI_FREQ ? true : false;
                    $sat = $this->randomFloat()<=self::SAT_FREQ ? true : false;
                    $sun = $this->randomFloat()<=self::SUN_FREQ ? true : false;
                    if (!$mon && !$tue && !$wed && !$thu && !$fri && !$sat && !$sun) {
                        $mon = true;
                    }
                    $ad->setSchedule([
                        [
                            'mon' => $mon,
                            'tue' => $tue,
                            'wed' => $wed,
                            'thu' => $thu,
                            'fri' => $fri,
                            'sat' => $sat,
                            'sun' => $sun,
                            'outwardTime' => $this->getRandomTime(self::MIN_TIME_OUTWARD, self::MAX_TIME_OUTWARD)->format('H:i'),
                            'returnTime' => $this->getRandomTime(self::MIN_TIME_RETURN, self::MAX_TIME_RETURN)->format('H:i')
                        ]
                    ]);
                }

                $subBboxOrigin = $this->getSubBbox($split, 1, $min_lat, $min_lon, $max_lat, $max_lon);
                $min_lat_origin = $subBboxOrigin['min_lat'];
                $min_lon_origin = $subBboxOrigin['min_lon'];
                $max_lat_origin = $subBboxOrigin['max_lat'];
                $max_lon_origin = $subBboxOrigin['max_lon'];
                $origin = new Waypoint();
                $origin->setPosition(0);
                $origin->setDestination(false);
                $origin->setAddress($this->generateFakeAddress($min_lat_origin, $min_lon_origin, $max_lat_origin, $max_lon_origin));

                $subBboxDestination = $this->getSubBbox($split, 2, $min_lat, $min_lon, $max_lat, $max_lon);
                $min_lat_destination = $subBboxDestination['min_lat'];
                $min_lon_destination = $subBboxDestination['min_lon'];
                $max_lat_destination = $subBboxDestination['max_lat'];
                $max_lon_destination = $subBboxDestination['max_lon'];
                $destination = new Waypoint();
                $destination->setPosition(1);
                $destination->setDestination(true);
                // get sure origin <> destination
                do {
                    $destination->setAddress($this->generateFakeAddress($min_lat_destination, $min_lon_destination, $max_lat_destination, $max_lon_destination, $origin->getAddress()->getLatitude(), $origin->getAddress()->getLongitude()));
                } while ($destination->getAddress()->getLongitude() == $origin->getAddress()->getLongitude() && $destination->getAddress()->getLatitude() == $origin->getAddress()->getLatitude());

                $ad->setOutwardWaypoints([$origin->getAddress()->jsonSerialize(),$destination->getAddress()->jsonSerialize()]);

                if (!$ad->isOneWay()) {
                    if ($ad->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                        $ad->setReturnDate($this->getRandomDate($ad->getOutwardDate()->format('Y-m-d'), self::MAX_DATE));
                        $ad->setReturnTime($this->getRandomTime(self::MIN_TIME, self::MAX_TIME)->format("H:i"));
                    }
                    $ad->setReturnDate(\Datetime::createFromFormat('Y-m-d', self::MIN_DATE));
                    $ad->setReturnLimitDate(\Datetime::createFromFormat('Y-m-d', self::MAX_DATE));
                }

                // we create the proposal and its related entities
                $ad = $this->adManager->createProposalFromAd($ad);
            }

            $generated++;
        }
        $this->logger->info('End generating users | ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));

        // 2 - we compute the directions and default values for the generated proposals
        $this->proposalManager->setDirectionsAndDefaultsForAllCriterias(self::BATCH);

        // 3 - we generate the matchings
        $this->proposalManager->createMatchingsForAllProposals();
    }

    private function generatePhoneNumber()
    {
        $number = "";
        if ($this->randomFloat()<0.5) {
            $number = "06";
        } else {
            $number = "07";
        }
        $number .= sprintf("%'.08d", $this->randomFloat() * 100000000);
        return $number;
    }

    /**
     * Get the sub bbox
     *
     * @param integer $splitNb  The number of subdivision
     * @param integer $partNb   The chosen part (1 or 2)
     * @param float  $min_lat   The min lat
     * @param float  $min_lon   The min lon
     * @param float  $max_lat   The max lat
     * @param float  $max_lon   The max lon
     * @return array    The sub bbox
     */
    private function getSubBbox(int $splitNb, int $partNb, float $min_lat, float $min_lon, float $max_lat, float $max_lon)
    {
        // first we get the orientation
        $orientation = $this->getBboxOrientation($min_lat, $min_lon, $max_lat, $max_lon);
        if ($orientation == 0 || $orientation == 1) {
            // square or horizontal => split horizontally, first part at left, second part at right
            if ($partNb == 1) {
                return [
                    'min_lat' => $min_lat,
                    'min_lon' => $min_lon,
                    'max_lat' => $max_lat,
                    'max_lon' => $min_lon + (($max_lon - $min_lon)/$splitNb)
                ];
            } else {
                return [
                    'min_lat' => $min_lat,
                    'min_lon' => $max_lon - (($max_lon - $min_lon)/$splitNb),
                    'max_lat' => $max_lat,
                    'max_lon' => $max_lon
                ];
            }
        } else {
            // vertical => split vertically, first part at bottom, second part at top
            if ($partNb == 1) {
                return [
                    'min_lat' => $min_lat,
                    'min_lon' => $min_lon,
                    'max_lat' => $min_lat + (($max_lat - $min_lat)/$splitNb),
                    'max_lon' => $max_lon
                ];
            } else {
                return [
                    'min_lat' => $max_lat - (($max_lat - $min_lat)/$splitNb),
                    'min_lon' => $min_lon,
                    'max_lat' => $max_lat,
                    'max_lon' => $max_lon
                ];
            }
        }
    }

    /**
     * Get the bbox orientation :
     * 0 : square
     * 1 : horizontal
     * 2 : vertical
     *
     * @param float  $min_lat   The min lat
     * @param float  $min_lon   The min lon
     * @param float  $max_lat   The max lat
     * @param float  $max_lon   The max lon
     * @return int
     */
    private function getBboxOrientation(float $min_lat, float $min_lon, float $max_lat, float $max_lon)
    {
        $xLength = $this->geoTools->haversineGreatCircleDistance($min_lat, $min_lon, $min_lat, $max_lon);
        $yLength = $this->geoTools->haversineGreatCircleDistance($min_lat, $min_lon, $max_lat, $min_lon);
        if ($xLength == $yLength) {
            return 0;
        } elseif ($xLength > $yLength) {
            return 1;
        }
        return 2;
    }



    private function randomFloat($min = 0, $max = 1)
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

    private function getFakeFirstName(int $gender=1)
    {
        $firstNamesLadies = ["adélaïde","adèle","adeline","adrienne","agathe","agnès","aimée","albertine","alexandrie","aline","alphonsine","amarante","ambre","amélie","anastasie","andrée","angèle","angeline","angelique","anne","annette","antoinette","apolline","arianne","arienne","arlette","armelle","arnaude","aude","aurélie","aurore","avril","axelle","béatrice","bénédicte","benjamine","benoite","bernadette","berthe","blanche","carine","carole","caroline","cécile","céleste","célestine","céline","cerise","chantal","chanté","charline","charlotte","chloé","christelle","christine","claire","clarisse","claudette","claudine","clémence","clementine","clothilde","colette","corinne","cosette","cunégonde","danièle","danielle","delphine","denise","désirée","diane","dianne","dieudonnée","donatienne","doriane","dorothée","édith","edwige","eléonore","éliane","élise","élodie","éloise","emeline","émilie","emmanuelle","esmée","eugénie","eulalie","evette","fabienne","faustine","félicie","felicienne","fernande","fifi","flavie","florette","florianne","francine","françoise","frédérique","gabrielle","gaetane","geneviève","georgette","georgine","germaine","gervaise","ghislaine","gigi","gilberte","gisèle","giselle","gisselle","gwenaelle","hélène","héloïse","henriette","honorine","hortense","hyacinthe","inès","irène","irénée","jacinthe","jacqueline","jeanine","jeanne","jeannette","jeannine","joceline","joelle","jolie","josée","josèphe","joséphine","josette","josiane","juliane","julie","julienne","juliette","justine","laure","laurence","laurentine","laurette","léa","léonie","léonne","léontine","liane","lisette","louise","luce","lucie","lucienne","lucile","lucille","lucinde","lucrece","lunete","lydie","madeleine","madeline","manon","marceline","marcelle","marcellette","marcelline","margot","marguerite","marianne","marie","marielle","mariette","marine","marise","marthe","martine","mathilde","maximilienne","mélanie","mélissa","michèle","micheline","michelle","mignon","mirabelle","mireille","monique","morgaine","morgane","myriam","nadia","nadine","natalie","nathalie","nicole","nicolette","ninette","ninon","noella","noelle","noémie","océane","odette","odile","olivie","olympe","oriane","orianne","ouida","pascale","pascaline","paule","paulette","pauline","pénélope","perrine","philippine","placide","raymonde","rébecca","régine","reine","renée","rochelle","rolande","romaine","rosalie","roselle","rosemonde","rosette","rosine","roxane","sabine","salomé","sandrine","sébastienne","seraphine","sévérine","sidonie","simone","solange","sophie","stéphanie","suzanne","suzette","sylvaine","sylviane","sylvianne","sylvie","tatienne","thérèse","toinette","valentine","valérie","véronique","victoire","victorine","vienne","violette","virginie","vivienne","yolande","yseult","yvette","yvonne","zephyrine","zoé"];
        $firstNamesGentlemen = ["absolon","achille","adolphe","adrien","aimé","alain","alexandre","alphonse","amaury","ambroise","amédée","anatole","andré","anselme","antoine","apollinaire","aristide","armand","armel","arnaud","auguste","augustin","aurèle","aurelien","baptiste","barnabé","barthélémy","basile","bastien","baudouin","benoit","bertrand","blaise","brice","célestin","cesaire","césar","charlot","christophe","clément","colombain","constantin","corin","damien","denis","désiré","didier","dieudonné","diodore","dion","donat","donatien","edgard","edmond","édouard","eloi","émile","émilien","ermenegilde","étienne","eugène","eustache","évariste","evrard","fabien","fabrice","felicien","félix","fernand","fiacre","firmin","florentin","franck","françois","frédéric","gaétan","gaspard","gaston","gautier","geoffroi","georges","gérald","gérard","géraud","germain","gervais","ghislain","gilles","godelieve","gratien","grégoire","guillaume","gustave","henri","hercule","hervé","hilaire","honoré","hugues","ignace","iréné","jacques","jean","jean-baptiste","jean-marie","jeannot","jérémie","jérôme","joël","jourdain","jules","julien","juste","laurent","lazare","léandre","léon","léonard","léonce","léopold","lionel","loic","lothaire","louis","loup","luc","lucien","marc","marcel","marcellin","marin","mathieu","matthieu","maxime","maximilien","michel","modeste","modestine","narcisse","nazaire","nicodème","nicolas","noé","noel","olivier","onesime","papillion","pascal","paschal","patrice","perceval","philibert","philippe","pierre","pons","prosper","rainier","raoul","raphaël","régis","rémi","rémy","renard","renaud","rené","reynaud","roch","rodolphe","rodrigue","romain","sébastien","serge","séverin","stéphane","sylvain","sylvestre","telesphore","theirn","théo","théodore","théophile","thibault","thierry","timothée","toussaint","urbain","valère","valéry","vespasien","yves","zacharie"];
        if ($gender == 1) {
            $name = $firstNamesLadies[random_int(0, count($firstNamesLadies)-1)];
        } elseif ($gender == 2) {
            $name = $firstNamesGentlemen[random_int(0, count($firstNamesGentlemen)-1)];
        } else {
            $mixed = array_merge($firstNamesLadies, $firstNamesGentlemen);
            sort($mixed);
            $name = $mixed[random_int(0, count($mixed)-1)];
        }
        return mb_strtoupper(mb_substr($name, 0, 1)) . mb_substr($name, 1);
    }

    private function getFakeLastName()
    {
        $lastNames = ["Martin","Bernard","Thomas","Petit","Robert","Richard","Durand","Dubois","Moreau","Laurent","Simon","Michel","Lefebvre","Leroy","Roux","David","Bertrand","Morel","Fournier","Girard","Bonnet","Dupont","Lambert","Fontaine","Rousseau","Vincent","Muller","Lefevre","Faure","Andre","Mercier","Blanc","Guerin","Boyer","Garnier","Chevalier","Francois","Legrand","Gauthier","Garcia","Perrin","Robin","Clement","Morin","Nicolas","Henry","Roussel","Mathieu","Gautier","Masson","Marchand","Duval","Denis","Dumont","Marie","Lemaire","Noel","Meyer","Dufour","Meunier","Brun","Blanchard","Giraud","Joly","Riviere","Lucas","Brunet","Gaillard","Barbier","Arnaud","Martinez","Gerard","Roche","Renard","Schmitt","Roy","Leroux","Colin","Vidal","Caron","Picard","Roger","Fabre","Aubert","Lemoine","Renaud","Dumas","Lacroix","Olivier","Philippe","Bourgeois","Pierre","Benoit","Rey","Leclerc","Payet","Rolland","Leclercq","Guillaume","Lecomte","Lopez","Jean","Dupuy","Guillot","Hubert","Berger","Carpentier","Sanchez","Dupuis","Moulin","Louis","Deschamps","Huet","Vasseur","Perez","Boucher","Fleury","Royer","Klein","Jacquet","Adam","Paris","Poirier","Marty","Aubry","Guyot","Carre","Charles","Renault","Charpentier","Menard","Maillard","Baron","Bertin","Bailly","Herve","Schneider","Fernandez","Collet","Leger","Bouvier","Julien","Prevost","Millet","Perrot","Daniel","Cousin","Germain","Breton","Besson","Langlois","Remy","Pelletier","Leveque","Perrier","Leblanc","Barre","Lebrun","Marchal","Weber","Mallet","Hamon","Boulanger","Jacob","Monnier","Michaud","Rodriguez","Guichard","Gillet","Etienne","Grondin","Poulain","Tessier","Chevallier","Collin","Chauvin","Bouchet","Gay","Lemaitre","Benard","Marechal","Humbert","Reynaud","Antoine","Hoarau","Perret","Barthelemy","Cordier","Pichon","Lejeune","Gilbert","Lamy","Delaunay","Pasquier","Carlier","Laporte"];
        return ucfirst($lastNames[random_int(0, count($lastNames)-1)]);
    }

    public function getRandomDate(string $start, string $end)
    {
        $start = \DateTime::createFromFormat('Y-m-d', $start);
        $end = \DateTime::createFromFormat('Y-m-d', $end);
        $randomTimestamp = mt_rand($start->getTimestamp(), $end->getTimestamp());
        $randomDate = new \DateTime();
        $randomDate->setTimestamp($randomTimestamp);
        return $randomDate;
    }

    public function getRandomTime(string $start, string $end)
    {
        $start = \DateTime::createFromFormat('H:i', $start);
        $end = \DateTime::createFromFormat('H:i', $end);
        $randomTimestamp = mt_rand($start->getTimestamp(), $end->getTimestamp());
        $randomDate = new \DateTime();
        $randomDate->setTimestamp($randomTimestamp);
        return $randomDate;
    }

    private function getFakeAddress(array $addresses)
    {
        return $addresses[random_int(0, count($addresses)-1)];
    }

    private function generateFakeAddress($min_lat, $min_lon, $max_lat, $max_lon, $ori_lat = null, $ori_lon = null)
    {
        $generated = false;
        $address = null;
        while (!$generated) {
            $lat = $this->randomFloat($min_lat, $max_lat);
            $lon = $this->randomFloat($min_lon, $max_lon);
            if (!is_null($ori_lat) && !is_null($ori_lon)) {
                if ($this->geoTools->haversineGreatCircleDistance($ori_lat, $ori_lon, $lat, $lon)<=self::MIN_DISTANCE) {
                    continue;
                }
            }
            if ($address = $this->geoSearcher->reverseGeoCode($lat, $lon)) {
                if (!is_null($address[0]->getLayer())) {
                    $generated = true;
                }
            }
        }
        return $address[0];
    }

    private function generateFakeAddresses($number, $min_lat, $min_lon, $max_lat, $max_lon)
    {
        $addresses = [];
        while (count($addresses) < $number) {
            $lat = $this->randomFloat($min_lat, $max_lat);
            $lon = $this->randomFloat($min_lon, $max_lon);
            if ($address = $this->geoSearcher->reverseGeoCode($lat, $lon)) {
                $addresses[] = $address[0];
            }
        }
        return $addresses;
    }

    private function normalize($string)
    {
        $table = array(
            'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj', 'Ž'=>'Z', 'ž'=>'z',
            'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
            'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
            'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
            'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
            'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
            'ÿ'=>'y',
        );
        return strtr($string, $table);
    }
}
