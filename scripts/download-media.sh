#!/bin/bash


WIKI_ROOT="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"/..
URL=vyfuk-git@atrey.karlin.mff.cuni.cz:/home/Akce/vyfuk/WWW/Vyfuk_stranky

rsync -aLzv $URL/data/media $WIKI_ROOT/data
rsync -aLzv $URL/data/media_meta $WIKI_ROOT/data

# Media history is ignored
# rsync -aLzv $URL/data/media_attic $WIKI_ROOT/data/media_attic

