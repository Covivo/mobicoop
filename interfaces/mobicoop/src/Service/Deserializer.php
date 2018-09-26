<?php

namespace App\Service;

use App\Entity\Address;
use App\Entity\User;
use App\Entity\UserAddress;

use TypeError;

use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;

/**
 * Custom deserializer service.
 * Used because deserialization of nested array of objects doesn't work yet...
 * Should be dumped when deserialization will work !
 * 
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 *
 */
class Deserializer
{
    const DATETIME_FORMAT = \DateTime::ISO8601;
    const SETTER_PREFIX = "set";

    /**
     * Deserialize an object.
     * 
     * @param string $class The expected class of the object
     * @param array $data   The array to deserialize
     * @return User|UserAddress|Address|null
     */
    public function deserialize(string $class, array $data)
    {
        switch ($class) {
            case User::class :  
                return self::deserializeUser($data);
                break;
            case UserAddress::class :  
                return self::deserializeUserAddress($data);
                break;
            case Address::class :  
                return self::deserializeAddress($data);
                break;
            default:
                break;
        }
        return null;
    }
    
    private function deserializeUser(array $data): ?User
    {
        $user = new User();
        $user = self::autoSet($user, $data);
        if (isset($data["@id"])) $user->setIri($data["@id"]);
        if (isset($data["userAddresses"])) {
            $userAddresses = [];
            foreach ($data["userAddresses"] as $userAddress) {
                $userAddresses[] = self::deserializeUserAddress($userAddress);
            }
            $user->setUserAddresses($userAddresses);
        }
        return $user;
    }
    
    private function deserializeUserAddress(array $data): ?UserAddress
    {
        $userAddress = new UserAddress();
        $userAddress = self::autoSet($userAddress, $data);
        if (isset($data["@id"])) $userAddress->setIri($data["@id"]);
        if (isset($data["address"])) {
            $userAddress->setAddress(self::deserializeAddress($data["address"]));
        }
        return $userAddress;
    }
    
    private function deserializeAddress(array $data): ?Address
    {
        $address = new Address();
        $address = self::autoSet($address, $data);
        if (isset($data["@id"]))$address->setIri($data["@id"]);
        return $address;
    }
    
    private function autoSet($object,$data) 
    {
        $phpDocExtractor = new PhpDocExtractor();
        $reflectionExtractor = new ReflectionExtractor();
        $listExtractors = array($reflectionExtractor);
        $typeExtractors = array($phpDocExtractor);
        $descriptionExtractors = array($phpDocExtractor);
        $accessExtractors = array($reflectionExtractor);
        
        $propertyInfo = new PropertyInfoExtractor(
                $listExtractors,
                $typeExtractors,
                $descriptionExtractors,
                $accessExtractors
                );
        
        $properties = $propertyInfo->getProperties(get_class($object));
        foreach ($properties as $property) {
            if (isset($data[$property])) {
                $setter = self::SETTER_PREFIX.ucwords($property);
                if (method_exists($object, $setter)) {
                    // we try to set the property
                    try {
                        // it works !!!
                        $object->$setter($data[$property]);
                    } catch (TypeError $error) {
                        // fail... it must be an object or array property, we will treat it manually
                        $type = $propertyInfo->getTypes(get_class($object), $property)[0]->getClassName();
                        switch ($type) {
                            case "DateTimeInterface":
                                try {
                                    $catchedValue = \DateTime::createFromFormat(self::DATETIME_FORMAT,$data[$property]);
                                    $object->$setter($catchedValue);
                                } catch (\Error $e) {
                                    
                                }
                                break;
                            default: break;
                        }
                    }
                }
            }
        }
        return $object;
    }
     
}