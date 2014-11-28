#!/usr/bin/python

import smtplib
import boto, boto.ec2
import datetime

"""
import argparse

parser = argparse.ArgumentParser(description='Run scrapers machine and run it.')
parser.add_argument('month', type=int, help='The month number (starting with 0)')
parser.add_argument('machine', type=int, help='The machine number (starting with 1, max 3)')
args = parser.parse_args()
"""

REGION = boto.utils.get_instance_metadata()['local-hostname'].split('.')[1]
EC2 = boto.ec2.connect_to_region(REGION)
AMI = 'ami-41ffaa71'
tag = "type"
value = "scraper"

reservations = EC2.get_all_reservations(filters={'tag:{0}'.format(tag) : value})
running_ids = [x.id for y in reservations for x in y.instances if x.state == "running"]
if running_ids:
 stoped = EC2.terminate_instances(instance_ids=running_ids)
 stoped_ids = [x.id for x in stoped]
 error_msg = ""
 if sorted(running_ids) != sorted(stoped_ids):
  error_msg = "Not all of the machines terminated. Terminated:{0}".format(str(stoped_ids))
 
 sender = "StuckMachines <StuckMachines@2fly.cheap>"
 recs = ['2flycheap@kazav.net']
 subject = "Stuck Machines - {0}".format(datetime.datetime.strftime(datetime.datetime.now(), "%Y-%m-%d %H:%M"))
 mail_msg = """Subject: {0}
 We had some stuck machines: {1}. killed them. 
 {2}
 """.format(subject, str(running_ids), error_msg)
 smtpObj = smtplib.SMTP('localhost')
 a = smtpObj.sendmail(sender, recs, mail_msg) 
