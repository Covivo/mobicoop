<?php

namespace App\User\Entity;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

class UserExport
{
    public const TRUE = 'Oui';
    public const FALSE = 'Non';

    public const SOLIDARY_PASSENGER = 'Passager solidaire';
    public const SOLIDARY_DRIVER = 'Transporteur bénévole';
    public const SOLIDARY_TWICE = 'Passager solidaire ET Transporteur bénévole';

    public const HITCHHIKING_DRIVER = 'Conducteur';
    public const HITCHHIKING_PASSENGER = 'Passager';
    public const HITCHHIKING_BOTH = 'Passager et Conducteur';
    public const HITCHHIKING_NONE = 'Aucun';

    public const IDENTITY_REJECTED = 'Rejetée';
    public const IDENTITY_VERIFIED = 'Vérifiée';
    public const IDENTITY_UNDER_REVIEW = 'En attente de vérification';
    public const IDENTITY_NONE = 'Non-communiquée';
    public const IDENTITY_CANCELED = 'Annulée';

    /**
     * @var int
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("userId")
     */
    private $id;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Nom")
     */
    private $familyName;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Prénom")
     */
    private $givenName;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Genre")
     */
    private $gender;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Email")
     */
    private $email;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Téléphone")
     */
    private $telephone;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Date de naissance")
     */
    private $birthDate;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Date d'inscription")
     */
    private $registrationDate;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Date de dernière activité")
     */
    private $lastActivityDate;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Accord pour newsletter")
     */
    private $newsletterSubscription = self::FALSE;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Date de validité d'annonce")
     */
    private $maxValidityAnnonceDate;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Commune de résidence")
     */
    private $addressLocality;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Utilisateur solidaire")
     */
    private $solidaryUser = self::FALSE;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Communauté 1")
     */
    private $community1;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Communauté 2")
     */
    private $community2;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Communauté 3")
     */
    private $community3;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Annonce 1 - Commune d'origine")
     */
    private $carpool1OriginLocality;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Annonce 2 - Commune d'origine")
     */
    private $carpool2OriginLocality;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Annonce 3 - Commune d'origine")
     */
    private $carpool3OriginLocality;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Annonce 1 - Commune de destination")
     */
    private $carpool1DestinationLocality;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Annonce 2 - Commune de destination")
     */
    private $carpool2DestinationLocality;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Annonce 3 - Commune de destination")
     */
    private $carpool3DestinationLocality;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Annonce 1 - Fréquence")
     */
    private $carpool1Frequency;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Annonce 2 - Fréquence")
     */
    private $carpool2Frequency;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Annonce 3 - Fréquence")
     */
    private $carpool3Frequency;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Rôle - ROLE_SUPER_ADMIN")
     */
    private $roleSuperAdmin = self::FALSE;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Rôle - ROLE_ADMIN")
     */
    private $roleAdmin = self::FALSE;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Rôle - ROLE_USER_REGISTERED_FULL")
     */
    private $roleUserRegisteredFull = self::FALSE;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Rôle - ROLE_USER_REGISTERED_MINIMAL")
     */
    private $roleUserRegisteredMinimal = self::FALSE;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Rôle - ROLE_USER")
     */
    private $roleUser = self::FALSE;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Rôle - ROLE_MASS_MATCH")
     */
    private $roleMassMatch = self::FALSE;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Rôle - ROLE_COMMUNITY_MANAGER")
     */
    private $roleCommunityManager = self::FALSE;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Rôle - ROLE_COMMUNITY_MANAGER_PUBLIC")
     */
    private $roleCommunityManagerPublic = self::FALSE;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Rôle - ROLE_COMMUNITY_MANAGER_PRIVATE")
     */
    private $roleCommunityManagerPrivate = self::FALSE;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Rôle - ROLE_SOLIDARY_OPERATOR")
     */
    private $roleSolidaryOperator = self::FALSE;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Rôle - ROLE_SOLIDARY_VOLUNTEER")
     */
    private $roleSolidaryVolunteer = self::FALSE;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Rôle - ROLE_SOLIDARY_BENEFICIARY")
     */
    private $roleSolidaryBeneficiary = self::FALSE;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Rôle - ROLE_COMMUNICATION_MANAGER")
     */
    private $roleCommunicationManager = self::FALSE;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Rôle - ROLE_SOLIDARY_VOLUNTEER_CANDIDATE")
     */
    private $roleSolidaryVolunteerCandidate = self::FALSE;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Rôle - ROLE_SOLIDARY_BENEFICIARY_CANDIDATE")
     */
    private $roleSolidaryBeneficiaryCandidate = self::FALSE;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Rôle - ROLE_INTEROPERABILITY")
     */
    private $roleInteroperability = self::FALSE;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Rôle - ROLE_SOLIDARY_ADMIN")
     */
    private $roleSolidaryAdmin = self::FALSE;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Rôle - ROLE_TERRITORY_CONSULTANT")
     */
    private $roleTerritoryConsultant = self::FALSE;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Statut identité")
     */
    private $identityStatus;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Utilisation de Rezo Pouce")
     */
    private $rezoPouceUse;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of familyName.
     */
    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }

    /**
     * Set the value of familyName.
     */
    public function setFamilyName(?string $familyName): self
    {
        $this->familyName = $familyName;

        return $this;
    }

    /**
     * Get the value of givenName.
     */
    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    /**
     * Set the value of givenName.
     */
    public function setGivenName(?string $givenName): self
    {
        $this->givenName = $givenName;

        return $this;
    }

    /**
     * Get the value of gender.
     */
    public function getGender(): ?string
    {
        return $this->gender;
    }

    /**
     * Set the value of gender.
     */
    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get the value of email.
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Set the value of email.
     */
    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of telephone.
     */
    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    /**
     * Set the value of telephone.
     */
    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * Get the value of birthDate.
     */
    public function getBirthDate(): ?string
    {
        return $this->birthDate;
    }

    /**
     * Set the value of birthDate.
     */
    public function setBirthDate(?\DateTime $birthDate): self
    {
        $this->birthDate = $this->dateToString($birthDate);

        return $this;
    }

    /**
     * Get the value of registrationDate.
     */
    public function getRegistrationDate(): ?string
    {
        return $this->registrationDate;
    }

    /**
     * Set the value of registrationDate.
     */
    public function setRegistrationDate(?\DateTime $registrationDate): self
    {
        $this->registrationDate = $this->dateToString($registrationDate);

        return $this;
    }

    /**
     * Get the value of lastActivityDate.
     */
    public function getLastActivityDate(): ?string
    {
        return $this->lastActivityDate;
    }

    /**
     * Set the value of lastActivityDate.
     */
    public function setLastActivityDate(?\DateTime $lastActivityDate): self
    {
        $this->lastActivityDate = $this->dateToString($lastActivityDate);

        return $this;
    }

    /**
     * Get the value of newsletterSubscription.
     */
    public function hasNewsletterSubscription(): string
    {
        return $this->newsletterSubscription;
    }

    /**
     * Set the value of newsletterSubscription.
     */
    public function setNewsletterSubscription(?bool $newsletterSubscription): self
    {
        $this->newsletterSubscription = $newsletterSubscription ? self::TRUE : self::FALSE;

        return $this;
    }

    /**
     * Get the value of maxValidityAnnonceDate.
     */
    public function getMaxValidityAnnonceDate(): ?string
    {
        return $this->maxValidityAnnonceDate;
    }

    /**
     * Set the value of maxValidityAnnonceDate.
     */
    public function setMaxValidityAnnonceDate(?\DateTime $maxValidityAnnonceDate): self
    {
        $this->maxValidityAnnonceDate = $this->dateToString($maxValidityAnnonceDate);

        return $this;
    }

    /**
     * Get the value of addressLocality.
     */
    public function getAddressLocality(): ?string
    {
        return $this->addressLocality;
    }

    /**
     * Set the value of addressLocality.
     */
    public function setAddressLocality(?string $addressLocality): self
    {
        $this->addressLocality = $addressLocality;

        return $this;
    }

    /**
     * Get the value of solidaryUser.
     */
    public function isSolidaryUser(): ?string
    {
        return $this->solidaryUser;
    }

    /**
     * Set the value of solidaryUser.
     */
    public function setSolidaryUser(?string $solidaryUser): self
    {
        $this->solidaryUser = $solidaryUser;

        return $this;
    }

    /**
     * Get the value of community1.
     */
    public function getCommunity1(): ?string
    {
        return $this->community1;
    }

    /**
     * Set the value of community1.
     */
    public function setCommunity1(?string $community1): self
    {
        $this->community1 = $community1;

        return $this;
    }

    /**
     * Get the value of community2.
     */
    public function getCommunity2(): ?string
    {
        return $this->community2;
    }

    /**
     * Set the value of community2.
     */
    public function setCommunity2(?string $community2): self
    {
        $this->community2 = $community2;

        return $this;
    }

    /**
     * Get the value of community3.
     */
    public function getCommunity3(): ?string
    {
        return $this->community3;
    }

    /**
     * Set the value of community3.
     */
    public function setCommunity3(?string $community3): self
    {
        $this->community3 = $community3;

        return $this;
    }

    /**
     * Get the value of carpool1OriginLocality.
     */
    public function getCarpool1OriginLocality(): ?string
    {
        return $this->carpool1OriginLocality;
    }

    /**
     * Set the value of carpool1OriginLocality.
     */
    public function setCarpool1OriginLocality(?string $carpool1OriginLocality): self
    {
        $this->carpool1OriginLocality = $carpool1OriginLocality;

        return $this;
    }

    /**
     * Get the value of carpool2OriginLocality.
     */
    public function getCarpool2OriginLocality(): ?string
    {
        return $this->carpool2OriginLocality;
    }

    /**
     * Set the value of carpool2OriginLocality.
     */
    public function setCarpool2OriginLocality(?string $carpool2OriginLocality): self
    {
        $this->carpool2OriginLocality = $carpool2OriginLocality;

        return $this;
    }

    /**
     * Get the value of carpool3OriginLocality.
     */
    public function getCarpool3OriginLocality(): ?string
    {
        return $this->carpool3OriginLocality;
    }

    /**
     * Set the value of carpool3OriginLocality.
     */
    public function setCarpool3OriginLocality(?string $carpool3OriginLocality): self
    {
        $this->carpool3OriginLocality = $carpool3OriginLocality;

        return $this;
    }

    /**
     * Get the value of carpool1DestinationLocality.
     */
    public function getCarpool1DestinationLocality(): ?string
    {
        return $this->carpool1DestinationLocality;
    }

    /**
     * Set the value of carpool1DestinationLocality.
     */
    public function setCarpool1DestinationLocality(?string $carpool1DestinationLocality): self
    {
        $this->carpool1DestinationLocality = $carpool1DestinationLocality;

        return $this;
    }

    /**
     * Get the value of carpool2DestinationLocality.
     */
    public function getCarpool2DestinationLocality(): ?string
    {
        return $this->carpool2DestinationLocality;
    }

    /**
     * Set the value of carpool2DestinationLocality.
     */
    public function setCarpool2DestinationLocality(?string $carpool2DestinationLocality): self
    {
        $this->carpool2DestinationLocality = $carpool2DestinationLocality;

        return $this;
    }

    /**
     * Get the value of carpool3DestinationLocality.
     */
    public function getCarpool3DestinationLocality(): ?string
    {
        return $this->carpool3DestinationLocality;
    }

    /**
     * Set the value of carpool3DestinationLocality.
     */
    public function setCarpool3DestinationLocality(?string $carpool3DestinationLocality): self
    {
        $this->carpool3DestinationLocality = $carpool3DestinationLocality;

        return $this;
    }

    /**
     * Get the value of carpool1Frequency.
     */
    public function getCarpool1Frequency(): ?string
    {
        return $this->carpool1Frequency;
    }

    /**
     * Set the value of carpool1Frequency.
     */
    public function setCarpool1Frequency(?string $carpool1Frequency): self
    {
        $this->carpool1Frequency = $carpool1Frequency;

        return $this;
    }

    /**
     * Get the value of carpool2Frequency.
     */
    public function getCarpool2Frequency(): ?string
    {
        return $this->carpool2Frequency;
    }

    /**
     * Set the value of carpool2Frequency.
     */
    public function setCarpool2Frequency(?string $carpool2Frequency): self
    {
        $this->carpool2Frequency = $carpool2Frequency;

        return $this;
    }

    /**
     * Get the value of carpool3Frequency.
     */
    public function getCarpool3Frequency(): ?string
    {
        return $this->carpool3Frequency;
    }

    /**
     * Set the value of carpool3Frequency.
     */
    public function setCarpool3Frequency(?string $carpool3Frequency): self
    {
        $this->carpool3Frequency = $carpool3Frequency;

        return $this;
    }

    /**
     * Get the value of roleSuperAdmin.
     */
    public function getRoleSuperAdmin(): string
    {
        return $this->roleSuperAdmin;
    }

    /**
     * Set the value of roleSuperAdmin.
     */
    public function setRoleSuperAdmin(string $roleSuperAdmin): self
    {
        $this->roleSuperAdmin = $roleSuperAdmin;

        return $this;
    }

    /**
     * Get the value of roleAdmin.
     */
    public function getRoleAdmin(): string
    {
        return $this->roleAdmin;
    }

    /**
     * Set the value of roleAdmin.
     */
    public function setRoleAdmin(string $roleAdmin): self
    {
        $this->roleAdmin = $roleAdmin;

        return $this;
    }

    /**
     * Get the value of roleUserRegisteredFull.
     */
    public function getRoleUserRegisteredFull(): string
    {
        return $this->roleUserRegisteredFull;
    }

    /**
     * Set the value of roleUserRegisteredFull.
     */
    public function setRoleUserRegisteredFull(string $roleUserRegisteredFull): self
    {
        $this->roleUserRegisteredFull = $roleUserRegisteredFull;

        return $this;
    }

    /**
     * Get the value of roleUserRegisteredMinimal.
     */
    public function getRoleUserRegisteredMinimal(): string
    {
        return $this->roleUserRegisteredMinimal;
    }

    /**
     * Set the value of roleUserRegisteredMinimal.
     */
    public function setRoleUserRegisteredMinimal(string $roleUserRegisteredMinimal): self
    {
        $this->roleUserRegisteredMinimal = $roleUserRegisteredMinimal;

        return $this;
    }

    /**
     * Get the value of roleUser.
     */
    public function getRoleUser(): string
    {
        return $this->roleUser;
    }

    /**
     * Set the value of roleUser.
     */
    public function setRoleUser(string $roleUser): self
    {
        $this->roleUser = $roleUser;

        return $this;
    }

    /**
     * Get the value of roleMassMatch.
     */
    public function getRoleMassMatch(): string
    {
        return $this->roleMassMatch;
    }

    /**
     * Set the value of roleMassMatch.
     */
    public function setRoleMassMatch(string $roleMassMatch): self
    {
        $this->roleMassMatch = $roleMassMatch;

        return $this;
    }

    /**
     * Get the value of roleCommunityManager.
     */
    public function getRoleCommunityManager(): string
    {
        return $this->roleCommunityManager;
    }

    /**
     * Set the value of roleCommunityManager.
     */
    public function setRoleCommunityManager(string $roleCommunityManager): self
    {
        $this->roleCommunityManager = $roleCommunityManager;

        return $this;
    }

    /**
     * Get the value of roleCommunityManagerPublic.
     */
    public function getRoleCommunityManagerPublic(): string
    {
        return $this->roleCommunityManagerPublic;
    }

    /**
     * Set the value of roleCommunityManagerPublic.
     */
    public function setRoleCommunityManagerPublic(string $roleCommunityManagerPublic): self
    {
        $this->roleCommunityManagerPublic = $roleCommunityManagerPublic;

        return $this;
    }

    /**
     * Get the value of roleCommunityManagerPrivate.
     */
    public function getRoleCommunityManagerPrivate(): string
    {
        return $this->roleCommunityManagerPrivate;
    }

    /**
     * Set the value of roleCommunityManagerPrivate.
     */
    public function setRoleCommunityManagerPrivate(string $roleCommunityManagerPrivate): self
    {
        $this->roleCommunityManagerPrivate = $roleCommunityManagerPrivate;

        return $this;
    }

    /**
     * Get the value of roleSolidaryOperator.
     */
    public function getRoleSolidaryOperator(): string
    {
        return $this->roleSolidaryOperator;
    }

    /**
     * Set the value of roleSolidaryOperator.
     */
    public function setRoleSolidaryOperator(string $roleSolidaryOperator): self
    {
        $this->roleSolidaryOperator = $roleSolidaryOperator;

        return $this;
    }

    /**
     * Get the value of roleSolidaryVolunteer.
     */
    public function getRoleSolidaryVolunteer(): string
    {
        return $this->roleSolidaryVolunteer;
    }

    /**
     * Set the value of roleSolidaryVolunteer.
     */
    public function setRoleSolidaryVolunteer(string $roleSolidaryVolunteer): self
    {
        $this->roleSolidaryVolunteer = $roleSolidaryVolunteer;

        return $this;
    }

    /**
     * Get the value of roleSolidaryBeneficiary.
     */
    public function getRoleSolidaryBeneficiary(): string
    {
        return $this->roleSolidaryBeneficiary;
    }

    /**
     * Set the value of roleSolidaryBeneficiary.
     */
    public function setRoleSolidaryBeneficiary(string $roleSolidaryBeneficiary): self
    {
        $this->roleSolidaryBeneficiary = $roleSolidaryBeneficiary;

        return $this;
    }

    /**
     * Get the value of roleCommunicationManager.
     */
    public function getRoleCommunicationManager(): string
    {
        return $this->roleCommunicationManager;
    }

    /**
     * Set the value of roleCommunicationManager.
     */
    public function setRoleCommunicationManager(string $roleCommunicationManager): self
    {
        $this->roleCommunicationManager = $roleCommunicationManager;

        return $this;
    }

    /**
     * Get the value of roleSolidaryVolunteerCandidate.
     */
    public function getRoleSolidaryVolunteerCandidate(): string
    {
        return $this->roleSolidaryVolunteerCandidate;
    }

    /**
     * Set the value of roleSolidaryVolunteerCandidate.
     */
    public function setRoleSolidaryVolunteerCandidate(string $roleSolidaryVolunteerCandidate): self
    {
        $this->roleSolidaryVolunteerCandidate = $roleSolidaryVolunteerCandidate;

        return $this;
    }

    /**
     * Get the value of roleSolidaryBeneficiaryCandidate.
     */
    public function getRoleSolidaryBeneficiaryCandidate(): string
    {
        return $this->roleSolidaryBeneficiaryCandidate;
    }

    /**
     * Set the value of roleSolidaryBeneficiaryCandidate.
     */
    public function setRoleSolidaryBeneficiaryCandidate(string $roleSolidaryBeneficiaryCandidate): self
    {
        $this->roleSolidaryBeneficiaryCandidate = $roleSolidaryBeneficiaryCandidate;

        return $this;
    }

    /**
     * Get the value of roleInteroperability.
     */
    public function getRoleInteroperability(): string
    {
        return $this->roleInteroperability;
    }

    /**
     * Set the value of roleInteroperability.
     */
    public function setRoleInteroperability(string $roleInteroperability): self
    {
        $this->roleInteroperability = $roleInteroperability;

        return $this;
    }

    /**
     * Get the value of roleSolidaryAdmin.
     */
    public function getRoleSolidaryAdmin(): string
    {
        return $this->roleSolidaryAdmin;
    }

    /**
     * Set the value of roleSolidaryAdmin.
     */
    public function setRoleSolidaryAdmin(string $roleSolidaryAdmin): self
    {
        $this->roleSolidaryAdmin = $roleSolidaryAdmin;

        return $this;
    }

    /**
     * Get the value of roleTerritoryConsultant.
     */
    public function getRoleTerritoryConsultant(): string
    {
        return $this->roleTerritoryConsultant;
    }

    /**
     * Set the value of roleTerritoryConsultant.
     */
    public function setRoleTerritoryConsultant(string $roleTerritoryConsultant): self
    {
        $this->roleTerritoryConsultant = $roleTerritoryConsultant;

        return $this;
    }

    public function getIdentityStatus(): ?string
    {
        return $this->identityStatus;
    }

    public function setIdentityStatus(?string $identityStatus): self
    {
        $this->identityStatus = $identityStatus;

        return $this;
    }

    public function getRezoPouceUse(): ?string
    {
        return $this->rezoPouceUse;
    }

    public function setRezoPouceUse(?string $rezoPouceUse): self
    {
        $this->rezoPouceUse = $rezoPouceUse;

        return $this;
    }

    private function dateToString(?\DateTime $date): ?string
    {
        return !is_null($date) ? $date->format('d-m-Y') : null;
    }
}
