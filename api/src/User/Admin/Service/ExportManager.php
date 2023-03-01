<?php

namespace App\User\Admin\Service;

use App\Carpool\Repository\ProposalRepository;
use App\Community\Repository\CommunityUserRepository;
use App\User\Entity\User;
use App\User\Entity\UserExport;
use App\User\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Security;

class ExportManager
{
    /**
     * @var User
     */
    private $_authenticatedUser;

    /**
     * @var CommunityUserRepository
     */
    private $_communityUserRepository;

    /**
     * @var ProposalRepository
     */
    private $_proposalRepository;

    /**
     * @var UserRepository
     */
    private $_userRepository;

    /**
     * @var int[]
     */
    private $_authenticatedUserRestrictionTerritories;

    public function __construct(Security $security, CommunityUserRepository $communityUserRepository, ProposalRepository $proposalRepository, UserRepository $userRepository)
    {
        if (is_null($security->getUser())) {
            throw new BadRequestHttpException('There is no authenticated User');
        }

        $this->_authenticatedUser = $security->getUser();
        $this->_communityUserRepository = $communityUserRepository;
        $this->_proposalRepository = $proposalRepository;
        $this->_userRepository = $userRepository;

        $this->_setAuthenticatedUserRestrictionTerritories();
    }

    public function exportAll()
    {
        $users = !empty($this->_authenticatedUserRestrictionTerritories)
            ? $this->_userRepository->findTerritoriesUsers($this->_authenticatedUserRestrictionTerritories)
            : $this->_userRepository->findAll();

        $usersToExport = [];

        foreach ($users as $user) {
            array_push($usersToExport, $this->_transformUser($user));
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

    private function _transformUser(User $user): UserExport
    {
        $userExport = new UserExport();

        $userExport->setFamilyName($user->getFamilyName());
        $userExport->setGivenName($user->getGivenName());
        $userExport->setGender($user->getGender());
        $userExport->setEmail($user->getEmail());
        $userExport->setTelephone($user->getTelephone());
        $userExport->setBirthDate($user->getBirthDate());
        $userExport->setRegistrationDate($user->getCreatedDate());
        $userExport->setLastActivityDate($user->getLastActivityDate());
        $userExport->setNewsletterSubscription($user->hasNewsSubscription());

        $maxValidityDate = $this->_proposalRepository->getUserMaxValidityAnnonceDate($user);
        $userExport->setMaxValidityAnnonceDate(isset($maxValidityDate['MaxValiditeAnnonce']) ? new \DateTime($maxValidityDate['MaxValiditeAnnonce']) : null);

        $adresses = array_values(array_filter($user->getAddresses(), function ($address) {
            return $address->isHome();
        }));
        $userExport->setAddressLocality(!empty($adresses) ? $adresses[0]->getAddressLocality() : null);

        $userExport->setSolidaryUser($this->_isUserSolidary($user));

        $communities = $this->_communityUserRepository->findUserCommunities($user);
        $userExport->setCommunity1(isset($communities['Communauté1']) ? $communities['Communauté1'] : null);
        $userExport->setCommunity1(isset($communities['Communauté2']) ? $communities['Communauté2'] : null);
        $userExport->setCommunity1(isset($communities['Communauté3']) ? $communities['Communauté3'] : null);

        $proposals = $this->_proposalRepository->userExportActiveProposal($user);

        $userExport->setCarpool1OriginLocality(isset($proposals['Annonce1_Origine']) ? $proposals['Annonce1_Origine'] : null);
        $userExport->setCarpool1DestinationLocality(isset($proposals['Annonce1_Destination']) ? $proposals['Annonce1_Destination'] : null);
        $userExport->setCarpool1Frequency(isset($proposals['Annonce1_Frequence']) ? $proposals['Annonce1_Frequence'] : null);
        $userExport->setCarpool2OriginLocality(isset($proposals['Annonce2_Origine']) ? $proposals['Annonce2_Origine'] : null);
        $userExport->setCarpool2DestinationLocality(isset($proposals['Annonce2_Destination']) ? $proposals['Annonce2_Destination'] : null);
        $userExport->setCarpool2Frequency(isset($proposals['Annonce2_Frequence']) ? $proposals['Annonce2_Frequence'] : null);
        $userExport->setCarpool3OriginLocality(isset($proposals['Annonce3_Origine']) ? $proposals['Annonce3_Origine'] : null);
        $userExport->setCarpool3DestinationLocality(isset($proposals['Annonce3_Destination']) ? $proposals['Annonce3_Destination'] : null);
        $userExport->setCarpool3Frequency(isset($proposals['Annonce3_Frequence']) ? $proposals['Annonce3_Frequence'] : null);

        return $userExport;
    }
}
