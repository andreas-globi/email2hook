#!/bin/bash

# test an email address
# usage:
# bash testaddress.sh email@address.com

# require input
ADDRESS="$1"
if [ "$ADDRESS" = "" ]; then
	echo -e "test an email address"
	echo -e "usage:"
	echo -e "bash testaddress.sh email@address.com"
	exit 0
fi

# find config
CURDIR=$(
  cd $(dirname "$0")
  pwd
)
CONFDIR="$CURDIR/config/"

DOMAIN=$(postmap -q "$ADDRESS" pcre:/etc/email2hook/vdomains)
if [ "$DOMAIN" = "" ]; then
	DOMAIN="FAIL"
fi

MAILBOX=$(postmap -q "$ADDRESS" pcre:/etc/email2hook/vmailbox)
if [ "$MAILBOX" = "" ]; then
	MAILBOX="FAIL"
fi

echo -e "$ADDRESS domain $DOMAIN map $MAILBOX"
