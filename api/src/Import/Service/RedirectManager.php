<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

namespace App\Import\Service;

use App\Article\Repository\ArticleRepository;
use App\Community\Repository\CommunityRepository;
use App\Event\Repository\EventRepository;
use App\Import\Entity\Redirect;
use App\Import\Repository\RedirectRepository;

/**
 * Redirect manager service.
 * Used to match uri between an old and a new platform.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class RedirectManager
{
    private $redirectRepository;
    private $communityRepository;
    private $eventRepository;
    private $articleRepository;

    /**
     * Constructor.
     */
    public function __construct(RedirectRepository $redirectRepository, CommunityRepository $communityRepository, EventRepository $eventRepository, ArticleRepository $articleRepository)
    {
        $this->redirectRepository = $redirectRepository;
        $this->communityRepository = $communityRepository;
        $this->eventRepository = $eventRepository;
        $this->articleRepository = $articleRepository;
    }

    /**
     * Search redirections.
     *
     * @param string $originUri The original uri to search
     *
     * @return array The redirections found
     */
    public function getRedirect(string $originUri): array
    {
        if ($redirects = $this->redirectRepository->findBy(['originUri' => $originUri])) {
            $redirect = $redirects[0];

            switch ($redirect->getType()) {
                case Redirect::TYPE_COMMUNITY:
                    if ($community = $this->communityRepository->find($redirect->getDestinationId())) {
                        $redirect->setDestinationComplement($community->getName());
                    }

                    break;

                case Redirect::TYPE_EVENT:
                    if ($event = $this->eventRepository->find($redirect->getDestinationId())) {
                        $redirect->setDestinationComplement($event->getName());
                    }

                    break;

                case Redirect::TYPE_ARTICLE:
                    if ($article = $this->articleRepository->find($redirect->getDestinationId())) {
                        $redirect->setDestinationComplement($article->getTitle());
                    }

                    break;

                case Redirect::TYPE_COMMUNITY_WIDGET:
                    if ($community = $this->communityRepository->find($redirect->getDestinationId())) {
                        $redirect->setDestinationComplement($community->getName());
                    }

                    break;

                case Redirect::TYPE_EVENT_WIDGET:
                    if ($event = $this->eventRepository->find($redirect->getDestinationId())) {
                        $redirect->setDestinationComplement($event->getName());
                    }

                    break;
            }

            return [$redirect];
        }
        if ($redirects = $this->redirectRepository->findByUriWithWildCard($originUri)) {
            return [$redirects[0]];
        }

        return [];
    }
}
