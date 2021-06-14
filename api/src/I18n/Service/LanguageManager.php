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

use App\Article\Entity\Article;
use App\I18n\Entity\Language;
use App\I18n\Repository\LanguageRepository;
use App\I18n\Repository\TranslateRepository;
use ReflectionClass;

/**
 * Language manager service.
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class LanguageManager
{
    private $languageRepository;
    private $translateRepository;

    const SETTER_PREFIX = "set";

    /**
        * Constructor.
        *
        * @param EntityManagerInterface $entityManager
        */
    public function __construct(LanguageRepository $languageRepository, TranslateRepository $translateRepository)
    {
        $this->languageRepository = $languageRepository;
        $this->translateRepository = $translateRepository;
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
     * @param integer $idLanguage   Language for the translation
     * @param string $domain        Domaine of the translation
     * @param integer $idEntity     Id of the object we want the translation
     * @param object $object        The object to translate
     * @return object   The translated object (or the original if there is no translation)
     */
    public function getTranslation(int $idLanguage, string $domain, int $idEntity, object $object): object
    {
        if ($language = $this->getLanguage($idLanguage)) {

            // Check if the Object implements TRANSLATATBLE_ITEMS constant
            $class = get_class($object);
            $reflect = new ReflectionClass($class);
            if (array_key_exists("TRANSLATABLE_ITEMS", $reflect->getConstants())) {
                foreach ($object::TRANSLATABLE_ITEMS as $key => $item) {
                    if ($translate = $this->translateRepository->findOneBy([
                        "property"=>$item,
                        "language"=>$language,
                        "domain"=>$domain,
                        "idEntity"=>$idEntity
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
