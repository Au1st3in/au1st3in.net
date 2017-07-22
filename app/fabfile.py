from fabric.api import run, env, settings, sudo, task
from gameq import gameq

import json
with open('../data/config.json') as cfg:
    config = json.load(cfg)

STAGES = {'pc': config['pc'], 'nas': config['nas']}

def stage_set(stage_name='pc'):
    env.stage = stage_name
    for option, value in STAGES[env.stage].items():
        setattr(env, option, value)

@task
def pc():
    stage_set('pc')

@task
def nas():
    stage_set('nas')

@task
def control(state, server, mod=None):
    if (str(state)+str(server)+str(mod)).isalnum():
        if state == 'restart' and server == 'ts3' and not mod:
            sudo("cd /usr/syno/sbin && synoservicecfg --restart pkgctl-ts3server", shell=True, pty=False)
        else:
            if mod:
                run("python control.py "+str(state)+" "+str(server)+" "+str(mod), shell=False, pty=False)
            else:
                run("python control.py "+str(state)+" "+str(server), shell=False, pty=False)

@task
def reboot(state=None):
    query = gameq(False)
    for server in query['online']:
        try:
            if not server in {'ts3', None}:
                run("python control.py stop "+str(server), shell=False, pty=False)
        except:
            pass
    run("shutdown -r -f", shell=False, pty=False)
