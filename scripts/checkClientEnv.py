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
Dotenv instance checker
=======================

This script allows to check the (many) dotenv files of an instance of Mobicoop-platform.
It must be launched from the root directory of the instance.
It does the following :

    1. check that all bundle client .env keys are present in instance .env, if not it copies the missing keys with default values
    2. identify the duplicate keys in local .env (instance, bundle api)
    3. identify unnecessary local .env keys

Parameters
----------
    -h :
        This help
    -path <platform_path> : str, optional
        The absolute path to the mobicoop platform (default : absolute path of *current_script_path*/../mobicoop-platform)
    -env <env> : str, optional
        The env to check : dev, test or prod (default : dev)
    -dry :
        Show information only (no append file)
"""

import os.path
import sys

script_absolute_path = os.path.dirname(os.path.realpath(__file__))
platform_path = os.path.abspath(script_absolute_path+"/../mobicoop-platform")
dry = False
env = "dev"

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
        elif sys.argv[pos] == "-path":
            platform_path = sys.argv[pos+1]
        elif sys.argv[pos] == "-env":
            env = sys.argv[pos+1]
        elif sys.argv[pos] == "-dry":
            dry = True
        pos = pos + 1

client_path = platform_path+"/client/"
api_path = platform_path+"/api/"


def env_file_to_dict(file, check_duplicates = False):

    my_dict = {}
    duplicates = 0

    if not os.path.isfile(file):
        print(file+" not found !")
        return my_dict

    # open file
    dotenv = open(file, "r")

    # read file line by line
    file_lines = dotenv.readlines()

    for line in file_lines:
        #skip lines starting with '#'
        if line[0] == '#':
            continue
        # find key
        index = line.find('=')
        if index > 0:
            key = line[:index]
            if check_duplicates and key in my_dict.keys():
                print("Duplicate key \033[1;37;40m"+key+"\033[0;37;40m")
                duplicates = duplicates + 1
            # find value, we strip if there's a comment on the same line
            value = line[index+1:]
            my_dict[key] = value.strip()

    dotenv.close()

    if check_duplicates:
        if duplicates == 0:
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
        if not dry:
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
