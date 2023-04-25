<?php

namespace App\User\Service;

use App\User\Entity\User;

class PseudonymizationManager
{
    private const DELETE_AVAILABLE_USER_FIELDS = ['birthDate', 'chat', 'chatFavorites', 'drivingLicenceNumber', 'emailToken', 'familyName', 'gamification', 'givenName', 'mobile', 'music', 'musicFavorites', 'newsSubscription', 'oldEmail', 'oldTelephone', 'postalAddress', 'proEmail', 'proName', 'smoke', 'ssoId', 'ssoProvider', 'telephone', 'unsubscribeToken'];
    private const DELETE_AVAILABLE_MASS_PERSON_FIELDS = ['familyName', 'givenName'];
    private const PSEUDONYMISED_EMAIL_SUFFIX = '@mobicoop-anonymized.io';

    /**
     * @var User
     */
    protected $_user;

    public function __construct()
    {
    }

    public static function isUserPseudonymized(User $user): bool
    {
        return User::STATUS_PSEUDONYMIZED === $user->getStatus();
    }

    public function pseudonymizedUser(User $user): User
    {
        $this->_user = $user;

        $this->_pseudonymizedBasics();
        $this->_pseudonymizedHomeAddress();
        $this->_pseudonomyzedMassPerson();

        $this->_removeFromCommunities();

        return $this->_user;
    }

    private function _getSetter(string $field): string
    {
        return 'set'.ucfirst($field);
    }

    private function _pseudonymizedBasics()
    {
        $today = new \DateTime('now');

        foreach (self::DELETE_AVAILABLE_USER_FIELDS as $key => $field) {
            $setter = $this->_getSetter($field);

            $this->_user->{$setter}(null);
        }

        $this->_user->setEmail($this->_user->getId().self::PSEUDONYMISED_EMAIL_SUFFIX);
        $this->_user->setPassword(password_hash($today->format('Y-m-d H:m:s'), PASSWORD_DEFAULT));
        $this->_user->setGender(User::GENDER_OTHER);
        $this->_user->setStatus(User::STATUS_PSEUDONYMIZED);
    }

    private function _pseudonymizedHomeAddress()
    {
        if (!is_null($this->_user->getHomeAddress())) {
            $this->_user->getHomeAddress()->setUser(null);
        }
    }

    private function _pseudonomyzedMassPerson()
    {
        if (!is_null($this->_user->getMassPerson())) {
            foreach (self::DELETE_AVAILABLE_MASS_PERSON_FIELDS as $key => $field) {
                $setter = $this->_getSetter($field);

                $this->_user->getMassPerson()->{$setter}(null);
            }

            $this->_user->getMassPerson()->setEmail($this->_user->getEmail());
        }
    }

    private function _removeFromCommunities()
    {
        foreach ($this->_user->getCommunities() as $community) {
            $this->_user->removeCommunity($community);
        }
    }
}
