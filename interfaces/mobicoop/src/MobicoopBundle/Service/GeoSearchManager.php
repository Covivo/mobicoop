<?php
/**
 * GeoSearchManager.php
 * Class
 * @author Sofiane Belaribi <sofiane.belaribi@mobicoop.org>
 * Date: 29/11/2018
 * Time: 16:38
 *
 */

namespace Mobicoop\Bundle\MobicoopBundle\Service;

use App\Geography\Entity\GeoSearch;

class GeoSearchManager
{


    //@todo : FAIRE MANAGER
    //@todo : CONNECTER AU DESERIALIZER
    //@todo : TESTER SUR LE FRONT
    //@todo : FINIR AUTOCOMPLETE
    //@todo : FORMATER EXTERNAL JOURNEY
    //@todo : CERNER BORNES API SUR FRANCE/EUROPE


    private $dataProvider;
    private $deserializer;

    public function __construct(DataProvider $dataProvider, Deserializer $deserializer)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(GeoSearch::class);
    }

    /**
     * Get a user by its identifier
     *
     * @param String $id The user id
     *
     * @return User|null The user found or null if not found.
     */
    public function getUser($id)
    {
        $response = $this->dataProvider->getItem($id);
        if ($response->getCode() == 200) {
            return $response->getValue();
        }
        return null;
    }

    /**
     * Get all users
     *
     * @return array|null The users found or null if not found.
     */
    public function getUsers()
    {
        $response = $this->dataProvider->getCollection();
        if ($response->getCode() == 200) {
            return $response->getValue();
        }
        return null;
    }
}
