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

DOMAIN=$(postmap -q "$ADDRESS" pcre:/etc/postfix/vdomains)
if [ "$DOMAIN" = "" ]; then
	DOMAIN="FAIL"
fi

MAILBOX=$(postmap -q "$ADDRESS" pcre:/etc/postfix/vmailbox)
if [ "$MAILBOX" = "" ]; then
	MAILBOX="FAIL ERROR"
fi

echo -e "$ADDRESS $DOMAIN $MAILBOX"
