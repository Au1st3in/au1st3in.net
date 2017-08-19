import ts3, ts3.commands
#from ts3.commands import clientlist, privilegekeyadd, privilegekeydelete, privilegekeylist

__all__ = ["ChannelTreeNode", "tsviewer", "clientlist", "gen_privilegekey", "rem_privilegekey", "get_privilegekeys"]

class ChannelTreeNode(object):
    """
    Represents a channel or the virtual server in the channel tree of a virtual
    server. Note, that this is a recursive data structure.

    Common
    ------

    self.childs = List with the child *Channels*.

    self.root = The *Channel* object, that is the root of the whole channel
                tree.

    Channel
    -------

    Represents a real channel.

    self.info =  Dictionary with all informations about the channel obtained by
                 ts3conn.channelinfo

    self.parent = The parent channel, represented by another *Channel* object.

    self.clients = List with dictionaries, that contains informations about the
                   clients in this channel.

    Root Channel
    ------------

    Represents the virtual server itself.

    self.info = Dictionary with all informations about the virtual server
                obtained by ts3conn.serverinfo

    self.parent = None

    self.clients = None

    Usage
    -----

    >>> tree = ChannelTreeNode.build_tree(ts3conn, sid=1)

    """

    def __init__(self, info, parent, root, clients=None):
        """
        Inits a new channel node.

        If root is None, root is set to *self*.
        """
        self.info = info
        self.childs = list()

        # Init a root channel
        if root is None:
            self.parent = None
            self.clients = None
            self.root = self

        # Init a real channel
        else:
            self.parent = parent
            self.root = root
            self.clients = clients if clients is not None else list()
        return None

    @classmethod
    def init_root(cls, info):
        """
        Creates a the root node of a channel tree.
        """
        return cls(info, None, None, None)

    def is_root(self):
        """
        Returns true, if this node is the root of a channel tree (the virtual
        server).
        """
        return self.parent is None

    def is_channel(self):
        """
        Returns true, if this node represents a real channel.
        """
        return self.parent is not None

    @classmethod
    def build_tree(cls, ts3conn, sid):
        """
        Returns the channel tree from the virtual server identified with
        *sid*, using the *TS3Connection* ts3conn.
        """
        ts3conn.use(sid=sid, virtual=True)

        resp = ts3conn.serverinfo()
        serverinfo = resp.parsed[0]

        resp = ts3conn.channellist()
        channellist = resp.parsed

        resp = ts3conn.clientlist()
        clientlist = resp.parsed
        # channel id -> clients
        clientlist = {cid: [client for client in clientlist \
                            if client["cid"] == cid]
                      for cid in map(lambda e: e["cid"], channellist)}

        root = cls.init_root(serverinfo)
        for channel in channellist:
            resp = ts3conn.channelinfo(cid=channel["cid"])
            channelinfo = resp.parsed[0]
            # This makes sure, that *cid* is in the dictionary.
            channelinfo.update(channel)

            channel = cls(
                info=channelinfo, parent=root, root=root,
                clients=clientlist[channel["cid"]])
            root.insert(channel)
        return root

    def insert(self, channel):
        """
        Inserts the channel in the tree.
        """
        self.root._insert(channel)
        return None

    def _insert(self, channel):
        """
        Inserts the channel recursivly in the channel tree.
        Returns true, if the tree has been inserted.
        """
        # We assumed on previous insertions, that a channel is a direct child
        # of the root, if we could not find the parent. Correct this, if ctree
        # is the parent from one of these orpheans.
        if self.is_root():
            i = 0
            while i < len(self.childs):
                child = self.childs[i]
                if channel.info["cid"] == child.info["pid"]:
                    channel.childs.append(child)
                    self.childs.pop(i)
                else:
                    i += 1

        # This is not the root and the channel is a direct child of this one.
        elif channel.info["pid"] == self.info["cid"]:
            self.childs.append(channel)
            return True

        # Try to insert the channel recursive.
        for child in self.childs:
            if child._insert(channel):
                return True

        # If we could not find a parent in the whole tree, assume, that the
        # channel is a child of the root.
        if self.is_root():
            self.childs.append(channel)
        return False

    def dict(self):
        """
        Returns a nested dictionary of arrays
        {"Server0": {"Channel0" : [TALK_POWER?, PERMANENT?, PASSWORD?, {"SubChannel0" : [TALK_POWER?, PERMANENT?, PASSWORD?, {}, (Client2, Client3)]} , (Client0, Client1)]}}
        """
        virtualserver = {}
        if self.is_root():
            virtualserver[self.info["virtualserver_name"]] = {}
        def helper(self):
            dictionary, clients = {}, []
            if not self:
                return dictionary
            for channel in self.childs:
                children = False
                for subchannel in channel.childs:
                    for client in subchannel.clients:
                        if not client["client_type"] == "1":
                            children = True
                if not "spacer" in channel.info["channel_name"] and (channel.clients or children):
                    for client in channel.clients:
                        if not client["client_type"] == "1":
                            clients.append(client["client_nickname"])
                            children = True
                    if children:
                        clients.sort()
                        dictionary[channel.info["channel_name"]] = [bool(int(channel.info["channel_needed_talk_power"]) > 0), bool(int(channel.info["channel_flag_permanent"])), bool(int(channel.info["channel_flag_password"])), helper(channel), tuple(clients)]
                        clients = []
            return dictionary
        virtualserver[self.info["virtualserver_name"]] = helper(self)
        return virtualserver

def tsviewer(ts3conn, sid=1):
    """
    Returns channel dictionary of the virtual server, including all clients.
    """
    tree = ChannelTreeNode.build_tree(ts3conn, sid)
    return tree.dict()

def clientdict(ts3conn):
    """

    """
    clients = {}
    for client in ts3conn.clientlist(voice=True, groups=True):
        if not int(client["client_type"]) == 1:
            clients[client["client_nickname"]] = {}
            #clients[client["client_nickname"]]["client_dbid"] = int(client["clid"])
            clients[client['client_nickname']]['client_flag_talking'] = bool(int(client['client_flag_talking']))
            clients[client['client_nickname']]['client_input_muted'] = bool(int(client['client_input_muted'])) or not bool(int(client['client_input_hardware']))
            clients[client['client_nickname']]['client_output_muted'] = bool(int(client['client_output_muted'])) or not bool(int(client['client_output_hardware']))
            clients[client["client_nickname"]]["client_servergroups"] = tuple(map(int, client["client_servergroups"].split(",")))
    return clients

def gen_privilegekey(ts3conn, sgid, steam_id):
    """

    """
    key = ts3conn.privilegekeyadd(tokentype=0, tokenid1=sgid, tokenid2=0, tokendescription=steam_id, tokencustomset="ident=forum_user\svalue=Token\pident=forum_id\svalue=76561198026915793")
    return key[0]['token']

def rem_privilegekey(ts3conn, token):
    """

    """
    ts3conn.privilegekeydelete(token=token)
    return token

def get_privilegekeys(ts3conn, sgid=None, steam_id=None):
    """

    """
    privilegekeys = []
    try:
        for token in ts3conn.privilegekeylist():
            if not bool(int(token['token_type'])) and not bool(int(token['token_id2'])):
                privilegekeys.append((int(token['token_id1']), token['token']))
    except:
        if sgid and steam_id:
            privilegekeys.append((int(sgid), gen_privilegekey(ts3conn, sgid, steam_id+' - Default')))
    return privilegekeys
