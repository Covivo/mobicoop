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

namespace App\Utility\Command;

use App\Utility\Entity\CsvMaker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Make Csv command.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class MakeCsvCommand extends Command
{
    private $_csvMaker;

    public function __construct(CsvMaker $csvMaker)
    {
        parent::__construct();
        $this->_csvMaker = $csvMaker;
    }

    protected function configure()
    {
        $this
            ->setName('app:utility:make-csv')
            ->setDescription('Make Csv file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_csvMaker->setQuery('SELECT id, given_name, family_name, gender, email, telephone, created_date, last_activity_date FROM `user`');

        return $this->_csvMaker->make();
    }
}
