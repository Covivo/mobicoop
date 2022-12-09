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


class KeyAndValue:

    key_value_regexp = re.compile('(?P<key>[^#=]+)=(?P<value>[^#]*)')

    @staticmethod
    def matches(line):
        return KeyAndValue.key_value_regexp.match(line)


def get_keys_from_file(filename):

    with open(filename, mode="r", encoding="utf-8") as file:
        keys = {
            match.group("key") for match in
            map(KeyAndValue.matches, file)
            if match
        }
    return keys

def keys_and_duplicates_from_env_file(file):

    if not os.path.isfile(file):
        print(f"{file} not found!")
        return set()

    with open(file, mode="r", encoding="utf-8") as dotenv:
        counter = collections.Counter(
            match.group("key") for match in
            map(KeyAndValue.matches, dotenv)
            if match
        )

    duplicates = False
    for key, count in filter(lambda t: t[1] > 1, counter.items()):
        print(f"Duplicate key \033[1;37;40m{key}\033[0;37;40m"
                f" {count-1} times")
        duplicates = True
    if not duplicates:
        print("No duplicates found")

    return set(counter)

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

# check for differences
key_not_found = string.Template(
  "Key \033[1;37;40m$key\033[0;37;40m not found!")

if args.dry:
    # here we need only the keys of client env
    client_keys = get_keys_from_file(f"{client_path}/.env")
    # get instance keys
    instance_keys = get_keys_from_file(".env")
    #check the differences
    differences = False
    for key in filter(lambda key: key not in instance_keys, client_keys):
        print(key_not_found.substitute({'key': key}))
        differences = True
    if not differences:
        print("No differences found")
else:
    # here we need both keys and values of client env
    with open(f"{client_path}/.env", mode="r", encoding="utf-8") as clt_env:
        dict_client = {
            match.group("key"): match.group("value").strip() for match in
            map(KeyAndValue.matches, clt_env)
            if match
        }
    # check the differences
    differences = False
    with open(".env", mode="r+", encoding="utf-8") as dotenv_instance:
        # get instance keys
        instance_keys = {
            match.group("key") for match in
            map(KeyAndValue.matches, dotenv_instance)
            if match
        }
        # add missing keys and values
        for key, value in filter(lambda pair: pair[0] not in instance_keys,
                                 dict_client.items()):
            print(key_not_found.substitute({'key': key}))
            print(f"=> adding it with default value: {value}")
            dotenv_instance.write(f"\n{key}={value}")
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
instance_local_keys = keys_and_duplicates_from_env_file(f".env.{env}.local")

print ("\033[1;34;40m")
print (f"Checking API .env.{env}.local")
print ("\033[0;37;40m")
api_local_keys = keys_and_duplicates_from_env_file(f"{api_path}/.env.{env}.local")

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
if instance_local_keys:
    unnecessary_keys = False
    for key in filter(lambda key: key not in instance_keys,
                      instance_local_keys):
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
if api_local_keys:
    # get api keys
    api_keys = get_keys_from_file(f"{api_path}/.env")
    unnecessary_keys = False
    for key in filter(lambda key: key not in api_keys, api_local_keys):
        print(key_not_found.substitute(key=key, where=f"API {api_path}/.env"))
        unnecessary_keys = True
    if not unnecessary_keys:
        print(f"API .env.{env}.local OK")
else:
    print("Nothing to check")
