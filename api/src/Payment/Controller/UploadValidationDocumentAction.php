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
use Symfony\Component\HttpFoundation\File\File;
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
        $validationDocument = $this->_treatMandatoryFile($request);
        $validationDocument = $this->_treatOptionalFile($request, $validationDocument, '2');

        return $this->paymentManager->uploadValidationDocument($validationDocument);
    }

    private function _treatFile(ValidationDocument $validationDocument, File $file, string $fileNum = ''): ValidationDocument
    {
        $paramName = 'file'.$fileNum;
        $setFileName = 'setFileName'.$fileNum;
        $setExtension = 'setExtension'.$fileNum;

        $validationDocument->{$setFileName}(substr($file->getClientOriginalName(), 0, strrpos($file->getClientOriginalName(), '.')));
        $validationDocument->{$setExtension}(
            substr($file->getClientOriginalName(), strrpos($file->getClientOriginalName(), '.') + 1, strlen($file->getClientOriginalName()) - 1)
        );

        $this->uploadHandler->upload($validationDocument, $paramName);

        return $validationDocument;
    }

    private function _treatMandatoryFile(Request $request): ValidationDocument
    {
        $paramName = 'file';
        $uploadedFile = $request->files->get($paramName);
        if (!$uploadedFile) {
            throw new BadRequestHttpException('"file" is required');
        }
        $validationDocument = new ValidationDocument();
        $validationDocument->setFile($uploadedFile);
        $validationDocument->setUser($this->security->getUser());

        return $this->_treatFile($validationDocument, $validationDocument->getFile());
    }

    private function _treatOptionalFile(Request $request, ValidationDocument $validationDocument, string $fileNum = ''): ValidationDocument
    {
        $paramName = 'file'.$fileNum;
        $uploadedFile = $request->files->get($paramName);
        if (!$uploadedFile) {
            return $validationDocument;
        }
        $validationDocument->setFile2($uploadedFile);

        return $this->_treatFile($validationDocument, $validationDocument->getFile2(), $fileNum);
    }
}
