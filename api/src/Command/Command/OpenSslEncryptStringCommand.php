<?php

/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
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

declare(strict_types=1);

namespace App\Command\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OpenSslEncryptStringCommand extends Command
{
    private $_secret;

    public function __construct(string $secret)
    {
        $this->_secret = $secret;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:commands:openssl:encrypt')
            ->addArgument('string', InputArgument::REQUIRED, 'The string to encrypt')
            ->setDescription('OpenSSL Encrypt Command')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $token = $input->getArgument('string');
        $cipher_method = 'aes-128-ctr';
        $enc_iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher_method));
        $crypted_token = openssl_encrypt($token, $cipher_method, $this->_secret, 0, $enc_iv).'::'.bin2hex($enc_iv);
        unset($token, $cipher_method, $enc_iv);

        echo $crypted_token;
    }
}
