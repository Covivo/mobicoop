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

namespace App\Utility\Service;

use Knp\Snappy\Pdf;
use Twig\Environment;

/**
 * Version manager service.
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class PdfManager
{
    private $pdf;
    private $twig;
    
    public function __construct(Pdf $pdf, Environment $twig)
    {
        $this->pdf = $pdf;
        $this->twig = $twig;
    }
   
    /**
     * Create an PDF export of an array.
     *
     * @param array $dataToPdf
     * @return String link to the pdf file.
     */
    public function generatePDF(array $dataToPdf)
    {
        $this->pdf->generateFromHtml(
            $this->twig->render(
                $dataToPdf['twigPath'],
                [
                        'array' => $dataToPdf
                    ]
            ),
            $dataToPdf['filePath'].$dataToPdf['fileName'],
            [],
            true
        );
        return $dataToPdf['returnUrl'];
    }
}
