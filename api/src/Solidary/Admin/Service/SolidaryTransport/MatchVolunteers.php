<?php
namespace App\Solidary\Admin\Service\SolidaryTransport;

use App\Solidary\Entity\Solidary;
use App\Solidary\Entity\SolidaryMatching;
use App\Solidary\Entity\SolidarySolution;
use App\Solidary\Entity\SolidaryUser;

class MatchVolunteers {
    /**
     * Remove the volunteers that are already in the matchings (maybe find a way to do it in the sql request ???)
     *
     * @param SolidaryUser[] $volunteers
     * @return SolidaryUser[]
     */
    public function getNewVolunteers(Solidary $solidary, $volunteers): array{
        $newVolunteers = [];

        foreach ($volunteers as $volunteer) {
            /**
             * @var SolidaryUser $acceptedVolunteer
             */
            foreach ($volunteer->getSolidaryMatchings() as $solidaryMatching) {
                /**
                 * @var SolidaryMatching $solidaryMatching
                 */
                if ($solidaryMatching->getSolidary()->getId() === $solidary->getId()) {
                    // this volunteer is already in matchings
                    // we skip the parent loop and check the next volunteer
                    continue 2;
                }
            }

            array_push($newVolunteers, $volunteer);
        }

        return $newVolunteers;
    }

    /**
     * Remove the volunteers that are no longer in the matchings
     * 1. Retrieve the solidarity matches already matched with Solidary
     * 2. Identify the matches that no longer match
     * 3. Remove these matches from Solidary
     *
     * @param Solidary $solidary
     * @param SolidaryUser[] $volunteers
     * @return Solidary
     */
    public function removeVolunteerNoLongerMatch(Solidary $solidary, array $volunteers) {
        /**
         * @var SolidaryMatching[]
         */
        $originalSolidaryMatchings = $solidary->getSolidaryMatchings();

        /**
         * @var SolidaryMatching[]
         */
        $matchsNoLongerMatch = [];

        foreach ($originalSolidaryMatchings as $originalSolidaryMatching) {
            $originalMatchedSolidaryUser = $originalSolidaryMatching->getSolidaryUser();
            if (
                is_null($originalMatchedSolidaryUser) ||
                $this->_isSolidaryUserSolidarySolution($solidary, $originalMatchedSolidaryUser)     //
            ) {
                continue;
            }

            if (empty(array_filter($volunteers, function (SolidaryUser $volunteer) use ($originalMatchedSolidaryUser) {
                return $volunteer->getId() === $originalMatchedSolidaryUser->getId();
            }))) {
                array_push($matchsNoLongerMatch, $originalSolidaryMatching);
            }
        }

        foreach ($matchsNoLongerMatch as $matchNoLongerMatch) {
            foreach ($originalSolidaryMatchings as $key => $originalSolidaryMatching) {
                if ($originalSolidaryMatching->getId() === $matchNoLongerMatch->getId()) {
                    $solidary->removeSolidaryMatching($originalSolidaryMatching);
                    break;
                }
            }
        }

        return $solidary;
    }

    /**
     * Returns whether a user is a transport solution for a solidarity request
     *
     * @param Solidary $solidary
     * @param SolidaryUser $solidaryUser
     * @return boolean
     */
    private function _isSolidaryUserSolidarySolution(Solidary $solidary, SolidaryUser $solidaryUser): bool {
        $solidarySolutions = $solidary->getSolidarySolutions();
        return !empty(array_filter($solidarySolutions, function (SolidarySolution $solidarySolution) use ($solidaryUser) {
            $matchedSolidaryUser = $solidarySolution->getSolidaryMatching()->getSolidaryUser();
            return $matchedSolidaryUser->getId() === $solidaryUser->getId();
        }));
    }
}
