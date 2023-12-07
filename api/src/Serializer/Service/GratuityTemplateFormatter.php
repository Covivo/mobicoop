<?php
/**
 * Copyright (c) 2023, MOBICOOP. All rights reserved.
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
 */

namespace App\Serializer\Service;

use App\Gratuity\Entity\GratuityCampaign;
use App\Serializer\Service\Interfaces\GratuityTemplateRulesInterface;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class GratuityTemplateFormatter
{
    private const RULES_FOLDER = __DIR__.'/Rules';
    private const RULES_NAMESPACE = 'App\\Serializer\\Service\\Rules\\';
    private $_rules;
    private $_customReplacements = [];

    public function __construct(string $baseUri)
    {
        $this->_rules = [];
        $this->_customReplacements['baseUri'] = $baseUri;
        $this->_autoloadRules();
    }

    public function _checkRule(string $field): ?GratuityTemplateRulesInterface
    {
        foreach ($this->_rules as $rule) {
            if (in_array($field, $rule->getFields())) {
                return $rule;
            }
        }

        return null;
    }

    public function format(GratuityCampaign $gratuityCampaign): GratuityCampaign
    {
        $pattern = '/\{([^}]+)\}/';
        $template = $gratuityCampaign->getTemplate();
        preg_match_all($pattern, $template, $matches);
        foreach ($matches[1] as $matche) {
            if ($rule = $this->_checkRule($matche)) {
                $getter = 'get'.ucfirst($matche);
                $formattedField = $rule->format($gratuityCampaign->{$getter}());
                $template = str_replace('{'.$matche.'}', $formattedField, $template);
            } else {
                $template = $this->_customReplacement($matche, $template);
            }
        }

        return $gratuityCampaign->setTemplate($template);
    }

    private function _customReplacement(string $matche, string $template): string
    {
        if (isset($this->_customReplacements[$matche])) {
            return str_replace('{'.$matche.'}', $this->_customReplacements[$matche], $template);
        }

        return $template;
    }

    private function _autoloadRules()
    {
        $files = glob(self::RULES_FOLDER.'/*.php');
        foreach ($files as $file) {
            // Inclure le fichier de classe
            require_once $file;
            $className = pathinfo($file, PATHINFO_FILENAME);
            $completeClassName = self::RULES_NAMESPACE.$className;
            $this->_rules[] = new $completeClassName();
        }
    }
}
