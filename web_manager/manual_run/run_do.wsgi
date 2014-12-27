#!/usr/bin/python
# -*- coding: UTF-8 -*-

import os
import subprocess

back_btn = """
<BR>

<script>
function goBack() {
    window.history.back()
}
</script>
<button onclick="goBack()">Go Back</button>
"""
def application (env, r):
    post_vars = dict((x,y) for x,y in (a.split('=') for a in env['wsgi.input'].read().split('&')))
    mon = post_vars['month']
    cmp_list = post_vars['cmp_list']
    run_cmd = os.path.expanduser('~ec2-user/wizz/scripts/run_sm_instance.py')
    subprocess.Popen(["nohup", run_cmd, mon, cmp_list])
    body = "Running {0} {1} {2} {3}".format(run_cmd, mon, cmp_list, back_btn)
    status = '200 OK'
    response_headers = [ ('Content-Type', 'text/html'), ('Content-Length', str (len (body) ) ) ]
    r (status, response_headers)
    return [body]


