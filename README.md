# email2hook

## Inbound Email to HTTP Webhook Gateway

Easily set up an Ubuntu server as a mailsink that receives inbound email and POSTs it to webhooks.

## Architecture

 - Postfix accepts email and save it to /home/{USERNAME}/mail
 - PHP daemons pick up email from /home/{USERNAME}/mail and POSTs to your webhooks
 - After posting, email is deleted, and on failure, is re-tried at the end of the queue
 - Postfix virtual config files stored in `config/vdomains` and `config/vmailbox`
 - Postfix mailbox files are default `{timestamp}.{idnumber}.{hostname}`
 - Failed mailbox files are named `xerr{timestamp}.{error_count}.{original_timestamp}.{idnumber}.{hostname}` - this ensures failures go to the bottom of the queue

## Requirements

 - Ubuntu Server (18.04+)
 - non-root user with sudo privileges
 
## Installation

```
cd ~
git clone git@github.com:andreas-globi/email2hook.git
cd email2hook
bash provision.sh
```

## Configuration

1. edit `config/config.php`
2. reload with `bash reload.sh` (note that it can take up to a minute for daemons to reload - have to wait for the cron job to re-spawn them)

## Administration

To see your queue sizes and ages, use: `php stats.php`

Logs are in `/var/log/email2hook.log`

---

### Reasoning

I use MailGun and SendGrid for incoming email parsing and 80% of the monthly cost is towards incoming email. So it's a huge expense that creates unnecessary vendor lock-in.
This simple Postfix PHP daemon setup replaces all that for $20/month (a basic 4GB Digital Ocean droplet can easily handle many thousands of emails per day).
