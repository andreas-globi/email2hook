#!/bin/bash
#
# daemon manager - runs every minute and ensures that daemon_master.php is running
#
# requires cron entry:
# * * * * * bash daemon_manager.sh

CURDIR=$(
  cd $(dirname "$0")
  pwd
)
DAEMON_SCRIPT="$CURDIR/daemon_master.php"

# check if running
PROCESS_COUNT=`pgrep -f $DAEMON_SCRIPT | wc -l`
if [ $PROCESS_COUNT -ge 1 ]; then
	exit 0
fi

# not running - launch
echo "launching $DAEMON_SCRIPT"
nohup php $DAEMON_SCRIPT >> /var/log/email2hook.log 2>&1 &
exit 0
