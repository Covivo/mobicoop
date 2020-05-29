#!/usr/bin/python

"""
Crontab updater
===============

This script updates the crontab with the needed jobs. It is mainly intended to launch symfony console command.

Parameters
----------
    -h :
        This help
    -php <php_path> : str, optional
        The absolute path to the php binary (default : /usr/bin/php7.2)
    -console <console_path> : str, optional
        The console command path (default : absolute path of <this_script_absolute_path>/../api/bin/)
"""

import os.path
import sys
from crontab import CronTab 

script_absolute_path = os.path.dirname(os.path.realpath(__file__))
console_path = os.path.abspath(script_absolute_path+"/../api/bin/console")
crontab_file_path = os.path.abspath(script_absolute_path+"/../api/scripts/cron-file.txt")
php_path = "/usr/bin/php7.2"

# read arguments
if len(sys.argv)>1:
    if (len(sys.argv)>5):
        print("Wrong number of arguments !")
        exit()
    pos = 1
    args = len(sys.argv) - 1
    while (args >= pos):
        if sys.argv[pos] == "-h":
            print(__doc__)
            exit()
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
    #skip lines starting with '#'
    if line[0] == '#':
        continue
    
    command = line.split("$2",1)[1].strip() 
    print(command)
    line = line.replace("$1", php_path)
    line = line.replace("$2", console_path)
    print(line)

    # search if job already exists
    iter = my_cron.find_command(command)
    found = False
    for item in iter:
        found = True

    if not found:
        cron_job = CronTab(tab=line)
        my_cron.write()

