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

import argparse
import collections
import os.path
import re
import string
import sys

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
client_path = f"{args.path}/client"
api_path = f"{args.path}/api"

key_value_regexp = re.compile('(?P<key>[^#=]+)=(?P<value>[^#]*)')


def get_keys_from_file(filename):

    with open(filename, mode="r", encoding="utf-8") as file:
        keys = {
            match.group("key") for match in
            map(lambda line: key_value_regexp.match(line), file)
            if match
        }
    return keys

class DuplicatesCounter:

    def __init__(self):
        self.d = collections.defaultdict(int)

    def compute(self, key):
        self.d[key] += 1

    def print(self):
        duplicates = False
        for key, count in filter(lambda t: t[1] > 1, self.d.items()):
            print(f"Duplicate key \033[1;37;40m{key}\033[0;37;40m"
                  f" {count-1} times")
            duplicates = True
        if not duplicates:
            print("No duplicates found")

class DuplicatesBypass:

    def compute(self, key):
        pass

    def print(self):
        pass

def env_file_to_dict(file, duplicates=DuplicatesBypass()):

    my_dict = {}

    if not os.path.isfile(file):
        print(f"{file} not found!")
        return my_dict

    with open(file, mode="r", encoding="utf-8") as dotenv:
        for line in dotenv:
            match = key_value_regexp.match(line)
            if match:
                my_dict[match.group('key')] = match.group('value').strip()
                duplicates.compute(match.group('key'))

    duplicates.print()

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
if not os.path.isfile(f"{api_path}/.env"):
    sys.exit(f"API .env not found in {api_path}!")

# find client .env
if not os.path.isfile(f"{client_path}/.env"):
    sys.exit(f"Client .env not found in {client_path}!")

# find instance .env file and append or create it
with open(".env", mode="a", encoding="utf-8") as env_file:
    pass

# create api dictionary
dict_api = env_file_to_dict(f"{api_path}/.env")

# create client dictionary
dict_client = env_file_to_dict(f"{client_path}/.env")

# create instance dictionary
dict_instance = env_file_to_dict(".env")

# check for differences
key_not_found = string.Template(
  "Key \033[1;37;40m$key\033[0;37;40m not found!")

if args.dry:
    differences = False
    for key in filter(lambda key: key not in dict_instance, dict_client):
        print(key_not_found.substitute({'key': key}))
        differences = True
    if not differences:
        print("No differences found")
else:
    differences = False
    with open(".env", mode="a+", encoding="utf-8") as dotenv_instance:
        for key in filter(lambda key: key not in dict_instance, dict_client):
            print(key_not_found.substitute({'key': key}))
            print(f"=> adding it with default value: {dict_client[key]}")
            dotenv_instance.write(f"\n{key}={dict_client[key]}")
            differences = True
    if not differences:
        print("No differences found")

##############################
# 2. identify duplicate keys #
##############################

print ("\033[1;32;40m")
print ("----------------------")
print ("2. IDENTIFY DUPLICATES")
print ("----------------------")

print ("\033[1;34;40m")
print (f"Checking instance .env.{env}.local")
print ("\033[0;37;40m")
dict_instance_local = env_file_to_dict(f".env.{env}.local", DuplicatesCounter())

print ("\033[1;34;40m")
print (f"Checking API .env.{env}.local")
print ("\033[0;37;40m")
dict_api_local = env_file_to_dict(f"{api_path}/.env.{env}.local", DuplicatesCounter())

################################
# 3. identify unnecessary keys #
################################

print ("\033[1;32;40m")
print ("----------------------------")
print ("3. IDENTIFY UNNECESSARY KEYS")
print ("----------------------------")

key_not_found = string.Template(
  f"Key \033[1;37;40m$key\033[0;37;40m not found in $where")

# instance
print ("\033[1;34;40m")
print (f"Checking instance .env.{env}.local")
print ("\033[0;37;40m")
if dict_instance_local:
    unnecessary_keys = False
    for key in filter(lambda key: key not in dict_instance,
                      dict_instance_local):
        print(key_not_found.substitute(key=key, where="instance .env"))
        unnecessary_keys = True
    if not unnecessary_keys:
        print(f"Instance .env.{env}.local OK")
else:
    print("Nothing to check")

# api
print ("\033[1;34;40m")
print (f"Checking API .env.{env}.local")
print ("\033[0;37;40m")
if dict_api_local:
    unnecessary_keys = False
    for key in filter(lambda key: key not in dict_api, dict_api_local):
        print(key_not_found.substitute(key=key, where=f"API {api_path}/.env"))
        unnecessary_keys = True
    if not unnecessary_keys:
        print(f"API .env.{env}.local OK")
else:
    print("Nothing to check")
