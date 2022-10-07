#!/usr/bin/env python3

# Copyright (c) 2020, MOBICOOP. All rights reserved.
# This project is dual licensed under AGPL and proprietary licence.
# #######################################
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as
# published by the Free Software Foundation, either version 3 of the
# License, or (at your option) any later version.

# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Affero General Public License for more details.

# You should have received a copy of the GNU Affero General Public License
# along with this program.  If not, see <gnu.org/licenses>.
# #######################################
# Licence MOBICOOP described in the file
# LICENSE
# #######################################

import os.path
import argparse
from crontab import CronTab

script_absolute_path = os.path.dirname(os.path.realpath(__file__))
console_path = os.path.abspath(script_absolute_path + "/../api/bin/console")
crontab_file_path = os.path.abspath(script_absolute_path
                    + "/../api/scripts/cron-file.txt")

parser = argparse.ArgumentParser(
  description='Crontab updater:'
  ' This script updates the crontab with the needed jobs.'
  ' It is mainly intended to launch symfony console command.'
  ' It has to be launched by the target crontab user.',
  formatter_class=argparse.ArgumentDefaultsHelpFormatter)
parser.add_argument('-c', '--console', default=console_path,
                    dest='console_path', help='The console command path')
parser.add_argument('-e', '--env', default='dev', dest='env_mode',
                    choices=('test', 'dev', 'prod'), help='The environment')
parser.add_argument('-p', '--php', default='php', dest='php_path',
                    help='The absolute path to the php binary')
# read arguments
args = parser.parse_args()

my_cron = CronTab(user=True)

# open the crontab file
with open(file=crontab_file_path, mode="r", encoding="utf-8") as crontab_file:
    # read file line by line
    for line in crontab_file:
        # skip blank lines or starting with '#'
        if not line.strip() or line[0] == '#':
            continue

        line = line.replace("$1", args.php_path)
        line = line.replace("$2", args.console_path)
        line = line.replace("$3", args.env_mode)

        schedule = line.split(args.php_path, 1)[0].strip()
        command = line.split(schedule, 1)[1].strip()

        # search if job already exists
        for _ in my_cron.find_command(command):
            break
        else:
            job  = my_cron.new(command=command)
            job.setall(schedule)
            my_cron.write()
            print(f"{line} was added to crontab")
# the file is closed after the with statement
