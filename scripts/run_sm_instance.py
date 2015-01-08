#!/usr/bin/python

import boto, boto.ec2
import boto.ec2.networkinterface
import time
import os, sys
import argparse

parser = argparse.ArgumentParser(description='Run scrapers machine and run it.')
parser.add_argument('month', type=int, help='The month number (starting with 0)')
parser.add_argument('machine', type=int, help='The machine number (starting with 1, max 3)')
args = parser.parse_args()

REGION = boto.utils.get_instance_metadata()['local-hostname'].split('.')[1]
EC2 = boto.ec2.connect_to_region(REGION)
#AMI = 'ami-41ffaa71' #=0.6
AMI = 'ami-8b0f53bb' #=0.7
SUBNET = 'subnet-6846a431'
SGROUP = 'sg-6d4c2208'

interface = boto.ec2.networkinterface.NetworkInterfaceSpecification(subnet_id=SUBNET, groups=[SGROUP], associate_public_ip_address=True)
interfaces = boto.ec2.networkinterface.NetworkInterfaceCollection(interface)
spot = EC2.request_spot_instances('0.45', AMI, count=1, key_name='koko', instance_type='m3.medium', network_interfaces=interfaces)

time.sleep(15)
cur_spot_id = spot[0].id
instance_id = EC2.get_all_spot_instance_requests(request_ids=cur_spot_id)[0].instance_id
while not instance_id:
	time.sleep(10)
	instance_id = EC2.get_all_spot_instance_requests(request_ids=cur_spot_id)[0].instance_id

instance = EC2.get_all_instances(instance_ids=instance_id)
time.sleep(5)
if args.month < 99 and args.machine < 99:
	instance[0].instances[0].add_tag("type","scraper")
	instance[0].instances[0].add_tag("month",args.month)
	instance[0].instances[0].add_tag("machine",args.machine)
else:
	instance[0].instances[0].add_tag("type","test_scraper")

instance_ip = instance[0].instances[0].private_ip_address
#public_ip = instance[0].instances[0].ip_address

test_code = "echo ok"
remote_code = "retries=0 ; cd  ; rm -rf wizz ; git clone --quiet git@github.com:gabik/wizzscrape.git wizz ; while [[ $? -ne 0 && $retries -lt 10 ]] ; do sleep 15 ; retries=$((retries+1)) ; rm -rf wizz ; git clone --quiet git@github.com:gabik/wizzscrape.git wizz ; done ; cd ~/wizz/scripts ; ./boot_run.sh {0} {1} &> /tmp/boot_run.log".format(str(args.machine), str(args.month))
retries = 0
ssh_code = os.system('ssh -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no -i ' + str(instance_ip) + ' "'+test_code+'" > /dev/null 2>&1')
while ssh_code != 0:
	retries += 1
	time.sleep(10)
	ssh_code = os.system('ssh -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no ' + str(instance_ip) + ' "'+test_code+'" > /dev/null 2>&1')
	if retries > 35:
		print "ERROR : SSH timeout to " + instance_id
		sys.exit(1)

if args.month < 99 and args.machine < 99:
	ssh_code = os.system('ssh -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no ' + str(instance_ip) + " '"+remote_code+"'")
#else:
	#str_file = '{0} - {1}'.format(str(datetime.datetime.now()), public_ip)
	#file_name = '/tmp/cur_tmp_server'
	#fd = open(file_name, 'w')
	#fd.write(str_file)
	#fd.close()
#
#print "Done."
