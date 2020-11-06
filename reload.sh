#!/bin/bash

# reloads configuration

CURDIR=$(
  cd $(dirname "$0")
  pwd
)

echo -e "reloading"
if ! php -q $CURDIR/app/config_maker.php; then
	echo -e "ERROR: failed generating config - please fix config file"
fi

echo -e "done"
exit 0

