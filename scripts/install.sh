#!/bin/bash


WIKI_ROOT="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"/..
BRANCH=web

if [ "x$1" != "x" ] ; then
	BRANCH=$1
fi

cd $WIKI_ROOT
b=`git rev-parse --abbrev-ref HEAD`
if [ $b != $BRANCH ] ; then
	echo "Cannot install branch $BRANCH when checked out branch $b" >&2
	exit 1
fi

git pull
git submodule init
git submodule update

echo "Installed branch $BRANCH."

