<?php

namespace App\User\Admin\Service;

use App\User\Entity\User;
use App\User\Entity\UserExport;
use App\User\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Security;

class ExportManager
{
    public const ALLOWED_FILTERS = [self::FILTER_COMMUNITY, self::FILTER_HITCHHIKING, self::FILTER_TERRITORY];
    public const MAXIMUM_NUMBER_OF_ROLES = 5;
    public const FILTER_COMMUNITY = 'community';
    public const FILTER_HITCHHIKING = 'isHitchHiker';
    public const FILTER_TERRITORY = 'territory';

    /**
     * @var User
     */
    private $_authenticatedUser;

    /**
     * @var array
     */
    private $_filters = [];

    /**
     * @var Request
     */
    private $_request;

    /**
     * @var UserRepository
     */
    private $_userRepository;

    /**
     * @var int[]
     */
    private $_authenticatedUserRestrictionTerritories = [];

    /**
     * @var int[]
     */
    private $_territoriesFilterBases = [];

    private $_currentUser;

    /**
     * @var UserExport
     */
    private $_currentUserExport;

    /**
     * @var bool
     */
    private $_isHitchhiking;

    public function __construct(
        Security $security,
        RequestStack $requestStack,
        UserRepository $userRepository
    ) {
        if (is_null($security->getUser())) {
            throw new BadRequestHttpException('There is no authenticated User');
        }

        $this->_authenticatedUser = $security->getUser();
        $this->_request = $requestStack->getCurrentRequest();
        $this->_userRepository = $userRepository;
        $this->_isHitchhiking = false;
        $this->_setFilters();

        $this->_setAuthenticatedUserRestrictionTerritories();
    }

    public function exportAll()
    {
        $users = $this->_userRepository->findForExport($this->_filters, $this->_authenticatedUserRestrictionTerritories);

        $usersToExport = [];

        foreach ($users as $user) {
            $this->_currentUser = $user;

            array_push($usersToExport, $this->_transformUser());
        }

        return $usersToExport;
    }

    private function _setAuthenticatedUserRestrictionTerritories(): self
    {
        $userTerritoryAuthAssignments = array_filter($this->_authenticatedUser->getUserAuthAssignments(), function ($assignment) {
            return 'admin_user_export_all' === $assignment->getAuthItem()->getName() && !is_null($assignment->getTerritory());
        });

        $this->_authenticatedUserRestrictionTerritories = array_map(function ($assignment) {
            return $assignment->getTerritory()->getId();
        }, $userTerritoryAuthAssignments);

        if (empty($this->_authenticatedUserRestrictionTerritories)) {
            $this->_authenticatedUserRestrictionTerritories = $this->_territoriesFilterBases;
        }

        return $this;
    }

    private function _setFilters(): self
    {
        $parameters = $this->_request->query->all();

        foreach ($parameters as $key => $parameter) {
            if (!in_array($key, self::ALLOWED_FILTERS)) {
                throw new BadRequestHttpException("The filter {$key} is not allowed");
            }

            switch ($key) {
                case self::FILTER_HITCHHIKING:
                    $this->_isHitchhiking = true;

                    break;

                case self::FILTER_TERRITORY:
                    $this->_territoriesFilterBases = array_map(
                        function ($id) {
                            return intval($id);
                        },
                        explode(',', $parameter)
                    );

                    break;
            }

            $this->_filters[$key] = $parameter;
        }

        return $this;
    }

    private function _transformUser(): UserExport
    {
        $this->_currentUserExport = new UserExport();

        $this->_currentUserExport->setId((int) $this->_currentUser['userId']);
        $this->_currentUserExport->setFamilyName($this->_currentUser['familyName']);
        $this->_currentUserExport->setGivenName($this->_currentUser['givenName']);
        $this->_currentUserExport->setGender($this->_currentUser['gender']);
        $this->_currentUserExport->setEmail($this->_currentUser['email']);
        $this->_currentUserExport->setTelephone($this->_currentUser['telephone']);
        $this->_currentUserExport->setBirthDate(new \DateTime($this->_currentUser['birthDate']));
        $this->_currentUserExport->setRegistrationDate(new \DateTime($this->_currentUser['registrationDate']));
        $this->_currentUserExport->setLastActivityDate(new \DateTime($this->_currentUser['lastActivityDate']));
        $this->_currentUserExport->setNewsletterSubscription($this->_currentUser['newsletterSubscription']);
        $this->_currentUserExport->setMaxValidityAnnonceDate(new \DateTime($this->_currentUser['maxValidityAnnonceDate']));
        $this->_currentUserExport->setAddressLocality($this->_currentUser['addressLocality']);
        $this->_currentUserExport->setSolidaryUser($this->_currentUser['solidaryUser']);

        $this->_currentUserExport->setCommunity1($this->_currentUser['community1']);
        $this->_currentUserExport->setCommunity2($this->_currentUser['community2']);
        $this->_currentUserExport->setCommunity3($this->_currentUser['community3']);

        $this->_currentUserExport->setCarpool1OriginLocality($this->_currentUser['carpool1OriginLocality']);
        $this->_currentUserExport->setCarpool1DestinationLocality($this->_currentUser['carpool1DestinationLocality']);
        $this->_currentUserExport->setCarpool1Frequency($this->_currentUser['carpool1Frequency']);
        $this->_currentUserExport->setCarpool2OriginLocality($this->_currentUser['carpool2OriginLocality']);
        $this->_currentUserExport->setCarpool2DestinationLocality($this->_currentUser['carpool2DestinationLocality']);
        $this->_currentUserExport->setCarpool2Frequency($this->_currentUser['carpool2Frequency']);
        $this->_currentUserExport->setCarpool3OriginLocality($this->_currentUser['carpool3OriginLocality']);
        $this->_currentUserExport->setCarpool3DestinationLocality($this->_currentUser['carpool3DestinationLocality']);
        $this->_currentUserExport->setCarpool3Frequency($this->_currentUser['carpool3Frequency']);

        $this->_currentUserExport->setRezoPouceUse($this->_currentUser['rezopouceUse']);
        $this->_currentUserExport->setIdentityStatus($this->_currentUser['identityStatus']);

        $this->_currentUserExport->setRoleSuperAdmin($this->_currentUser['roleSuperAdmin']);
        $this->_currentUserExport->setRoleAdmin($this->_currentUser['roleAdmin']);
        $this->_currentUserExport->setRoleUserRegisteredFull($this->_currentUser['roleUserRegisteredFull']);
        $this->_currentUserExport->setRoleUserRegisteredMinimal($this->_currentUser['roleUserRegisteredMinimal']);
        $this->_currentUserExport->setRoleUser($this->_currentUser['roleUser']);
        $this->_currentUserExport->setRoleMassMatch($this->_currentUser['roleMassMatch']);
        $this->_currentUserExport->setRoleCommunityManager($this->_currentUser['roleCommunityManager']);
        $this->_currentUserExport->setRoleCommunityManagerPublic($this->_currentUser['roleCommunityManagerPublic']);
        $this->_currentUserExport->setRoleCommunityManagerPrivate($this->_currentUser['roleCommunityManagerPrivate']);
        $this->_currentUserExport->setRoleCommunityManagerPrivate($this->_currentUser['roleSolidaryOperator']);
        $this->_currentUserExport->setRoleSolidaryVolunteer($this->_currentUser['roleSolidaryVolunteer']);
        $this->_currentUserExport->setRoleSolidaryBeneficiary($this->_currentUser['roleSolidaryBeneficiary']);
        $this->_currentUserExport->setRoleCommunicationManager($this->_currentUser['roleCommunicationManager']);
        $this->_currentUserExport->setRoleSolidaryVolunteerCandidate($this->_currentUser['roleSolidaryVolunteerCandidate']);
        $this->_currentUserExport->setRoleSolidaryBeneficiaryCandidate($this->_currentUser['roleSolidaryBeneficiaryCandidate']);
        $this->_currentUserExport->setRoleInteroperability($this->_currentUser['roleInteroperability']);
        $this->_currentUserExport->setRoleSolidaryAdmin($this->_currentUser['roleSolidaryAdmin']);
        $this->_currentUserExport->setRoleTerritoryConsultant($this->_currentUser['roleTerritoryConsultant']);

        return $this->_currentUserExport;
    }
}
