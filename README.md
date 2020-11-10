# email2hook

## Inbound Email to HTTP Webhook Gateway

Easily set up an Ubuntu server as a mailsink that receives inbound email and POSTs it to webhooks.

## Architecture

 - Postfix accepts email and saves it to /home/{USERNAME}/mail/
 - PHP daemons pick up email from /home/{USERNAME}/mail and POSTs to your webhooks
 - After posting, email is deleted, and on failure, is re-tried with exponential back-off at the end of the queue
 - Postfix virtual config files stored in `config/vdomains` and `config/vmailbox`
 - Postfix mailbox files are default `{timestamp}.{idnumber}.{hostname}`
 - Failed mailbox files are named `xerr{timestamp}.{error_count}.{original_timestamp}.{idnumber}.{hostname}` - this ensures failures go to the bottom of the queue

## Requirements

 - Ubuntu Server (18.04+)
 - non-root user with sudo privileges
 
## Installation

```
cd ~
git clone https://github.com/andreas-globi/email2hook.git
cd email2hook
bash provision.sh
```

## Configuration

1. edit `config/config.php` (see config/config.sample.php for inspiration)
2. reload with `bash reload.sh` (note that it can take up to a minute for daemons to reload - have to wait for the cron job to re-spawn them)

## Administration

To see your queue sizes and ages, use: `php stats.php`

Logs are in `/home/{USERNAME}/email2hook.log`

To test an email route, use `bash testaddress.sh {emailaddress}` - eg `bash testaddress.sh me@domain.com`

---

### Reasoning

Although most ESP's offer inbound email parsing, it creates unnecessary vendor lock-in. Most also do not offer wildcard subdomains. Separation of concerns is extremely important in your code AND in your architecture.

This simple Postfix PHP daemon setup can handle all your incoming email for a super low price (a basic 2GB Digital Ocean droplet can easily handle many thousands of emails per day). It handles wildcard subdomains, and removes all reliance on outside parties.

### Resilliency

There's no need to run this in a cluster. Even with all it's flaws, email is resillient by design. If the server cannot accept a message for any reason, the sending server will retry again later.

If it makes you feel better, you can add an email continuity service into the mix with something like DNS Made Easy (around $13/year/domain).