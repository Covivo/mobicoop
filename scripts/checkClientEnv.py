#!/usr/bin/python

import os.path

# find client .env
if not os.path.isfile("../client/.env"):
    print ("Client .env not found !")
    exit()

# open client .env file
dotenv_mobicoop = open("../client/.env", "r")

# find instance .env file and append or create it
if not os.path.isfile("../../.env"):
    print ("Instance .env not found ! Creating it...")
    dotenv_instance = open("../../.env", "w+")
    dotenv_instance.close()
    
# open instance .env file
dotenv_instance = open("../../.env", "r")

# read mobicoop file line by line
dotenv_mobicoop_lines = dotenv_mobicoop.readlines()
dictenv_mobicoop = {}

for line in dotenv_mobicoop_lines:
    #skip lines starting with '#'
    if line[0] == '#':
        continue
    # find key
    index = line.find('=')
    if index > 0:
        key = line[:index]
        # find value, we strip if there's a comment on the same line
        value = line[index+1:]
        dictenv_mobicoop[key] = value.strip()

# close client .env file
dotenv_mobicoop.close()

# read instance file line by line
dotenv_instance_lines = dotenv_instance.readlines()
dictenv_instance = {}

for line in dotenv_instance_lines:
    index = line.find('=')
    if index > 0:
        key = line[:index]
        value = line[index+1:]
        dictenv_instance[key] = value.strip()

# close instance .env file 
dotenv_instance.close()
    
# re-open instance .env file for append
dotenv_instance = open("../../.env", "a+")

# check for differences
for key in dictenv_mobicoop:
    if key not in dictenv_instance.keys():
        print ("Key "+key+ " not found, adding it with default value : "+dictenv_mobicoop.get(key))
        dotenv_instance.write("\n"+key+'='+dictenv_mobicoop.get(key))

dotenv_instance.close()