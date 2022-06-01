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
 */

namespace App\Payment\Controller;

use App\Payment\Ressource\ValidationDocument;
use App\Payment\Service\PaymentManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Security;
use Vich\UploaderBundle\Handler\UploadHandler;

/**
 * Validation document controller
 * Here we use a controller instead of a DataPersister as we need to handle multipart/form-data.
 */
final class UploadValidationDocumentAction
{
    private $security;
    private $uploadHandler;
    private $paymentManager;

    public function __construct(Security $security, UploadHandler $uploadHandler, PaymentManager $paymentManager)
    {
        $this->security = $security;
        $this->uploadHandler = $uploadHandler;
        $this->paymentManager = $paymentManager;
    }

    public function __invoke(Request $request): ?ValidationDocument
    {
        $uploadedFile = $request->files->get('file');
        if (!$uploadedFile) {
            throw new BadRequestHttpException('"file" is required');
        }
        $validationDocument = new ValidationDocument();
        $validationDocument->setFile($uploadedFile);
        $validationDocument->setUser($this->security->getUser());

        // We need to delete the extension added by vich uploader
        $validationDocument->setFileName(substr($validationDocument->getFile()->getClientOriginalName(), 0, strrpos($validationDocument->getFile()->getClientOriginalName(), '.')));
        $validationDocument->setExtension(
            substr($validationDocument->getFile()->getClientOriginalName(), strrpos($validationDocument->getFile()->getClientOriginalName(), '.') + 1, strlen($validationDocument->getFile()->getClientOriginalName()) - 1)
        );

        $this->uploadHandler->upload($validationDocument, 'file');

        return $this->paymentManager->uploadValidationDocument($validationDocument);
    }
}
