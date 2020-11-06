#!/bin/bash

# email2hook provision script
# - installs postfix if necessary
# - sets up initial configuration
#
# NOTE:
# - cannot be run as root - must be run as your user but your user must have sudo privs

USERNAME=`whoami`
MYUID=`echo $UID`

# make sure we're not root
# ==============================
if [ $USERNAME = "root" ]; then
	echo -e "ERROR: cannot be run as root. Run as user with sudo privs"
	exit 1
fi

# ensure postfix
# ==============================
echo -e "checking postfix"
OK=0
if apt -qq list --installed postfix 2>/dev/null | grep -q postfix; then
	OK=1
fi
if [ "$OK" = 0 ]; then
	echo -e "installing postfix"
	if ! eval "sudo apt-get install postfix -y"; then
		echo -e "ERROR: installing postfix failed"
		exit 1
	fi
fi

# ensure postfix-pcre
# ==============================
echo -e "checking postfix-pcre"
OK=0
if apt -qq list --installed postfix-pcre 2>/dev/null | grep -q postfix-pcre; then
	OK=1
fi
if [ "$OK" = 0 ]; then
	echo -e "installing postfix-pcre"
	if ! eval "sudo apt-get install postfix-pcre -y"; then
		echo -e "ERROR: installing postfix-pcre failed"
		exit 1
	fi
fi

# ensure php-cli
# ==============================
echo -e "checking php-cli"
OK=0
if apt -qq list --installed php-cli 2>/dev/null | grep -q php-cli; then
	OK=1
fi
if [ "$OK" = 0 ]; then
	echo -e "installing php-cli"
	if ! eval "sudo apt-get install php-cli -y"; then
		echo -e "ERROR: installing php-cli failed"
		exit 1
	fi
fi


# postfix config main.cf
# ==============================
echo -e "checking postfix config"

DELIM="# email2hook - do not touch"
CURDIR=$(
  cd $(dirname "$0")
  pwd
)

CONFIG=$(cat <<__EOT
$DELIM
virtual_mailbox_domains = pcre:$CURDIR/config/vdomains
virtual_mailbox_base = /home/$USERNAME/mail
virtual_mailbox_maps = pcre:$CURDIR/config/vmailbox
virtual_minimum_uid = $MYUID
virtual_uid_maps = static:$MYUID
virtual_gid_maps = static:$MYUID
virtual_mailbox_limit = 0
__EOT
)

EXISTING=$(sudo cat /etc/postfix/main.cf)
PART1=""
i=0
while read -r LINE; do
	if [ "$LINE" == "$DELIM" ]; then
		break
	fi
	PART1="${PART1}${LINE}"$'\n'
done <<< "$EXISTING"

REPLACE="${PART1}${CONFIG}"

if [ "$REPLACE" != "$EXISTING" ]; then
	echo -e "replacing config"
	echo "$REPLACE" | sudo tee /etc/postfix/main.cf > /dev/null
fi


# ensure virtual files and dirs
# ==============================
echo -e "checking presence of config files and dirs"
if [ ! -f $CURDIR/config/vdomains ]; then
	touch $CURDIR/config/vdomains
fi
if [ ! -f $CURDIR/config/vmailbox ]; then
	touch $CURDIR/config/vmailbox
fi
if [ ! -d /home/$USERNAME/mail ]; then
	mkdir /home/$USERNAME/mail
fi
if [ ! -f /var/log/email2hook.log ]; then
	sudo touch /var/log/email2hook.log
	sudo chown $USERNAME:$USERNAME /var/log/email2hook.log
	sudo chmod 664 /var/log/email2hook.log
fi

CONFIG=$(cat <<__EOT
<?php
// config
// [{name:"name",domains:["domain.com"],url:"http://domain.com/hook",count:1},...]
// where
// - name    = a generic service name
// - domains = an array of email catch domains (can include * wildcard)
// - url     = the http end-point to post to
// - count   = number of daemons to run
// see config.sample.php for examples
\$config = [
];
__EOT
)
if [ ! -f $CURDIR/config/config.php ]; then
	echo "$CONFIG" > $CURDIR/config/config.php
fi


# allow firewall if needed
# ==============================
echo -e "checking UFW"
if which ufw > /dev/null; then
	OK=0
	if sudo ufw status | grep -q "^Postfix[ \/].*ALLOW"; then
		OK=1
	fi
	if [ "$OK" = 0 ]; then
		echo -e "allowing postfix in ufw"
		sudo ufw allow Postfix
	fi
else
	echo -e "ufw not detected"
fi


# reload postfix
# ==============================
echo -e "reloading postfix"
sudo postfix reload


# ensure cron job
# ==============================
echo -e "ensuring cron job"

if ! crontab -l | grep -qF "daemon_manager"; then
	echo -e "installing cron"
	crontab -l > /tmp/cron
	echo -e "* * * * * bash $CURDIR/app/daemon_manager.sh" >> /tmp/cron
	if ! eval "crontab /tmp/cron"; then
		echo -e "ERROR: installing cron failed"
		exit 1
	fi
	service cron reload
fi


# done
# ==============================
echo -e "done"
exit 0
