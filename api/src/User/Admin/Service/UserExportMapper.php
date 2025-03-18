<?php

namespace App\User\Admin\Service;

use App\User\Entity\UserExport;

class UserExportMapper
{
    public static function fromArray(array $user): UserExport
    {
        $exportedUser = new UserExport();

        $exportedUser->setId((int) $user['userId']);
        $exportedUser->setFamilyName($user['familyName']);
        $exportedUser->setGivenName($user['givenName']);
        $exportedUser->setGender($user['gender']);
        $exportedUser->setEmail($user['email']);
        $exportedUser->setTelephone($user['telephone']);
        $exportedUser->setBirthDate(new \DateTime($user['birthDate']));
        $exportedUser->setRegistrationDate('' != trim($user['registrationDate']) ? new \DateTime($user['registrationDate']) : null);
        $exportedUser->setLastActivityDate('' != trim($user['lastActivityDate']) ? new \DateTime($user['lastActivityDate']) : null);
        $exportedUser->setNewsletterSubscription($user['newsletterSubscription']);
        $exportedUser->setMaxValidityAnnonceDate('' != trim($user['maxValidityAnnonceDate']) ? new \DateTime($user['maxValidityAnnonceDate']) : null);
        $exportedUser->setAddressLocality($user['addressLocality']);
        $exportedUser->setSolidaryUser($user['solidaryUser']);

        $exportedUser->setCommunity1($user['community1']);
        $exportedUser->setCommunity2($user['community2']);
        $exportedUser->setCommunity3($user['community3']);

        $exportedUser->setCarpool1OriginLocality($user['carpool1OriginLocality']);
        $exportedUser->setCarpool1DestinationLocality($user['carpool1DestinationLocality']);
        $exportedUser->setCarpool1Frequency($user['carpool1Frequency']);
        $exportedUser->setcarpool1RoleDriver($user['carpool1RoleDriver']);
        $exportedUser->setcarpool1RolePassenger($user['carpool1RolePassenger']);
        $exportedUser->setCarpool2OriginLocality($user['carpool2OriginLocality']);
        $exportedUser->setCarpool2DestinationLocality($user['carpool2DestinationLocality']);
        $exportedUser->setCarpool2Frequency($user['carpool2Frequency']);
        $exportedUser->setcarpool2RoleDriver($user['carpool2RoleDriver']);
        $exportedUser->setcarpool2RolePassenger($user['carpool2RolePassenger']);
        $exportedUser->setCarpool3OriginLocality($user['carpool3OriginLocality']);
        $exportedUser->setCarpool3DestinationLocality($user['carpool3DestinationLocality']);
        $exportedUser->setCarpool3Frequency($user['carpool3Frequency']);
        $exportedUser->setcarpool3RoleDriver($user['carpool3RoleDriver']);
        $exportedUser->setcarpool3RolePassenger($user['carpool3RolePassenger']);

        $exportedUser->setRezoPouceUse($user['rezopouceUse']);
        $exportedUser->setIdentityStatus($user['identityStatus']);

        $exportedUser->setRoleSuperAdmin($user['roleSuperAdmin']);
        $exportedUser->setRoleAdmin($user['roleAdmin']);
        $exportedUser->setRoleUserRegisteredFull($user['roleUserRegisteredFull']);
        $exportedUser->setRoleUserRegisteredMinimal($user['roleUserRegisteredMinimal']);
        $exportedUser->setRoleUser($user['roleUser']);
        $exportedUser->setRoleMassMatch($user['roleMassMatch']);
        $exportedUser->setRoleCommunityManager($user['roleCommunityManager']);
        $exportedUser->setRoleCommunityManagerPublic($user['roleCommunityManagerPublic']);
        $exportedUser->setRoleSuperCommunityManagerPublic($user['roleSuperCommunityManagerPublic']);
        $exportedUser->setRoleCommunityManagerPrivate($user['roleCommunityManagerPrivate']);
        $exportedUser->setRoleCommunityManagerPrivate($user['roleSolidaryOperator']);
        $exportedUser->setRoleSolidaryVolunteer($user['roleSolidaryVolunteer']);
        $exportedUser->setRoleSolidaryBeneficiary($user['roleSolidaryBeneficiary']);
        $exportedUser->setRoleCommunicationManager($user['roleCommunicationManager']);
        $exportedUser->setRoleSolidaryVolunteerCandidate($user['roleSolidaryVolunteerCandidate']);
        $exportedUser->setRoleSolidaryBeneficiaryCandidate($user['roleSolidaryBeneficiaryCandidate']);
        $exportedUser->setRoleInteroperability($user['roleInteroperability']);
        $exportedUser->setRoleSolidaryAdmin($user['roleSolidaryAdmin']);
        $exportedUser->setRoleTerritoryConsultant($user['roleTerritoryConsultant']);

        return $exportedUser;
    }
}
