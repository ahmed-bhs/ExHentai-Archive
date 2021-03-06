#!/usr/bin/env sh
if [ ! -f "/etc/periodic/15min/archive.sh" ]; then
  cp /var/www/html/.docker/config/app/cron/15min/archive.sh /etc/periodic/15min/archive.sh
  chmod +x /etc/periodic/15min/archive.sh
  (crontab -l ; echo "*/10 * * * * /etc/periodic/15min/archive.sh > /proc/1/fd/1 2>/proc/1/fd/2") | sort - | uniq - | crontab -
fi

if [ ! -f "/etc/periodic/hourly/audit.sh" ]; then
  cp /var/www/html/.docker/config/app/cron/hourly/audit.sh /etc/periodic/hourly/audit.sh
  chmod +x /etc/periodic/hourly/audit.sh
  (crontab -l ; echo "0 4 * * * /etc/periodic/hourly/audit.sh > /proc/1/fd/1 2>/proc/1/fd/2") | sort - | uniq - | crontab -
fi

if [ ! -f "/etc/periodic/hourly/thumbnails.sh" ]; then
  cp /var/www/html/.docker/config/app/cron/hourly/thumbnails.sh /etc/periodic/hourly/thumbnails.sh
  chmod +x /etc/periodic/hourly/thumbnails.sh
  (crontab -l ; echo "0 5 * * * /etc/periodic/hourly/thumbnails.sh > /proc/1/fd/1 2>/proc/1/fd/2") | sort - | uniq - | crontab -
fi

if [ ! -f "/etc/periodic/hourly/daily-bonus.sh" ]; then
  cp /var/www/html/.docker/config/app/cron/hourly/daily-bonus.sh /etc/periodic/hourly/daily-bonus.sh
  chmod +x /etc/periodic/hourly/daily-bonus.sh
  (crontab -l ; echo "0 5 * * * /etc/periodic/hourly/daily-bonus.sh > /proc/1/fd/1 2>/proc/1/fd/2") | sort - | uniq - | crontab -
fi

crond
