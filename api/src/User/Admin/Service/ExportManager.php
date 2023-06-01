<?php

namespace App\User\Admin\Service;

use App\Auth\Entity\AuthItem;
use App\Carpool\Repository\ProposalRepository;
use App\Community\Repository\CommunityUserRepository;
use App\User\Entity\User;
use App\User\Entity\UserExport;
use App\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Security;

class ExportManager
{
    public const ALLOWED_FILTERS = ['community', 'isHitchHiker'];
    public const MAXIMUM_NUMBER_OF_ROLES = 5;

    /**
     * @var User
     */
    private $_authenticatedUser;

    /**
     * @var CommunityUserRepository
     */
    private $_communityUserRepository;

    /**
     * @var EntityManagerInterface
     */
    private $_em;

    /**
     * @var array
     */
    private $_filters = [];

    /**
     * @var ProposalRepository
     */
    private $_proposalRepository;

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
    private $_authenticatedUserRestrictionTerritories;

    /**
     * @var User
     */
    private $_currentUser;

    /**
     * @var UserExport
     */
    private $_currentUserExport;

    public function __construct(
        Security $security,
        RequestStack $requestStack,
        EntityManagerInterface $em,
        CommunityUserRepository $communityUserRepository,
        ProposalRepository $proposalRepository,
        UserRepository $userRepository
    ) {
        if (is_null($security->getUser())) {
            throw new BadRequestHttpException('There is no authenticated User');
        }

        $this->_authenticatedUser = $security->getUser();
        $this->_request = $requestStack->getCurrentRequest();
        $this->_em = $em;
        $this->_communityUserRepository = $communityUserRepository;
        $this->_proposalRepository = $proposalRepository;
        $this->_userRepository = $userRepository;

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

    private function _isUserSolidary(User $user): string
    {
        $solidaryUser = $user->getSolidaryUser();

        if (!is_null($solidaryUser)) {
            switch (true) {
                case $solidaryUser->isBeneficiary() && (!$solidaryUser->isVolunteer() || is_null($solidaryUser->isVolunteer())):
                    return UserExport::SOLIDARY_PASSENGER;

                case (!$solidaryUser->isBeneficiary() || is_null($solidaryUser->isBeneficiary())) && $solidaryUser->isVolunteer():
                    return UserExport::SOLIDARY_DRIVER;

                case $solidaryUser->isBeneficiary() && $solidaryUser->isBeneficiary():
                    return UserExport::SOLIDARY_TWICE;
            }
        }

        return UserExport::FALSE;
    }

    private function _setAuthenticatedUserRestrictionTerritories(): self
    {
        $userTerritoryAuthAssignments = array_filter($this->_authenticatedUser->getUserAuthAssignments(), function ($assignment) {
            return 'admin_user_export_all' === $assignment->getAuthItem()->getName() && !is_null($assignment->getTerritory());
        });

        $this->_authenticatedUserRestrictionTerritories = array_map(function ($assignment) {
            return $assignment->getTerritory()->getId();
        }, $userTerritoryAuthAssignments);

        return $this;
    }

    private function _setFilters(): self
    {
        $parameters = $this->_request->query->all();

        foreach ($parameters as $key => $parameter) {
            if (!in_array($key, self::ALLOWED_FILTERS)) {
                throw new BadRequestHttpException("The filter {$key} is not allowed");
            }

            $this->_filters[$key] = $parameter;
        }

        return $this;
    }

    private function _transformUser(): UserExport
    {
        $this->_currentUserExport = new UserExport();

        $this->_currentUserExport->setFamilyName($this->_currentUser->getFamilyName());
        $this->_currentUserExport->setGivenName($this->_currentUser->getGivenName());
        $this->_currentUserExport->setGender($this->_currentUser->getGender());
        $this->_currentUserExport->setEmail($this->_currentUser->getEmail());
        $this->_currentUserExport->setTelephone($this->_currentUser->getTelephone());
        $this->_currentUserExport->setBirthDate($this->_currentUser->getBirthDate());
        $this->_currentUserExport->setRegistrationDate($this->_currentUser->getCreatedDate());
        $this->_currentUserExport->setLastActivityDate($this->_currentUser->getLastActivityDate());
        $this->_currentUserExport->setNewsletterSubscription($this->_currentUser->hasNewsSubscription());

        $maxValidityDate = $this->_proposalRepository->getUserMaxValidityAnnonceDate($this->_currentUser);
        $this->_currentUserExport->setMaxValidityAnnonceDate(isset($maxValidityDate['MaxValiditeAnnonce']) ? new \DateTime($maxValidityDate['MaxValiditeAnnonce']) : null);

        $adresses = array_values(array_filter($this->_currentUser->getAddresses(), function ($address) {
            return $address->isHome();
        }));
        $this->_currentUserExport->setAddressLocality(!empty($adresses) ? $adresses[0]->getAddressLocality() : null);

        $this->_currentUserExport->setSolidaryUser($this->_isUserSolidary($this->_currentUser));

        $communities = $this->_communityUserRepository->findUserCommunities($this->_currentUser);
        $this->_currentUserExport->setCommunity1(isset($communities['Communauté1']) ? $communities['Communauté1'] : null);
        $this->_currentUserExport->setCommunity1(isset($communities['Communauté2']) ? $communities['Communauté2'] : null);
        $this->_currentUserExport->setCommunity1(isset($communities['Communauté3']) ? $communities['Communauté3'] : null);

        $proposals = $this->_proposalRepository->userExportActiveProposal($this->_currentUser);

        $this->_currentUserExport->setCarpool1OriginLocality(isset($proposals['Annonce1_Origine']) ? $proposals['Annonce1_Origine'] : null);
        $this->_currentUserExport->setCarpool1DestinationLocality(isset($proposals['Annonce1_Destination']) ? $proposals['Annonce1_Destination'] : null);
        $this->_currentUserExport->setCarpool1Frequency(isset($proposals['Annonce1_Frequence']) ? $proposals['Annonce1_Frequence'] : null);
        $this->_currentUserExport->setCarpool2OriginLocality(isset($proposals['Annonce2_Origine']) ? $proposals['Annonce2_Origine'] : null);
        $this->_currentUserExport->setCarpool2DestinationLocality(isset($proposals['Annonce2_Destination']) ? $proposals['Annonce2_Destination'] : null);
        $this->_currentUserExport->setCarpool2Frequency(isset($proposals['Annonce2_Frequence']) ? $proposals['Annonce2_Frequence'] : null);
        $this->_currentUserExport->setCarpool3OriginLocality(isset($proposals['Annonce3_Origine']) ? $proposals['Annonce3_Origine'] : null);
        $this->_currentUserExport->setCarpool3DestinationLocality(isset($proposals['Annonce3_Destination']) ? $proposals['Annonce3_Destination'] : null);
        $this->_currentUserExport->setCarpool3Frequency(isset($proposals['Annonce3_Frequence']) ? $proposals['Annonce3_Frequence'] : null);

        $this->_setCurrentUserExportRoles();

        return $this->_currentUserExport;
    }

    private function _setCurrentUserExportRoles(): void
    {
        foreach ($this->_em->getRepository(AuthItem::class)->findByType(2) as $role) {
            foreach ($this->_currentUser->getUserAuthAssignments() as $currentUserRole) {
                if ($currentUserRole->getAuthItem() === $role) {
                    $setter = $this->_getSetter($role->getName());
                    $this->_currentUserExport->{$setter}(
                        !is_null($currentUserRole->getTerritory())
                        ? $currentUserRole->getTerritory()->getName() : UserExport::TRUE
                    );
                }
            }
        }
    }

    private function _getSetter(string $baseRoleName): string
    {
        $roleNameParts = explode('_', $baseRoleName);

        $setter = 'set';

        foreach ($roleNameParts as $part) {
            $setter .= ucfirst(strtolower($part));
        }

        return $setter;
    }
}
