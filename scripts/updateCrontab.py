#!/usr/bin/python

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

"""
Crontab updater
===============

This script updates the crontab with the needed jobs. It is mainly intended to launch symfony console command.
It has to be launched by the target crontab user.

Parameters
----------
    -h :
        This help
    -env <env> : str, optional
        The environment (default : dev)
    -php <php_path> : str, optional
        The absolute path to the php binary (default : php)
    -console <console_path> : str, optional
        The console command path (default : absolute path of <this_script_absolute_path>/../api/bin/)
"""

import os.path
import sys
from crontab import CronTab

script_absolute_path = os.path.dirname(os.path.realpath(__file__))
console_path = os.path.abspath(script_absolute_path+"/../api/bin/console")
crontab_file_path = os.path.abspath(script_absolute_path+"/../api/scripts/cron-file.txt")
php_path = "php"
env_mode = "dev"

# read arguments
if len(sys.argv)>1:
    if (len(sys.argv)>7):
        print("Wrong number of arguments !")
        exit()
    pos = 1
    args = len(sys.argv) - 1
    while (args >= pos):
        if sys.argv[pos] == "-h":
            print(__doc__)
            exit()
        elif sys.argv[pos] == "-env":
            env_mode = sys.argv[pos+1]
        elif sys.argv[pos] == "-php":
            php_path = sys.argv[pos+1]
        elif sys.argv[pos] == "-console":
            console_path = sys.argv[pos+1]
        pos = pos + 1

my_cron = CronTab(user=True)

# open the crontab file
crontab_file = open(crontab_file_path, "r")

# read file line by line
file_lines = crontab_file.readlines()

for line in file_lines:
    # skip blank lines or starting with '#'
    if not line.strip():
        continue
    if line[0] == '#':
        continue

    line = line.replace("$1", php_path)
    line = line.replace("$2", console_path)
    line = line.replace("$3", env_mode)

    schedule = line.split(php_path,1)[0].strip()
    command = line.split(schedule,1)[1].strip()

    # search if job already exists
    iter = my_cron.find_command(command)
    found = False
    for item in iter:
        found = True
        break

    if not found:
        job  = my_cron.new(command=command)
        job.setall(schedule)
        my_cron.write()
        print(line+ " was added to crontab")
