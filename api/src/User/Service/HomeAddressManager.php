<?php
namespace App\User\Service;

use App\Geography\Entity\Address;
use App\User\Entity\User;

class HomeAddressManager {
    public function upsert(User $user): Address
    {
        // home address updated, we search the original home address
        $homeAddress = null;
        foreach ($user->getAddresses() as $address) {
            if ($address->isHome()) {
                $homeAddress = $address;

                break;
            }
        }

        $newHomeAddressData = $user->getHomeAddress();

        if (is_null($homeAddress)) {
            $homeAddress = new Address();
            $homeAddress->setHome(true);
            $homeAddress->setName(Address::HOME_ADDRESS);
            $homeAddress->setUser($user);
            $user->addAddress($homeAddress);
        }

        $homeAddress->setStreetAddress($newHomeAddressData->getStreetAddress());
        $homeAddress->setStreet($newHomeAddressData->getStreet());
        $homeAddress->setPostalCode($newHomeAddressData->getPostalCode());
        $homeAddress->setAddressLocality($newHomeAddressData->getAddressLocality());
        $homeAddress->setAddressCountry($newHomeAddressData->getAddressCountry());
        $homeAddress->setLatitude($newHomeAddressData->getLatitude());
        $homeAddress->setLongitude($newHomeAddressData->getLongitude());
        $homeAddress->setHouseNumber($newHomeAddressData->getHouseNumber());
        $homeAddress->setSubLocality($newHomeAddressData->getSubLocality());
        $homeAddress->setLocalAdmin($newHomeAddressData->getLocalAdmin());
        $homeAddress->setCounty($newHomeAddressData->getCounty());
        $homeAddress->setMacroCounty($newHomeAddressData->getMacroCounty());
        $homeAddress->setRegion($newHomeAddressData->getRegion());
        $homeAddress->setMacroRegion($newHomeAddressData->getMacroRegion());
        $homeAddress->setCountryCode($newHomeAddressData->getCountryCode());

        return $homeAddress;
    }
}
