#!/bin/bash

# This script assumes your web server runs under 'www-data' user.

WIKI_ROOT="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"/..

# set data writable by "server" user

find $WIKI_ROOT/data -type d -exec setfacl -m user:www-data:rwx {} \;
find $WIKI_ROOT/data -type d -exec setfacl -m default:user:www-data:rwx {} \;
find $WIKI_ROOT/data -type f -exec setfacl -m user:www-data:rw- {} \;

# set configuration writable
setfacl -m user:www-data:rw- $WIKI_ROOT/conf/local.php


