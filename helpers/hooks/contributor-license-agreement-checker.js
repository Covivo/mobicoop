const program = require('commander');
const fs = require('fs');
const path = require('path');
const kuler = require('kuler');
const gitUserName = require('git-user-name');
const readline = require('readline');
const reader = readline.createInterface({
  input: process.stdin,
  output: process.stdout
});

console.log("*************************************************************************************\r \n",
   "Copyright (c) 2018, MOBICOOP. All rights reserved.\r \n",
   "This project is dual licensed under AGPL and proprietary licence.\r \n",
"\r \n",
   "This program is free software: you can redistribute it and/or modify\r \n",
   "it under the terms of the GNU Affero General Public License as\r \n",
   "published by the Free Software Foundation, either version 3 of the\r \n",
   "License, or (at your option) any later version.\r \n",
"\r \n",
   "This program is distributed in the hope that it will be useful,\r \n",
   "but WITHOUT ANY WARRANTY; without even the implied warranty of\r \n",
   "MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the\r \n",
   "GNU Affero General Public License for more details.\r \n",
"\r \n",
   "You should have received a copy of the GNU Affero General Public License\r \n",
   "along with this program.  If not, see <gnu.org/licenses>.\r \n",
"\r \n",
   "Licence MOBICOOP described in the file LICENSE\r \n",
"*************************************************************************************\r \n")
console.log(gitUserName())




console.log("Your git username is not registrered on the contributor list")

console.log("YYYY-MM-DD")