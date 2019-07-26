#!/bin/bash

WIKI_ROOT="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"/..
TARGET_BRANCH=web
DEV_BRANCH=master

if [ "x$1" != "x" ] ; then
TARGET_BRANCH=$1
fi

cd $WIKI_ROOT
b=`git rev-parse --abbrev-ref HEAD`

echo "Merging..."

git checkout $TARGET_BRANCH
git merge --no-ff $DEV_BRANCH
git checkout $b

echo "Pushing..."
git push origin $TARGET_BRANCH

echo
echo "Branch $TARGET_BRANCH released"
echo

