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
Add a language
==============

This script create all necessary files to handle a new translation language of Mobicoop-platform.
It must be launched from the root directory of the instance. It uses the english files as model.
It does the following : 

    1. create client components translation files 
    2. update client components with the new language
    2. create client ui translation files
    3. create api translation files

Parameters
----------
    -h :
        This help
    -lang <language> : str, mandatory
        The language code
"""

import os
import re
import sys
import ntpath
from shutil import copyfile

script_absolute_path = os.path.dirname(os.path.realpath(__file__))
client_components_translation_path = os.path.abspath(
    script_absolute_path+"/../client/src/MobicoopBundle/Resources/translations/components/")
lang = ""

if len(sys.argv) < 3:
    print("Language code is mandatory")
    exit()

pos = 1
args = len(sys.argv) - 1
while (args >= pos):
    if sys.argv[pos] == "-h":
        print(__doc__)
        exit()
    elif sys.argv[pos] == "-lang":
        lang = sys.argv[pos+1]
    pos = pos + 1

if lang == "":
    print("Language code is mandatory")
    exit()

# useful functions


def directory_spider(input_dir, path_pattern="", file_pattern=""):
    file_paths = []
    if not os.path.exists(input_dir):
        raise FileNotFoundError("Could not find path: %s" % (input_dir))
    for dirpath, dirnames, filenames in os.walk(input_dir):
        if re.search(path_pattern, dirpath):
            file_list = [item for item in filenames if re.search(
                file_pattern, item)]
            file_path_list = [os.path.join(dirpath, item)
                              for item in file_list]
            file_paths += file_path_list
    return file_paths
    # for file in file_path_list:
    #     fileWithoutExtension = os.path.splitext(file)[0]
    #     filePath = os.path.dirname(file)
    #     copyfile(file, fileWithoutExtension+"_fr.json")
    #     copyfile(file, fileWithoutExtension+"_en.json")


def path_leaf(path):
    head, tail = ntpath.split(path)
    return tail or ntpath.basename(head)

# 0 - check that language does not exist yet !


# 1 - create client components files
files = directory_spider(client_components_translation_path, "", "_en.json$")

for file in files:
    filePath = os.path.dirname(file)
    fileWithoutExtension = os.path.splitext(file)[0]
    component_name = path_leaf(fileWithoutExtension.replace("_en", ""))
    newFile = file.replace("_en.json", "_"+lang+".json")
    copyfile(file, newFile)
    # print("Nouveau fichier : "+newFile)
    # print("Composant : "+component_name)

    # Open the file in append & read mode ('a+')
    with open(filePath+"/index.js", "a+") as file_object:
        # Move read cursor to the start of file.
        file_object.seek(0)
        # If file is not empty then append '\n'
        data = file_object.read(100)
        if len(data) > 0:
            file_object.write("\n")
        # Append text at the end of file
        file_object.write("export {default as messages_"+lang+"} from './"+component_name+"_"+lang +".json'")

# directory_spider("../client/src/MobicoopBundle/Resources/translations","",".json$")
