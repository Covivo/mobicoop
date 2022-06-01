<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

namespace App\Service;

/**
 * File manager.
 *
 * This service contains method related to file management (sanitize, move, delete...).
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class FileManager
{
    /**
     * Sanitizes a name (remove special chars, replace spaces...).
     * @param string $string
     * @param boolean $force_lowercase
     * @param boolean $anal
     * @param string $replace The replacement char for forbidden chars
     * @return string
     */
    public function sanitize(string $string, bool $force_lowercase = true, bool $anal = false, string $replace = ""): string
    {
        $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
            "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
            "â€”", "â€“", ",", "<", ".", ">", "/", "?");
        $clean = trim(str_replace($strip, $replace, strip_tags($string)));
        $clean = preg_replace('/\s+/', "-", $clean);
        $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", $replace, $clean) : $clean ;
        if ($force_lowercase) {
            if (function_exists('mb_strtolower')) {
                $clean = mb_strtolower($clean, 'UTF-8');
            } else {
                $clean = strtolower($clean);
            }
        }

        $clean = strtr($clean, [
            "à" => "a",
            "â" => "a",
            "ä" => "a",
            "é" => "e",
            "è" => "e",
            "ê" => "e",
            "ë" => "e",
            "ï" => "i",
            "î" => "i",
            "ô" => "o",
            "ö" => "o",
            "ù" => "u",
            "û" => "u",
            "ü" => "u",
            "ç" => "c"
        ]);
        return $clean;
    }

    /**
     * Returns the extension of a file.
     * @param mixed $file
     * @return string
     */
    public function getExtension($file): string
    {
        if (is_string($file)) {
            return substr(strrchr($file, '.'), 1);
        } elseif (is_object($file)) {
            $fileParts = pathinfo($file);
            return $fileParts['extension'];
        }
    }
}
