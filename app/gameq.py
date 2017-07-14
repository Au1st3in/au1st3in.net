import json, socket
from gameQuery.query import Query

with open('../data/config.json') as cfg:
    config = json.load(cfg)

def gameq(soc):
    ''' Game Server Queries '''

    ip, info = socket.gethostbyname(config['dns']['main']), Query()
    try:
        query = {
            'arma2oa': info.query(str(ip), config['arma2oa']['query'], 'valve'),
            'arma3': info.query(str(ip), config['arma3']['query'], 'valve'),
            'dayz': info.query(str(ip), config['dayz']['query'], 'valve'),
            'minecraft': info.query(str(ip), config['minecraft']['port'], 'gamespy4'),
            'ts3': {'dns': config['dns']['ts3']},
            'online': []
        }
    except:
        query = {'arma2oa': None, 'arma3': None, 'dayz': None, 'minecraft': None, 'ts3': {'dns': config['dns']['ts3']}, 'online': []}

    for server in query:
        if not server == 'online':
            if query[server]:
                query[server]['ip'], query[server]['port'] = str(ip), config[server]['port']
                if server == 'minecraft':
                    query[server]['dns'] = config['dns']['minecraft']
                if server == 'ts3':
                    query['online'].append(server)
    if soc:
        try:
            sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            sock.settimeout(1)
            host = config['server']['hosts'][0].split(':')
            if sock.connect_ex((host[0], int(host[1]))) == 0:
                query['pc'] = {}
                query['pc']['ip'] = host[0]
            else:
                query['pc'] = None
        except:
            query['pc'] = None
    else:
        query['pc'] = None

    if not query['online']:
        query['online'].append(None)

    return query
