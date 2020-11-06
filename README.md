# email2hook

Easily set up an Ubuntu server as a mailsink that receives inbound email and POSTs it to webhooks.

### Reasoning

I use MailGun or SendGrid for incoming email parsing and 80% of the monthly cost is towards incoming email. So it's a huge expense that creates vendor lock-in. This simple Postfix PHP daemon setup replaces all that for $20/month (a basic 4GB Digital Ocean droplet can easily handle thousands of emails per day).

## Architecture

 - Postfix accepts email and save it to /home/$USERNAME/mail
 - PHP daemons pick up email from /home/$USERNAME/mail and POSTs to your webhooks
 - After posting, email is deleted, and on failed, is re-tried with exponential back-off
 - Postfix virtual config files stored in ``config/vdomains` and ``config/vmailbox`

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

1. edit ``config/config.php`
2. reload with ``bash reload.sh`

