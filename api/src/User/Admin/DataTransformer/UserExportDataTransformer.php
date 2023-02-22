<?php

namespace App\User\Admin\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\Carpool\Repository\ProposalRepository;
use App\Community\Repository\CommunityUserRepository;
use App\User\Admin\Resource\UserExport;
use App\User\Entity\User;

class UserExportDataTransformer implements DataTransformerInterface
{
    /**
     * @var CommunityUserRepository
     */
    private $_communityUserRepository;

    /**
     * @var ProposalRepository
     */
    private $_proposalRepository;

    public function __construct(CommunityUserRepository $communityUserRepository, ProposalRepository $proposalRepository)
    {
        $this->_communityUserRepository = $communityUserRepository;
        $this->_proposalRepository = $proposalRepository;
    }

    public function transform($object, string $to, array $context = [])
    {
        $userExport = new UserExport();

        $userExport->setFamilyName($object->getFamilyName());
        $userExport->setGivenName($object->getGivenName());
        $userExport->setGender($object->getGender());
        $userExport->setEmail($object->getEmail());
        $userExport->setTelephone($object->getTelephone());
        $userExport->setBirthDate($object->getBirthDate());
        $userExport->setRegistrationDate($object->getCreatedDate());
        $userExport->setLastActivityDate($object->getLastActivityDate());
        $userExport->setNewsletterSubscription($object->hasNewsSubscription());

        $maxValidityDate = $this->_proposalRepository->getUserMaxValidityAnnonceDate($object);
        $userExport->setMaxValidityAnnonceDate(isset($maxValidityDate['MaxValiditeAnnonce']) ? new \DateTime($maxValidityDate['MaxValiditeAnnonce']) : null);

        $adresses = array_values(array_filter($object->getAddresses(), function ($address) {
            return $address->isHome();
        }));
        $userExport->setAddressLocality(!empty($adresses) ? $adresses[0]->getAddressLocality() : null);

        $userExport->setSolidaryUser($this->isUserSolidary($object));

        $communities = $this->_communityUserRepository->findUserCommunities($object);
        $userExport->setCommunity1(isset($communities['Communauté1']) ? $communities['Communauté1'] : null);
        $userExport->setCommunity1(isset($communities['Communauté2']) ? $communities['Communauté2'] : null);
        $userExport->setCommunity1(isset($communities['Communauté3']) ? $communities['Communauté3'] : null);

        $proposals = $this->_proposalRepository->userExportActiveProposal($object);

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

    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return UserExport::class === $to && $data instanceof User;
    }

    private function isUserSolidary(User $user): string
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
}
