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
     * @SerializedName("Role 1 - Nom")
     */
    private $role1Name;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Rôle 1 - Territoire")
     */
    private $role1Territory;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Role 2 - Nom")
     */
    private $role2Name;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Rôle 2 - Territoire")
     */
    private $role2Territory;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Role 3 - Nom")
     */
    private $role3Name;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Rôle 3 - Territoire")
     */
    private $role3Territory;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Role 4 - Nom")
     */
    private $role4Name;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Rôle 4 - Territoire")
     */
    private $role4Territory;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Role 5 - Nom")
     */
    private $role5Name;

    /**
     * @var string
     *
     * @Groups({"user-export"})
     *
     * @SerializedName("Rôle 5 - Territoire")
     */
    private $role5Territory;

    /**
     * Get the value of familyName.
     *
     * @return string
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
     *
     * @return string
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
     *
     * @return string
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
        if (!in_array($gender, User::GENDERS)) {
            throw new \LogicException('Gender is not defined');
        }

        switch ($gender) {
            case User::GENDER_FEMALE:
                $gender = 'Femme';

                break;

            case User::GENDER_MALE:
                $gender = 'Homme';

                break;

            case User::GENDER_OTHER:
                $gender = 'Autre';

                break;
        }

        $this->gender = $gender;

        return $this;
    }

    /**
     * Get the value of email.
     *
     * @return string
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
     *
     * @return string
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
     *
     * @return string
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
     *
     * @return string
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
     *
     * @return string
     */
    public function getCommunity1(): ?string
    {
        return $this->community1;
    }

    /**
     * Set the value of community1.
     *
     * @param string $community1
     */
    public function setCommunity1(?string $community1): self
    {
        $this->community1 = $community1;

        return $this;
    }

    /**
     * Get the value of community2.
     *
     * @return string
     */
    public function getCommunity2(): ?string
    {
        return $this->community2;
    }

    /**
     * Set the value of community2.
     *
     * @param string $community2
     */
    public function setCommunity2(?string $community2): self
    {
        $this->community2 = $community2;

        return $this;
    }

    /**
     * Get the value of community3.
     *
     * @return string
     */
    public function getCommunity3(): ?string
    {
        return $this->community3;
    }

    /**
     * Set the value of community3.
     *
     * @param string $community3
     */
    public function setCommunity3(?string $community3): self
    {
        $this->community3 = $community3;

        return $this;
    }

    /**
     * Get the value of carpool1OriginLocality.
     *
     * @return string
     */
    public function getCarpool1OriginLocality(): ?string
    {
        return $this->carpool1OriginLocality;
    }

    /**
     * Set the value of carpool1OriginLocality.
     *
     * @param string $carpool1OriginLocality
     */
    public function setCarpool1OriginLocality(?string $carpool1OriginLocality): self
    {
        $this->carpool1OriginLocality = $carpool1OriginLocality;

        return $this;
    }

    /**
     * Get the value of carpool2OriginLocality.
     *
     * @return string
     */
    public function getCarpool2OriginLocality(): ?string
    {
        return $this->carpool2OriginLocality;
    }

    /**
     * Set the value of carpool2OriginLocality.
     *
     * @param string $carpool2OriginLocality
     */
    public function setCarpool2OriginLocality(?string $carpool2OriginLocality): self
    {
        $this->carpool2OriginLocality = $carpool2OriginLocality;

        return $this;
    }

    /**
     * Get the value of carpool3OriginLocality.
     *
     * @return string
     */
    public function getCarpool3OriginLocality(): ?string
    {
        return $this->carpool3OriginLocality;
    }

    /**
     * Set the value of carpool3OriginLocality.
     *
     * @param string $carpool3OriginLocality
     */
    public function setCarpool3OriginLocality(?string $carpool3OriginLocality): self
    {
        $this->carpool3OriginLocality = $carpool3OriginLocality;

        return $this;
    }

    /**
     * Get the value of carpool1DestinationLocality.
     *
     * @return string
     */
    public function getCarpool1DestinationLocality(): ?string
    {
        return $this->carpool1DestinationLocality;
    }

    /**
     * Set the value of carpool1DestinationLocality.
     *
     * @param string $carpool1DestinationLocality
     */
    public function setCarpool1DestinationLocality(?string $carpool1DestinationLocality): self
    {
        $this->carpool1DestinationLocality = $carpool1DestinationLocality;

        return $this;
    }

    /**
     * Get the value of carpool2DestinationLocality.
     *
     * @return string
     */
    public function getCarpool2DestinationLocality(): ?string
    {
        return $this->carpool2DestinationLocality;
    }

    /**
     * Set the value of carpool2DestinationLocality.
     *
     * @param string $carpool2DestinationLocality
     */
    public function setCarpool2DestinationLocality(?string $carpool2DestinationLocality): self
    {
        $this->carpool2DestinationLocality = $carpool2DestinationLocality;

        return $this;
    }

    /**
     * Get the value of carpool3DestinationLocality.
     *
     * @return string
     */
    public function getCarpool3DestinationLocality(): ?string
    {
        return $this->carpool3DestinationLocality;
    }

    /**
     * Set the value of carpool3DestinationLocality.
     *
     * @param string $carpool3DestinationLocality
     */
    public function setCarpool3DestinationLocality(?string $carpool3DestinationLocality): self
    {
        $this->carpool3DestinationLocality = $carpool3DestinationLocality;

        return $this;
    }

    /**
     * Get the value of carpool1Frequency.
     *
     * @return string
     */
    public function getCarpool1Frequency(): ?string
    {
        return $this->carpool1Frequency;
    }

    /**
     * Set the value of carpool1Frequency.
     *
     * @param string $carpool1Frequency
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
     *
     * @param string $carpool2Frequency
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
     *
     * @param string $carpool3Frequency
     */
    public function setCarpool3Frequency(?string $carpool3Frequency): self
    {
        $this->carpool3Frequency = $carpool3Frequency;

        return $this;
    }

    /**
     * Get the value of role1Name.
     *
     * @return string
     */
    public function getRole1Name(): ?string
    {
        return $this->role1Name;
    }

    /**
     * Set the value of role1Name.
     *
     * @param string $role1Name
     */
    public function setRole1Name(?string $role1Name): self
    {
        $this->role1Name = $role1Name;

        return $this;
    }

    /**
     * Get the value of role1Territory.
     *
     * @return string
     */
    public function getRole1Territory(): ?string
    {
        return $this->role1Territory;
    }

    /**
     * Set the value of role1Territory.
     *
     * @param string $role1Territory
     */
    public function setRole1Territory(?string $role1Territory): self
    {
        $this->role1Territory = $role1Territory;

        return $this;
    }

    /**
     * Get the value of role2Name.
     *
     * @return string
     */
    public function getRole2Name(): ?string
    {
        return $this->role2Name;
    }

    /**
     * Set the value of role2Name.
     *
     * @param string $role2Name
     */
    public function setRole2Name(?string $role2Name): self
    {
        $this->role2Name = $role2Name;

        return $this;
    }

    /**
     * Get the value of role2Territory.
     *
     * @return string
     */
    public function getRole2Territory(): ?string
    {
        return $this->role2Territory;
    }

    /**
     * Set the value of role2Territory.
     *
     * @param string $role2Territory
     */
    public function setRole2Territory(?string $role2Territory): self
    {
        $this->role2Territory = $role2Territory;

        return $this;
    }

    /**
     * Get the value of role3Name.
     *
     * @return string
     */
    public function getRole3Name(): ?string
    {
        return $this->role3Name;
    }

    /**
     * Set the value of role3Name.
     *
     * @param string $role3Name
     */
    public function setRole3Name(?string $role3Name): self
    {
        $this->role3Name = $role3Name;

        return $this;
    }

    /**
     * Get the value of role3Territory.
     *
     * @return string
     */
    public function getRole3Territory(): ?string
    {
        return $this->role3Territory;
    }

    /**
     * Set the value of role3Territory.
     *
     * @param string $role3Territory
     */
    public function setRole3Territory(?string $role3Territory): self
    {
        $this->role3Territory = $role3Territory;

        return $this;
    }

    /**
     * Get the value of role4Name.
     *
     * @return string
     */
    public function getRole4Name(): ?string
    {
        return $this->role4Name;
    }

    /**
     * Set the value of role4Name.
     *
     * @param string $role4Name
     */
    public function setRole4Name(?string $role4Name): self
    {
        $this->role4Name = $role4Name;

        return $this;
    }

    /**
     * Get the value of role4Territory.
     *
     * @return string
     */
    public function getRole4Territory(): ?string
    {
        return $this->role4Territory;
    }

    /**
     * Set the value of role4Territory.
     *
     * @param string $role4Territory
     */
    public function setRole4Territory(?string $role4Territory): self
    {
        $this->role4Territory = $role4Territory;

        return $this;
    }

    /**
     * Get the value of role5Name.
     *
     * @return string
     */
    public function getRole5Name(): ?string
    {
        return $this->role5Name;
    }

    /**
     * Set the value of role5Name.
     *
     * @param string $role5Name
     */
    public function setRole5Name(?string $role5Name): self
    {
        $this->role5Name = $role5Name;

        return $this;
    }

    /**
     * Get the value of role5Territory.
     *
     * @return string
     */
    public function getRole5Territory(): ?string
    {
        return $this->role5Territory;
    }

    /**
     * Set the value of role5Territory.
     *
     * @param string $role5Territory
     */
    public function setRole5Territory(?string $role5Territory): self
    {
        $this->role5Territory = $role5Territory;

        return $this;
    }

    private function dateToString(?\DateTime $date): ?string
    {
        return !is_null($date) ? $date->format('d-m-Y') : null;
    }
}
