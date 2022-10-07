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

"""
Add a language
==============

This script create all necessary files to handle a new translation language of Mobicoop-platform.
It must be launched from the root directory of the instance. It uses the english files as model.
It does the following : 

    1. create client components translation files
    2. create client components translation files for instances
    3. update client components with the new language
    4. create client ui translation files
    5. create client routes
    6. create api translation files

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
client_components_instance_translation_path = os.path.abspath(
    script_absolute_path+"/../client/translations/components/")
client_components_path = os.path.abspath(
    script_absolute_path+"/../client/src/MobicoopBundle/Resources/assets/js/components/")
client_ui_path = os.path.abspath(
    script_absolute_path+"/../client/src/MobicoopBundle/Resources/translations/UI/")
client_route_file = os.path.abspath(
    script_absolute_path+"/../client/src/MobicoopBundle/Resources/config/routes.yaml")
api_translations_path = os.path.abspath(
    script_absolute_path+"/../api/translations/")
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

def path_leaf(path):
    head, tail = ntpath.split(path)
    return tail or ntpath.basename(head)

# 0 - check that language does not exist yet !
# if os.path.isfile(api_translations_path+"/messages+intl-icu."+lang+".yaml"):
#     print("Language already exists !")
#     exit()

# 1 - create client components translation files 
files = directory_spider(client_components_translation_path, "", "_en.json$")
for file in files:
    filePath = os.path.dirname(file)
    fileWithoutExtension = os.path.splitext(file)[0]
    component_name = path_leaf(fileWithoutExtension.replace("_en", ""))
    newFile = file.replace("_en.json", "_"+lang+".json")
    if not os.path.isfile(newFile):
        copyfile(file, newFile)

    # Open the file in append & read mode ('a+')
    with open(filePath+"/index.js", "a+") as file_object:
        # Move read cursor to the start of file.
        file_object.seek(0)
        # If file is not empty then append '\n'
        data = file_object.read(100)
        if len(data) > 0:
            file_object.write("\n")
        # Append text at the end of file
        file_object.write("export {default as messages_"+lang+"} from './"+component_name+"_"+lang +".json';")

# 2 - create client components translation files for instances
files = directory_spider(client_components_instance_translation_path, "", "_en.json$")
for file in files:
    filePath = os.path.dirname(file)
    fileWithoutExtension = os.path.splitext(file)[0]
    component_name = path_leaf(fileWithoutExtension.replace("_en", ""))
    newFile = file.replace("_en.json", "_"+lang+".json")
    if not os.path.isfile(newFile):
        copyfile(file, newFile)

    # Open the file in append & read mode ('a+')
    with open(filePath+"/index.js", "a+") as file_object:
        # Move read cursor to the start of file.
        file_object.seek(0)
        # If file is not empty then append '\n'
        data = file_object.read(100)
        if len(data) > 0:
            file_object.write("\n")
        # Append text at the end of file
        file_object.write("export {default as messages_"+lang+"} from './"+component_name+"_"+lang +".json';")

# 3 - update client components with the new language
files = directory_spider(client_components_path, "", ".vue$")
for file in files:
    with open(file, 'r+') as f:
        file_source = f.read()
        # check if component uses translations
        if re.search('import {messages_en',file_source):
            # add messages for the new language
            file_source = re.sub('(import {messages_en,)(.*)(})(.*)\n', r'\g<1>\g<2>, messages_'+lang+'\g<3>\g<4>\n', file_source)
            # check for possible client override
            if re.search('import {messages_client_en',file_source):
                file_source = re.sub('(import {messages_client_en,)(.*)(})(.*)\n', r'\g<1>\g<2>, messages_client_'+lang+'\g<3>\g<4>\n', file_source)
                file_source = re.sub('(let MessagesMergedEn = merge\(messages_en, messages_client_en\);)\n', r'\g<1>\nlet MessagesMerged'+lang.capitalize()+' = merge(messages_'+lang+', messages_client_'+lang+');\n', file_source)
                file_source = re.sub('(.*)(\'en\': MessagesMergedEn,)\n', r"\g<1>\g<2>\n\g<1>'"+lang+'\': MessagesMerged'+lang.capitalize()+',\n', file_source)
            else:
                file_source = re.sub('(.*)(\'en\': messages_en,)\n', r"\g<1>\g<2>\n\g<1>'"+lang+'\': messages_'+lang+',\n', file_source)
            f.truncate(0)
            f.seek(0)
            f.write(file_source)

# 4 - create client ui translation files
if not os.path.isfile(client_ui_path+"/ui."+lang+".yaml"):
    copyfile(client_ui_path+"/ui.en.yaml", client_ui_path+"/ui."+lang+".yaml")

# 5 - create client routes
with open(client_route_file, 'r+') as f:
    file_source = f.read()
    file_source = re.sub('(\s+)(en: )(.*)\n', r"\g<1>\g<2>\g<3>\g<1>"+lang+": \g<3>\n", file_source)
    f.truncate(0)
    f.seek(0)
    f.write(file_source)

# 6 - create api translation files
if not os.path.isfile(api_translations_path+"/messages+intl-icu."+lang+".yaml"):
    copyfile(api_translations_path+"/messages+intl-icu.en.yaml", api_translations_path+"/messages+intl-icu."+lang+".yaml")