#!/bin/bash

# reloads configuration

CURDIR=$(
  cd $(dirname "$0")
  pwd
)

echo -e "reloading"
if ! sudo php -q $CURDIR/app/config_maker.php; then
	echo -e "ERROR: failed generating config - please fix config file"
fi

echo -e "stopping daemons"
if ! php -q $CURDIR/app/daemon_kill.php; then
	echo -e "ERROR: failed killing process daemons"
fi

echo -e "done"
exit 0

