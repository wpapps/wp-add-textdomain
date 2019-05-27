#!/bin/bash

# Note that this does not use pipefail
# because if the grep later doesn't match any deleted files,
# which is likely the majority case,
# it does not exit with a 0, and I only care about the final exit.
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

#cd "$GITHUB_WORKSPACE"

# Generate POT File
php addtextdomain.php -i "$DOMAIN" "$GITHUB_WORKSPACE"
#wp i18n make-pot . "$SAVE_PATH" --slug="$ITEM_SLUG" --package-name="$PACKAGE_NAME" --headers="$HEADERS" --allow-root

# ls -l | cat $SAVE_PATH | echo ${GITHUB_REF} | echo $GITHUB_REF

# Update In Github
git add -A
git commit -m "Textdomain Added"
git remote set-url origin https://$GITHUB_TOKEN@github.com/${GITHUB_REPOSITORY}.git
git push --set-upstream origin $GITHUB_REF -f
