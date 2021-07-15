<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\I18n\Service;

use App\App\Entity\App;
use App\Article\Entity\Article;
use App\I18n\Entity\Language;
use App\I18n\Repository\LanguageRepository;
use App\I18n\Repository\TranslateRepository;
use ReflectionClass;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Language manager service.
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class LanguageManager
{
    private $languageRepository;
    private $translateRepository;
    private $security;
    private $defaultLanguage;
    private $request;

    const SETTER_PREFIX = "set";

    /**
        * Constructor.
        *
        * @param EntityManagerInterface $entityManager
        */
    public function __construct(LanguageRepository $languageRepository, TranslateRepository $translateRepository, Security $security, int $defaultLanguage, RequestStack $requestStack)
    {
        $this->languageRepository = $languageRepository;
        $this->translateRepository = $translateRepository;
        $this->security = $security;
        $this->defaultLanguage = $defaultLanguage;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * Get a language by its id.
     *
     * @param integer $id
     * @return Language|null
     */
    public function getLanguage(int $id): ?Language
    {
        return $this->languageRepository->find($id);
    }

    /**
     * Get the translated object if there is any
     *
     * @param object $object        The object to translate
     * @return object   The translated object (or the original if there is no translation)
     */
    public function getTranslation(object $object): object
    {
        // Set the id to the default language
        $idLanguage = $this->defaultLanguage;
        
        if ($this->security->getUser() instanceof App) {
            // If the user is an App, we check if there is a locale given
            if ($this->request->headers->has('X-LOCALE')) {
                // We try to locate the language id using x-locale value
                foreach (Language::LANGUAGES as $key => $currentLanguage) {
                    if ($currentLanguage['code']==$this->request->headers->get('X-LOCALE')) {
                        $idLanguage = $currentLanguage['id'];
                        break;
                    }
                }

                if ($idLanguage == $this->defaultLanguage) {
                    // The app and the platform use the same language. We do nothing.
                    return $object;
                }
            } else {
                // No local, we do nothing
                return $object;
            }
        } else {
            // Not an App
            // We check if the user has a language and if it's not de default language of the platform
            // Otherwise, we return the original object and do nothing
            // Get the user language
            if ($this->security->getUser()) {
                $userLanguage = $this->security->getUser()->getLanguage();
                if (is_null($userLanguage)) {
                    // The user has no specific language. We do nothing.
                    return $object;
                } else {
                    if ($userLanguage->getId() == $this->defaultLanguage) {
                        // The user and the platform use the same language. We do nothing.
                        return $object;
                    } else {
                        // The user and the platform use different language. We set the User's language and try to find translation
                        $idLanguage = $userLanguage->getId();
                    }
                }
            }
        }


        if ($language = $this->getLanguage($idLanguage)) {
            // Check if the Object implements TRANSLATATBLE_ITEMS constant
            $class = get_class($object);
            $reflect = new ReflectionClass($class);
            if (array_key_exists("TRANSLATABLE_ITEMS", $reflect->getConstants())) {
                foreach ($object::TRANSLATABLE_ITEMS as $key => $item) {
                    if ($translate = $this->translateRepository->findOneBy([
                        "property"=>$item,
                        "language"=>$language,
                        "domain"=>$reflect->getShortName(),
                        "idEntity"=>$object->getId()
                    ])) {
                        $setter = self::SETTER_PREFIX.ucwords($item);
                        if (method_exists($object, $setter)) {
                            $object->$setter($translate->getText());
                        }
                    }
                }
            }
        }
        

        return $object;
    }
}
