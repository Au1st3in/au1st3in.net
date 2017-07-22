import json, socket
import ts3.query as tsquery
from gameQuery.query import Query

with open('../data/config.json') as cfg:
    config = json.load(cfg)

def gameq(soc):
    ''' Game Server Queries '''

    ip, info, servers = socket.gethostbyname(config['dns']['main']), Query(), [key for key in config['servers'].keys()]
    query = {
        'pc' : {},
        'ts3': {'dns': config['dns']['ts3']},
        'online': []
        }
    for server in servers:
        try:
            query[server] = info.query(str(ip), config['servers'][server]['query'], config['servers'][server]['protocol'])
            if query[server]:
                query[server]['ip'], query[server]['port'], query[server]['protocol'] = str(ip), config['servers'][server]['port'], config['servers'][server]['protocol']
                if config['servers'][server]['dns']:
                    query[server]['dns'] = config['servers'][server]['dns']
                if config['servers'][server]['mods']:
                    query[server]['mods'] = config['servers'][server]['mods']
                query['online'].append(server)
        except:
            query[server] = None

    try:
        if tsquery.TS3Connection(str(socket.gethostbyname(config['dns']['main'])), config['ts3']['query']):
            query['online'].append('ts3')
    except:
        pass

    if soc:
        try:
            sock, host = socket.socket(socket.AF_INET, socket.SOCK_STREAM), config['pc']['hosts'][0].split(':')
            sock.settimeout(1)
            if sock.connect_ex((host[0], int(host[1]))) == 0:
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
