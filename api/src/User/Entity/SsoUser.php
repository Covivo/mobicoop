<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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
 **************************/

namespace App\User\Entity;

/**
 * A SSO User
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SsoUser
{
    const GENDER_MALE = 'male';
    const GENDER_FEMALE = 'female';

    private $provider;
    private $sub;
    private $email;
    private $first_name;
    private $last_name;
    private $given_name;
    private $family_name;
    private $title;
    private $gender;
    private $birthdate;
    private $birthplace;
    private $birthcountry;
    private $birthplace_insee;
    private $birthcountry_insee;
    private $validated;
    private $validation_date;
    private $validation_context;
    private $preferred_username;
    private $preferred_givenname;
    private $address_number;
    private $address_street;
    private $address_complement;
    private $address_zipcode;
    private $address_city;
    private $address_country;
    private $home_mobile_phone;
    private $home_phone;
    private $professional_mobile_phone;
    private $professional_phone;
    private $birthdepartment;
    private $autoCreateAccount;

    public function getProvider()
    {
        return $this->provider;
    }

    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    public function getSub()
    {
        return $this->sub;
    }

    public function setSub($sub)
    {
        $this->sub = $sub;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getFirstname()
    {
        return $this->first_name;
    }

    public function setFirstname($first_name)
    {
        $this->first_name = $first_name;
    }

    public function getLastname()
    {
        return $this->last_name;
    }

    public function setLastname($last_name)
    {
        $this->last_name = $last_name;
    }

    public function getGivenname()
    {
        return $this->given_name;
    }

    public function setGivenname($given_name)
    {
        $this->given_name = $given_name;
    }

    public function getFamilyname()
    {
        return $this->family_name;
    }

    public function setFamilyname($family_name)
    {
        $this->family_name = $family_name;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getGender()
    {
        return $this->gender;
    }

    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    public function getBirthdate()
    {
        return $this->birthdate;
    }

    public function setBirthdate($birthdate)
    {
        $this->birthdate = $birthdate;
    }

    public function getBirthplace()
    {
        return $this->birthplace;
    }

    public function setBirthplace($birthplace)
    {
        $this->birthplace = $birthplace;
    }

    public function getBirthcountry()
    {
        return $this->birthcountry;
    }

    public function setBirthcountry($birthcountry)
    {
        $this->birthcountry = $birthcountry;
    }

    public function getBirthplaceinsee()
    {
        return $this->birthplace_insee;
    }

    public function setBirthplaceinsee($birthplace_insee)
    {
        $this->birthplace_insee = $birthplace_insee;
    }

    public function getBirthcountryinsee()
    {
        return $this->birthcountry_insee;
    }

    public function setBirthcountryinsee($birthcountry_insee)
    {
        $this->birthcountry_insee = $birthcountry_insee;
    }

    public function getValidated()
    {
        return $this->validated;
    }

    public function setValidated($validated)
    {
        $this->validated = $validated;
    }

    public function getValidationdate()
    {
        return $this->validation_date;
    }

    public function setValidationdate($validation_date)
    {
        $this->validation_date = $validation_date;
    }

    public function getValidationcontext()
    {
        return $this->validation_context;
    }

    public function setValidationcontext($validation_context)
    {
        $this->validation_context = $validation_context;
    }

    public function getPreferredusername()
    {
        return $this->preferred_username;
    }

    public function setPreferredusername($preferred_username)
    {
        $this->preferred_username = $preferred_username;
    }

    public function getPreferredgivenname()
    {
        return $this->preferred_givenname;
    }

    public function setPreferredgivenname($preferred_givenname)
    {
        $this->preferred_givenname = $preferred_givenname;
    }

    public function getAddressnumber()
    {
        return $this->address_number;
    }

    public function setAddressnumber($address_number)
    {
        $this->address_number = $address_number;
    }

    public function getAddressstreet()
    {
        return $this->address_street;
    }

    public function setAddressstreet($address_street)
    {
        $this->address_street = $address_street;
    }

    public function getAddresscomplement()
    {
        return $this->address_complement;
    }

    public function setAddresscomplement($address_complement)
    {
        $this->address_complement = $address_complement;
    }

    public function getAddresszipcode()
    {
        return $this->address_zipcode;
    }

    public function setAddresszipcode($address_zipcode)
    {
        $this->address_zipcode = $address_zipcode;
    }

    public function getAddresscity()
    {
        return $this->address_city;
    }

    public function setAddresscity($address_city)
    {
        $this->address_city = $address_city;
    }

    public function getAddresscountry()
    {
        return $this->address_country;
    }

    public function setAddresscountry($address_country)
    {
        $this->address_country = $address_country;
    }

    public function getHomemobilephone()
    {
        return $this->home_mobile_phone;
    }

    public function setHomemobilephone($home_mobile_phone)
    {
        $this->home_mobile_phone = $home_mobile_phone;
    }

    public function getHomephone()
    {
        return $this->home_phone;
    }

    public function setHomephone($home_phone)
    {
        $this->home_phone = $home_phone;
    }

    public function getProfessionalmobilephone()
    {
        return $this->professional_mobile_phone;
    }

    public function setProfessionalmobilephone($professional_mobile_phone)
    {
        $this->professional_mobile_phone = $professional_mobile_phone;
    }

    public function getProfessionalphone()
    {
        return $this->professional_phone;
    }

    public function setProfessionalphone($professional_phone)
    {
        $this->professional_phone = $professional_phone;
    }

    public function getBirthdepartment()
    {
        return $this->birthdepartment;
    }

    public function setBirthdepartment($birthdepartment)
    {
        $this->birthdepartment = $birthdepartment;
    }

    public function hasAutoCreateAccount(): ?bool
    {
        return (!is_null($this->autoCreateAccount)) ? $this->autoCreateAccount : true;
    }

    public function setAutoCreateAccount(?bool $autoCreateAccount)
    {
        $this->autoCreateAccount = $autoCreateAccount;
    }
}
