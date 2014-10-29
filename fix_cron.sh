#!/bin/bash
sudo chown root cron 
sudo chmod 640 cron
sudo service crond restart
