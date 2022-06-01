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
 */

namespace App\Import\Controller;

use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Waypoint;
use App\Carpool\Ressource\Ad;
use App\Geography\Service\GeoSearcher;
use App\Import\Entity\UserImport;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller class for API fake import testing purpose.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class FakeUserController extends AbstractController
{
    public const BATCH = 50;
    public const MIN_BIRTHDATE = '1930-01-01';
    public const MAX_BIRTHDATE = '2001-01-01';
    public const MIN_DATE = '2019-11-28';
    public const MAX_DATE = '2020-12-31';
    public const MIN_TIME = '05:00';
    public const MAX_TIME = '23:45';
    public const MIN_TIME_OUTWARD = '05:00';
    public const MAX_TIME_OUTWARD = '10:00';
    public const MIN_TIME_RETURN = '16:00';
    public const MAX_TIME_RETURN = '22:00';
    public const DOMAIN = 'mobicoop-import.org';
    public const PASSWORD = '$2y$10$1n.jspEsnNz7ch4ZgjtT6O2WKXRqEpaL/9QrY5TqafqBtTiM2Xndu'; // "password" bcrypted
    public const ORIGIN = 'ouestgo';
    public const PUNCTUAL_FREQ = 0.2;
    public const ONEWAY_FREQ = 0.3;
    public const DRIVER_FREQ = 0.3;
    public const PASSENGER_FREQ = 0.5;
    public const NB_FREQ = 0.8;

    private $addresses;

    // INSERT INTO user_import (user_id, user_external_id,origin,status,created_date) SELECT id as userid, id as extuserid, "ouestgo",0,"2019-12-13" FROM user

    /**
     * Fake user generator.
     *
     * @Route("/rd/import/user/faker/{number_users}/{number_addresses}/{min_lat}/{min_lon}/{max_lat}/{max_lon}", name="faker_import_user")
     *
     * @param mixed $number_users
     * @param mixed $number_addresses
     * @param mixed $min_lat
     * @param mixed $min_lon
     * @param mixed $max_lat
     * @param mixed $max_lon
     */
    public function faker($number_users, $number_addresses, $min_lat, $min_lon, $max_lat, $max_lon, GeoSearcher $geoSearcher, EntityManagerInterface $entityManager)
    {
        set_time_limit(360);
        $generated = 0;
        $pool = 0;
        $emails = []; // used to avoid duplicates
        $users = [];

        echo 'Start generating fake addresses at '.(new \DateTime('UTC'))->format('Ymd H:i:s.u').'<br />';
        $this->generateFakeAddresses($number_addresses, $min_lat, $min_lon, $max_lat, $max_lon, $geoSearcher);
        echo 'End generating fake addresses at '.(new \DateTime('UTC'))->format('Ymd H:i:s.u').'<br />';

        echo 'Start generating users at '.(new \DateTime('UTC'))->format('Ymd H:i:s.u').'<br />';
        while ($generated < $number_users) {
            // create the user
            $user = new User();
            $user->setStatus(User::STATUS_ACTIVE);
            $user->setGender(random_int(1, 3));
            $user->setBirthDate($this->getRandomDate(self::MIN_BIRTHDATE, self::MAX_BIRTHDATE));
            $user->setGivenName($this->getFakeFirstName());
            $user->setFamilyName($this->getFakeLastName());
            $email = $this->normalize(strtolower($user->getGivenName())).'.'.$this->normalize(strtolower($user->getFamilyName())).'@'.self::DOMAIN;
            // skip if user already exists
            if (in_array($email, $emails)) {
                continue;
            }
            $user->setEmail($email);
            $emails[] = $email;
            $user->setPassword(self::PASSWORD);
            $user->setPhoneDisplay(1);
            $entityManager->persist($user);

            // create the user_import
            $userImport = new UserImport();
            $userImport->setOrigin(self::ORIGIN);
            $userImport->setUserExternalId((string) ($generated + 1));
            $userImport->setUser($user);
            $entityManager->persist($userImport);

            // create the proposal
            $proposal = new Proposal();
            $proposal->setType($this->randomFloat() <= self::ONEWAY_FREQ ? Proposal::TYPE_ONE_WAY : Proposal::TYPE_OUTWARD);
            $proposal->setPrivate(false);

            $criteria = new Criteria();
            $criteria->setFrequency($this->randomFloat() <= self::PUNCTUAL_FREQ ? Criteria::FREQUENCY_PUNCTUAL : Criteria::FREQUENCY_REGULAR);
            $roleFreq = $this->randomFloat();
            $role = $roleFreq < self::PASSENGER_FREQ ? ($roleFreq <= self::DRIVER_FREQ ? Ad::ROLE_DRIVER : Ad::ROLE_PASSENGER) : Ad::ROLE_DRIVER_OR_PASSENGER;
            $criteria->setDriver(Ad::ROLE_DRIVER == $role || Ad::ROLE_DRIVER_OR_PASSENGER == $role);
            $criteria->setPassenger(Ad::ROLE_PASSENGER == $role || Ad::ROLE_DRIVER_OR_PASSENGER == $role);
            if (Criteria::FREQUENCY_PUNCTUAL == $criteria->getFrequency()) {
                $criteria->setFromDate($this->getRandomDate(self::MIN_DATE, self::MAX_DATE));
                $criteria->setFromTime($this->getRandomTime(self::MIN_TIME, self::MAX_TIME));
                $criteria->setStrictDate(true);
            } else {
                $criteria->setFromDate(\DateTime::createFromFormat('Y-m-d', self::MIN_DATE));
                $criteria->setToDate(\DateTime::createFromFormat('Y-m-d', self::MAX_DATE));
                $time = $this->getRandomTime(self::MIN_TIME_OUTWARD, self::MAX_TIME_OUTWARD);
                $criteria->setMonCheck(true);
                $criteria->setTueCheck(true);
                $criteria->setWedCheck(true);
                $criteria->setThuCheck(true);
                $criteria->setFriCheck(true);
                $criteria->setMonTime($time);
                $criteria->setTueTime($time);
                $criteria->setWedTime($time);
                $criteria->setThuTime($time);
                $criteria->setFriTime($time);
            }
            $criteria->setSeatsDriver(3);
            $criteria->setSeatsPassenger(1);
            $proposal->setCriteria($criteria);

            $origin = new Waypoint();
            $origin->setPosition(0);
            $origin->setDestination(false);
            $origin->setAddress(clone $this->getFakeAddress());

            $destination = new Waypoint();
            $destination->setPosition(1);
            $destination->setDestination(true);
            // get sure origin <> destination
            do {
                $destination->setAddress(clone $this->getFakeAddress());
            } while ($destination->getAddress()->getLongitude() == $origin->getAddress()->getLongitude() && $destination->getAddress()->getLatitude() == $origin->getAddress()->getLatitude());

            $proposal->addWaypoint($origin);
            $proposal->addWaypoint($destination);
            $entityManager->persist($proposal);

            $user->addProposal($proposal);
            if (Proposal::TYPE_OUTWARD == $proposal->getType()) {
                $proposalReturn = new Proposal();
                $proposalReturn->setType(Proposal::TYPE_RETURN);
                $proposalReturn->setPrivate(false);

                $criteriaReturn = new Criteria();
                $criteriaReturn->setFrequency($criteria->getFrequency());
                $criteriaReturn->setDriver(Ad::ROLE_DRIVER == $role || Ad::ROLE_DRIVER_OR_PASSENGER == $role);
                $criteriaReturn->setPassenger(Ad::ROLE_PASSENGER == $role || Ad::ROLE_DRIVER_OR_PASSENGER == $role);
                if (Criteria::FREQUENCY_PUNCTUAL == $criteriaReturn->getFrequency()) {
                    $criteriaReturn->setFromDate($this->getRandomDate($criteria->getFromDate()->format('Y-m-d'), self::MAX_DATE));
                    $criteriaReturn->setFromTime($this->getRandomTime($criteria->getFromTime()->format('H:i'), self::MAX_TIME));
                } else {
                    $criteriaReturn->setFromDate($criteria->getFromDate());
                    $criteriaReturn->setToDate($criteria->getToDate());
                    $time = $this->getRandomTime(self::MIN_TIME_RETURN, self::MAX_TIME_RETURN);
                    $criteriaReturn->setMonCheck(true);
                    $criteriaReturn->setTueCheck(true);
                    $criteriaReturn->setWedCheck(true);
                    $criteriaReturn->setThuCheck(true);
                    $criteriaReturn->setFriCheck(true);
                    $criteriaReturn->setMonTime($time);
                    $criteriaReturn->setTueTime($time);
                    $criteriaReturn->setWedTime($time);
                    $criteriaReturn->setThuTime($time);
                    $criteriaReturn->setFriTime($time);
                }
                $criteriaReturn->setSeatsDriver(3);
                $criteriaReturn->setSeatsPassenger(1);
                $proposalReturn->setCriteria($criteriaReturn);

                $originReturn = new Waypoint();
                $originReturn->setPosition(0);
                $originReturn->setDestination(false);
                $originReturn->setAddress(clone $destination->getAddress());

                $destinationReturn = new Waypoint();
                $destinationReturn->setPosition(1);
                $destinationReturn->setDestination(true);
                $destinationReturn->setAddress(clone $origin->getAddress());

                $proposalReturn->addWaypoint($originReturn);
                $proposalReturn->addWaypoint($destinationReturn);
                $entityManager->persist($proposalReturn);
                $proposalReturn->setProposalLinked($proposal);
                $user->addProposal($proposalReturn);
            }

            $entityManager->persist($user);

            $users[] = $user;
            ++$generated;

            // batch
            ++$pool;
            if ($pool >= self::BATCH) {
                $entityManager->flush();
                $entityManager->clear();
                $pool = 0;
            }
        }

        $entityManager->flush();
        $entityManager->clear();

        echo 'End generating users at '.(new \DateTime('UTC'))->format('Ymd H:i:s.u').'<br />';

        // echo "<ul>";
        // foreach ($users as $user) {
        //     echo "<li>" . $user->getGivenName() . " " . $user->getFamilyName() . " - " . $user->getEmail() . " - " . $user->getBirthdate()->format('d/m/Y') . " added</li>";
        // }
        // echo "</ul>";
        // return $geoSearcher->geoCode($this->request->get("input"));
        return new Response();
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

    private function randomFloat($min = 0, $max = 1)
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

    private function getFakeFirstName()
    {
        $firstNames = ['absolon', 'achille', 'adélaïde', 'adèle', 'adeline', 'adolphe', 'adrien', 'adrienne', 'agathe', 'agnès', 'aimé', 'aimée', 'alain', 'albertine', 'alexandre', 'alexandrie', 'aline', 'alphonse', 'alphonsine', 'amarante', 'amaury', 'ambre', 'ambroise', 'amédée', 'amélie', 'anastasie', 'anatole', 'andré', 'andrée', 'angèle', 'angeline', 'angelique', 'anne', 'annette', 'anselme', 'antoine', 'antoinette', 'apollinaire', 'apolline', 'arianne', 'arienne', 'aristide', 'arlette', 'armand', 'armel', 'armelle', 'arnaud', 'arnaude', 'aude', 'auguste', 'augustin', 'aurèle', 'aurélie', 'aurelien', 'aurore', 'avril', 'axelle', 'baptiste', 'barnabé', 'barthélémy', 'basile', 'bastien', 'baudouin', 'béatrice', 'bénédicte', 'benjamine', 'benoit', 'benoite', 'bernadette', 'berthe', 'bertrand', 'blaise', 'blanche', 'brice', 'carine', 'carole', 'caroline', 'cécile', 'céleste', 'célestin', 'célestine', 'céline', 'cerise', 'cesaire', 'césar', 'chantal', 'chanté', 'charline', 'charlot', 'charlotte', 'chloé', 'christelle', 'christine', 'christophe', 'claire', 'clarisse', 'claudette', 'claudine', 'clémence', 'clément', 'clementine', 'clothilde', 'colette', 'colombain', 'constantin', 'corin', 'corinne', 'cosette', 'cunégonde', 'damien', 'danièle', 'danielle', 'delphine', 'denis', 'denise', 'désiré', 'désirée', 'diane', 'dianne', 'didier', 'dieudonné', 'dieudonnée', 'diodore', 'dion', 'donat', 'donatien', 'donatienne', 'doriane', 'dorothée', 'edgard', 'édith', 'edmond', 'édouard', 'edwige', 'eléonore', 'éliane', 'élise', 'élodie', 'eloi', 'éloise', 'emeline', 'émile', 'émilie', 'émilien', 'emmanuelle', 'ermenegilde', 'esmée', 'étienne', 'eugène', 'eugénie', 'eulalie', 'eustache', 'évariste', 'evette', 'evrard', 'fabien', 'fabienne', 'fabrice', 'faustine', 'félicie', 'felicien', 'felicienne', 'félix', 'fernand', 'fernande', 'fiacre', 'fifi', 'firmin', 'flavie', 'florentin', 'florette', 'florianne', 'francine', 'franck', 'françois', 'françoise', 'frédéric', 'frédérique', 'gabrielle', 'gaétan', 'gaetane', 'gaspard', 'gaston', 'gautier', 'geneviève', 'geoffroi', 'georges', 'georgette', 'georgine', 'gérald', 'gérard', 'géraud', 'germain', 'germaine', 'gervais', 'gervaise', 'ghislain', 'ghislaine', 'gigi', 'gilberte', 'gilles', 'gisèle', 'giselle', 'gisselle', 'godelieve', 'gratien', 'grégoire', 'guillaume', 'gustave', 'gwenaelle', 'hélène', 'héloïse', 'henri', 'henriette', 'hercule', 'hervé', 'hilaire', 'honoré', 'honorine', 'hortense', 'hugues', 'hyacinthe', 'ignace', 'inès', 'iréné', 'irène', 'irénée', 'jacinthe', 'jacqueline', 'jacques', 'jean', 'jean-baptiste', 'jeanine', 'jean-marie', 'jeanne', 'jeannette', 'jeannine', 'jeannot', 'jérémie', 'jérôme', 'joceline', 'joël', 'joelle', 'jolie', 'josée', 'josèphe', 'joséphine', 'josette', 'josiane', 'jourdain', 'jules', 'juliane', 'julie', 'julien', 'julienne', 'juliette', 'juste', 'justine', 'laure', 'laurence', 'laurent', 'laurentine', 'laurette', 'lazare', 'léa', 'léandre', 'léon', 'léonard', 'léonce', 'léonie', 'léonne', 'léontine', 'léopold', 'liane', 'lionel', 'lisette', 'loic', 'lothaire', 'louis', 'louise', 'loup', 'luc', 'luce', 'lucie', 'lucien', 'lucienne', 'lucile', 'lucille', 'lucinde', 'lucrece', 'lunete', 'lydie', 'madeleine', 'madeline', 'manon', 'marc', 'marcel', 'marceline', 'marcelle', 'marcellette', 'marcellin', 'marcelline', 'margot', 'marguerite', 'marianne', 'marie', 'marielle', 'mariette', 'marin', 'marine', 'marise', 'marthe', 'martine', 'mathieu', 'mathilde', 'matthieu', 'maxime', 'maximilien', 'maximilienne', 'mélanie', 'mélissa', 'michel', 'michèle', 'micheline', 'michelle', 'mignon', 'mirabelle', 'mireille', 'modeste', 'modestine', 'monique', 'morgaine', 'morgane', 'myriam', 'nadia', 'nadine', 'narcisse', 'natalie', 'nathalie', 'nazaire', 'nicodème', 'nicolas', 'nicole', 'nicolette', 'ninette', 'ninon', 'noé', 'noel', 'noella', 'noelle', 'noémie', 'océane', 'odette', 'odile', 'olivie', 'olivier', 'olympe', 'onesime', 'oriane', 'orianne', 'ouida', 'papillion', 'pascal', 'pascale', 'pascaline', 'paschal', 'patrice', 'paule', 'paulette', 'pauline', 'pénélope', 'perceval', 'perrine', 'philibert', 'philippe', 'philippine', 'pierre', 'placide', 'pons', 'prosper', 'rainier', 'raoul', 'raphaël', 'raymonde', 'rébecca', 'régine', 'régis', 'reine', 'rémi', 'rémy', 'renard', 'renaud', 'rené', 'renée', 'reynaud', 'roch', 'rochelle', 'rodolphe', 'rodrigue', 'rolande', 'romain', 'romaine', 'rosalie', 'roselle', 'rosemonde', 'rosette', 'rosine', 'roxane', 'sabine', 'salomé', 'sandrine', 'sébastien', 'sébastienne', 'seraphine', 'serge', 'séverin', 'sévérine', 'sidonie', 'simone', 'solange', 'sophie', 'stéphane', 'stéphanie', 'suzanne', 'suzette', 'sylvain', 'sylvaine', 'sylvestre', 'sylviane', 'sylvianne', 'sylvie', 'tatienne', 'telesphore', 'theirn', 'théo', 'théodore', 'théophile', 'thérèse', 'thibault', 'thierry', 'timothée', 'toinette', 'toussaint', 'urbain', 'valentine', 'valère', 'valérie', 'valéry', 'véronique', 'vespasien', 'victoire', 'victorine', 'vienne', 'violette', 'virginie', 'vivienne', 'yolande', 'yseult', 'yves', 'yvette', 'yvonne', 'zacharie', 'zephyrine', 'zoé'];
        $name = $firstNames[random_int(0, count($firstNames) - 1)];

        return mb_strtoupper(mb_substr($name, 0, 1)).mb_substr($name, 1);
    }

    private function getFakeLastName()
    {
        $lastNames = ['Martin', 'Bernard', 'Thomas', 'Petit', 'Robert', 'Richard', 'Durand', 'Dubois', 'Moreau', 'Laurent', 'Simon', 'Michel', 'Lefebvre', 'Leroy', 'Roux', 'David', 'Bertrand', 'Morel', 'Fournier', 'Girard', 'Bonnet', 'Dupont', 'Lambert', 'Fontaine', 'Rousseau', 'Vincent', 'Muller', 'Lefevre', 'Faure', 'Andre', 'Mercier', 'Blanc', 'Guerin', 'Boyer', 'Garnier', 'Chevalier', 'Francois', 'Legrand', 'Gauthier', 'Garcia', 'Perrin', 'Robin', 'Clement', 'Morin', 'Nicolas', 'Henry', 'Roussel', 'Mathieu', 'Gautier', 'Masson', 'Marchand', 'Duval', 'Denis', 'Dumont', 'Marie', 'Lemaire', 'Noel', 'Meyer', 'Dufour', 'Meunier', 'Brun', 'Blanchard', 'Giraud', 'Joly', 'Riviere', 'Lucas', 'Brunet', 'Gaillard', 'Barbier', 'Arnaud', 'Martinez', 'Gerard', 'Roche', 'Renard', 'Schmitt', 'Roy', 'Leroux', 'Colin', 'Vidal', 'Caron', 'Picard', 'Roger', 'Fabre', 'Aubert', 'Lemoine', 'Renaud', 'Dumas', 'Lacroix', 'Olivier', 'Philippe', 'Bourgeois', 'Pierre', 'Benoit', 'Rey', 'Leclerc', 'Payet', 'Rolland', 'Leclercq', 'Guillaume', 'Lecomte', 'Lopez', 'Jean', 'Dupuy', 'Guillot', 'Hubert', 'Berger', 'Carpentier', 'Sanchez', 'Dupuis', 'Moulin', 'Louis', 'Deschamps', 'Huet', 'Vasseur', 'Perez', 'Boucher', 'Fleury', 'Royer', 'Klein', 'Jacquet', 'Adam', 'Paris', 'Poirier', 'Marty', 'Aubry', 'Guyot', 'Carre', 'Charles', 'Renault', 'Charpentier', 'Menard', 'Maillard', 'Baron', 'Bertin', 'Bailly', 'Herve', 'Schneider', 'Fernandez', 'Collet', 'Leger', 'Bouvier', 'Julien', 'Prevost', 'Millet', 'Perrot', 'Daniel', 'Cousin', 'Germain', 'Breton', 'Besson', 'Langlois', 'Remy', 'Pelletier', 'Leveque', 'Perrier', 'Leblanc', 'Barre', 'Lebrun', 'Marchal', 'Weber', 'Mallet', 'Hamon', 'Boulanger', 'Jacob', 'Monnier', 'Michaud', 'Rodriguez', 'Guichard', 'Gillet', 'Etienne', 'Grondin', 'Poulain', 'Tessier', 'Chevallier', 'Collin', 'Chauvin', 'Bouchet', 'Gay', 'Lemaitre', 'Benard', 'Marechal', 'Humbert', 'Reynaud', 'Antoine', 'Hoarau', 'Perret', 'Barthelemy', 'Cordier', 'Pichon', 'Lejeune', 'Gilbert', 'Lamy', 'Delaunay', 'Pasquier', 'Carlier', 'Laporte'];

        return ucfirst($lastNames[random_int(0, count($lastNames) - 1)]);
    }

    private function getFakeAddress()
    {
        return $this->addresses[random_int(0, count($this->addresses) - 1)];
    }

    private function generateFakeAddresses($number, $min_lat, $min_lon, $max_lat, $max_lon, GeoSearcher $geoSearcher)
    {
        $addresses = [];
        while (count($addresses) < $number) {
            $lat = $this->randomFloat($min_lat, $max_lat);
            $lon = $this->randomFloat($min_lon, $max_lon);
            if ($address = $geoSearcher->reverseGeoCode($lat, $lon)) {
                $addresses[] = $address;
            }
        }
        $this->addresses = $addresses;
    }

    private function normalize($string)
    {
        $table = [
            'Š' => 'S', 'š' => 's', 'Ð' => 'Dj', 'Ž' => 'Z', 'ž' => 'z',
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
            'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O',
            'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c', 'è' => 'e', 'é' => 'e',
            'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o',
            'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'ý' => 'y', 'þ' => 'b',
            'ÿ' => 'y',
        ];

        return strtr($string, $table);
    }
}
