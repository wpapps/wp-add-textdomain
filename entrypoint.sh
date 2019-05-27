#!/bin/bash

set -eo

# Allow some ENV variables to be customized
if [[ -z "$DOMAIN" ]]; then
	echo "Set TextDomain First"
	exit 1
fi

if [[ -z "$GITHUB_TOKEN" ]]; then
	echo "Set the GITHUB_TOKEN env variable"
	exit 1
fi

## Update Git Config.
git config --global user.email "wptextdomain+github@gmail.com" && git config --global user.name "WP TextDomain on GitHub"

cd ../
wget https://raw.githubusercontent.com/wpapps/wp-add-textdomain/master/addtextdomain.php

# Generate POT File
php addtextdomain.php -i "$DOMAIN" "$GITHUB_WORKSPACE"

# Update In Github
cd "$GITHUB_WORKSPACE"
git add -A
git commit -m "Textdomain Added"
git remote set-url origin https://$GITHUB_TOKEN@github.com/${GITHUB_REPOSITORY}.git
git push --set-upstream origin $GITHUB_REF -f
