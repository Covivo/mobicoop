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

namespace App\User\Admin\Serializer;

use App\Carpool\Repository\ProposalRepository;
use App\Carpool\Ressource\Ad;
use App\User\Entity\User;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Normalizer used to add adType to each User of a User collection.
 */
final class UsersNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    private $decorated;
    private $proposalRepository;

    public function __construct(
        NormalizerInterface $decorated,
        ProposalRepository $proposalRepository
    ) {
        if (!$decorated instanceof DenormalizerInterface) {
            throw new \InvalidArgumentException(sprintf('The decorated normalizer must implement the %s.', DenormalizerInterface::class));
        }
        $this->decorated = $decorated;
        $this->proposalRepository = $proposalRepository;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $this->decorated->supportsNormalization($data, $format) && $data instanceof User;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $data = $this->decorated->normalize($object, $format, $context);
        if (isset($context['collection_operation_name']) && $context['collection_operation_name'] === 'ADMIN_get') {
            $nbDriver = $this->proposalRepository->getNbActiveAdsForUserAndRole($data['id'], Ad::ROLE_DRIVER);
            $nbPassenger = $this->proposalRepository->getNbActiveAdsForUserAndRole($data['id'], Ad::ROLE_PASSENGER);
            if ($nbDriver>0 && $nbPassenger>0) {
                $data['adType'] = User::AD_DRIVER_PASSENGER;
            } elseif ($nbDriver>0) {
                $data['adType'] = User::AD_DRIVER;
            } elseif ($nbPassenger>0) {
                $data['adType'] = User::AD_PASSENGER;
            } else {
                $data['adType'] = User::AD_NONE;
            }
        }
        return $data;
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $this->decorated->supportsDenormalization($data, $type, $format);
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        return $this->decorated->denormalize($data, $class, $format, $context);
    }

    public function setSerializer(SerializerInterface $serializer)
    {
        if ($this->decorated instanceof SerializerAwareInterface) {
            $this->decorated->setSerializer($serializer);
        }
    }
}
