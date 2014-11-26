#!/usr/bin/python

"""
Get Start date (in python date object), and number of days.
the script needs to return on demand, the next day to scrape.

"""

import datetime
import random

_dates_list = []

#init the list with the dateTime object
def init_randomizer(start_date, numberOfDay):
	for x in xrange(numberOfDay):
		_dates_list.append((start_date + datetime.timedelta(days=x)))

def get_date_from_list():
	index = random.randint(0, len(_dates_list) - 1)
	cur_date = _dates_list[index]
	del(_dates_list[index])
	return cur_date

def is_empty():
	if _dates_list:
		return False
	else:
		return True

