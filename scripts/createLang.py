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

import os
import re
import sys
import ntpath
from shutil import copyfile

def directory_spider(input_dir, path_pattern="", file_pattern=""):
    if not os.path.exists(input_dir):
        raise FileNotFoundError("Could not find path: %s"%(input_dir))
    for dirpath, dirnames, filenames in os.walk(input_dir):
        if re.search(path_pattern, dirpath):
            file_list = [item for item in filenames if re.search(file_pattern,item)]
            file_path_list = [os.path.join(dirpath, item) for item in file_list]
            for file in file_path_list:
                fileWithoutExtension = os.path.splitext(file)[0]
                filePath = os.path.dirname(file)
                copyfile(file, fileWithoutExtension+"_fr.json")
                copyfile(file, fileWithoutExtension+"_en.json")

def path_leaf(path):
    head, tail = ntpath.split(path)
    return tail or ntpath.basename(head)

# directory_spider("../client/src/MobicoopBundle/Resources/translations","",".json$")