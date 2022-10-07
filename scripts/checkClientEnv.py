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
import collections

script_absolute_path = os.path.dirname(os.path.realpath(__file__))
platform_path = os.path.abspath(script_absolute_path
                                + "/../mobicoop-platform")

parser = argparse.ArgumentParser(
  description='''\
Dotenv instance checker
=======================
This script allows to check the (many) dotenv files of an instance of Mobicoop-platform.
It must be launched from the root directory of the instance.
It does the following :

    1. check that all bundle client .env keys are present in instance .env,\
 if not it copies the missing keys with default values
    2. identify the duplicate keys in local .env (instance, bundle api)
    3. identify unnecessary local .env keys
''',
  formatter_class=argparse.RawDescriptionHelpFormatter
)
parser.add_argument('-p', '--path', default=platform_path,
                    help='The absolute path to the mobicoop platform')
parser.add_argument('-e', '--env', choices=('test', 'dev', 'prod'),
     default='dev', help='The env to check: dev, test or prod (default: dev)')
parser.add_argument('--dry', help='Show information only (no append file)',
                    action='store_true')
# read arguments
args = parser.parse_args()

env = args.env
client_path = args.path + "/client/"
api_path = args.path + "/api/"


def env_file_to_dict(file, check_duplicates = False):

    my_dict = {}

    if not os.path.isfile(file):
        print(f"{file} not found !")
        return my_dict

    if check_duplicates:
        key_counter = collections.defaultdict(int)

    with open(file, mode="r", encoding="utf-8") as dotenv:
        for line in dotenv:
            if not line.strip() or line[0] == '#':
                continue
            try:
                key, value = line.split('=')
            except ValueError:
                pass
            else:
                my_dict[key] = value.strip()
                if check_duplicates:
                    key_counter[key] += 1

    if check_duplicates:
        duplicates = False
        for key, count in filter(lambda t: t[1] > 1, key_counter.items()):
            print(
      f"Duplicate key \033[1;37;40m{key}\033[0;37;40m {count-1} times")
            duplicates = True
        if not duplicates:
            print("No duplicates found")

    return my_dict

####################################################
# 1. check differences between bundle and instance #
####################################################

print ("\033[1;32;40m")
print ("--------------------")
print ("1. CHECK DIFFERENCES")
print ("--------------------")
print ("\033[0;37;40m")

# find api .env
if not os.path.isfile(api_path+".env"):
    print ("API .env not found in "+api_path+" !")
    exit()

# find client .env
if not os.path.isfile(client_path+".env"):
    print ("Client .env not found in "+client_path+" !")
    exit()

# find instance .env file and append or create it
if not os.path.isfile(".env"):
    print ("Instance .env not found !")
    exit()

# create api dictionary
dict_api = env_file_to_dict(api_path+".env")

# create client dictionary
dict_client = env_file_to_dict(client_path+".env")

# create instance dictionary
dict_instance = env_file_to_dict(".env")

# open instance .env file for append
dotenv_instance = open(".env", "a+")

# check for differences
differences = 0
for key in dict_client:
    if key not in dict_instance.keys():
        print ("Key \033[1;37;40m"+key+"\033[0;37;40m not found !")
        if not args.dry:
            print("=> adding it with default value : "+dict_client.get(key))
            differences = differences + 1
            dotenv_instance.write("\n"+key+'='+dict_client.get(key))
if differences == 0:
    print("No differences found")

dotenv_instance.close()

##############################
# 2. identify duplicate keys #
##############################

print ("\033[1;32;40m")
print ("----------------------")
print ("2. IDENTIFY DUPLICATES")
print ("----------------------")

print ("\033[1;34;40m")
print ("Checking instance .env."+env+".local")
print ("\033[0;37;40m")
dict_instance_local = env_file_to_dict(".env."+env+".local",True)

print ("\033[1;34;40m")
print ("Checking API .env."+env+".local")
print ("\033[0;37;40m")
dict_api_local = env_file_to_dict(api_path+".env."+env+".local",True)

################################
# 3. identify unnecessary keys #
################################

print ("\033[1;32;40m")
print ("----------------------------")
print ("3. IDENTIFY UNNECESSARY KEYS")
print ("----------------------------")

# api
print ("\033[1;34;40m")
print ("Checking instance .env."+env+".local")
print ("\033[0;37;40m")
if len(dict_instance_local)>0:
    unnecessary_keys = 0
    for key in dict_instance_local:
        if key not in dict_instance.keys():
            print ("Key \033[1;37;40m"+key+"\033[0;37;40m not found in instance .env."+env+".local !")
            unnecessary_keys = unnecessary_keys + 1
    if unnecessary_keys == 0:
        print ("Instance .env."+env+".local OK")
else:
    print ("Nothing to check")

# api
print ("\033[1;34;40m")
print ("Checking API .env."+env+".local")
print ("\033[0;37;40m")
if len(dict_api_local)>0:
    unnecessary_keys = 0
    for key in dict_api_local:
        if key not in dict_api.keys():
            print ("Key \033[1;37;40m"+key+"\033[0;37;40m not found in API .env."+env+".local !")
            unnecessary_keys = unnecessary_keys + 1
    if unnecessary_keys == 0:
        print ("API .env."+env+".local OK")
else:
    print ("Nothing to check")
