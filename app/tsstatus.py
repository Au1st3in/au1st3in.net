from profanity import profanity

def dictify(tree):
    '''                                       Returns a nested dictionary of arrays from a given ChannelTreeNode object
      {'Server0': {'Channel0' : [TALK_POWER?, PERMANENT?, PASSWORD?, {'SubChannel0' : [TALK_POWER?, PERMANENT?, PASSWORD?, {}, (Client2, Client3)]} , (Client0, Client1)]}} '''
    virtualserver = {}
    if tree.is_root():
        server_name = profanity.censor(tree.info['virtualserver_name'])
        virtualserver[server_name] = {}
    def dictify_helper(tree):
        dictionary, clients = {}, []
        if not tree:
            return dictionary
        for channel in tree.childs:
            for client in channel.clients:
                if client['client_type'] == "1":
                    continue
                clients.append(profanity.censor(client['client_nickname']))
            dictionary[profanity.censor(channel.info['channel_name'])] = [bool(int(channel.info['channel_needed_talk_power']) > 0), bool(int(channel.info['channel_flag_permanent'])), bool(int(channel.info['channel_flag_password'])), dictify_helper(channel), tuple(clients)]
            clients = []
        return dictionary
    virtualserver[server_name] = dictify_helper(tree)
    return virtualserver

def minify(virtualserver):
    '''                                   Returns only Channels/SubChannels with Clients from a given VirtualServer Dictionary                                   '''
    TALK_POWER, PERMANENT, PASSWORD, SUBCHANNELS, CLIENTS = range(0,5)
    new_dict ={}
    def minify_helper(server):
        dictionary = {}
        for channel in server:
            if not server[channel][SUBCHANNELS] and server[channel][CLIENTS]:
                dictionary[channel] = [server[channel][TALK_POWER], server[channel][PERMANENT], server[channel][PASSWORD], {}, server[channel][CLIENTS]]
            elif server[channel][SUBCHANNELS] and server[channel][CLIENTS]:
                dictionary[channel] = [server[channel][TALK_POWER], server[channel][PERMANENT], server[channel][PASSWORD], minify_helper(server[channel][SUBCHANNELS]), server[channel][CLIENTS]]
            elif server[channel][SUBCHANNELS]:
                for subchannel in server[channel][SUBCHANNELS]:
                    if server[channel][SUBCHANNELS][subchannel][CLIENTS]:
                        dictionary[channel] = [server[channel][TALK_POWER], server[channel][PERMANENT], server[channel][PASSWORD], minify_helper(server[channel][SUBCHANNELS]), server[channel][CLIENTS]]
        return dictionary
    for server in virtualserver:
        new_dict[server] = {}
        new_dict[server] = minify_helper(virtualserver[server])
    return new_dict

def tsviewer(tree):
    return minify(dictify(tree))
