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
 **************************/

namespace App\Exception;

use App\Exception\Repository\MessageRepository;
use App\Exception\Entity\Message;
use Throwable;

class ApiException extends \Exception
{
    /**
     * @var Message $exception_message
     */
    private $exception_message=null;
    
    
    public function __construct($messageKey = "", $code = 0, Throwable $previous = null, MessageRepository $messageRepository=null)
    {
        $this->setExceptionMessage($messageRepository->findOneByShortname($messageKey));
        parent::__construct($this->getExceptionMessage()->getShortname(),$code,$previous);
    }
    
    /**
     * @return Message
     */
    public function getExceptionMessage(): Message
    {
        return $this->exception_message;
    }
    
    /**
     * @param Message $exception_message
     */
    public function setExceptionMessage(Message $exception_message)
    {
        $this->exception_message = $exception_message;
    }
}