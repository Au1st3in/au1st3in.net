''' Flask Imports '''
from flask import Flask, g, json, redirect, render_template, request, send_from_directory, session

''' SQLAlchemy Import '''
from flask_sqlalchemy import get_debug_queries, SQLAlchemy

''' OpenID Imports '''
from flask_openid import OpenID
from os.path import dirname, join

''' Miscellaneous Imports '''
import datetime, errno, hashlib, json, logging, random, re, signal, socket, ssl, sys, urllib
from datetime import date
from random import shuffle

''' GameQ Function Import '''
from gameq import gameq

''' Py-TS3 Imports '''
from tsstatus import tsviewer
import ts3.query as tsquery
import ts3.response
from ts3.common import TS3Error
from ts3.examples.viewer import ChannelTreeNode
from socket import error as socket_error

''' Fabric3 Imports and Setup '''
import fabfile
from fabric import state
from fabric.api import execute
from fabric.main import load_fabfile
docstring, callables, default = load_fabfile('fabfile.py')
state.commands.update(callables)

''' Config.json Import '''
with open('../data/config.json') as cfg:
    config = json.load(cfg)

''' Initialize Flask App, Database, and OpenID '''
app = Flask(__name__)

#from OpenSSL import SSL
#context = SSL.Context(SSL.TLSv1_2_METHOD)
#context.use_privatekey_file('../cert/privkey.pem')
#context.use_certificate_chain_file('../cert/fullchain.pem')
#context.use_certificate_file('../cert/cert.pem')

app.config.update(
    SQLALCHEMY_DATABASE_URI = 'sqlite:///../data/'+config['sqlite']['db'],
    SQLALCHEMY_TRACK_MODIFICATIONS = False,
    SECRET_KEY = config['steam-api']['key'],
    DEBUG = config['flask']['debug'],
    HOST = config['flask']['host'],
    PORT = config['flask']['port']
    #SSL_CONTEXT = context
)

''' Database and Open ID Instantiation '''
db = SQLAlchemy(app)
oid = OpenID(app, join(dirname(__file__), 'openid_store'))

''' Flask Debug Toolbar Import '''
if app.debug:
    from termcolor import colored
    from flask_debugtoolbar import DebugToolbarExtension
    ssl._create_default_https_context = ssl._create_unverified_context
    debug_toolbar = DebugToolbarExtension()
    debug_toolbar.init_app(app)
#else:
#    from flask_sslify import SSLify
#    sslify = SSLify(app, subdomains=True)

class User(db.Model):
    ''' User Database Model '''
    id, steam_id, guid, personaname, avatar = db.Column(db.Integer, primary_key=True), db.Column(db.String(40)), db.Column(db.String(40)), db.Column(db.String(80)), db.Column(db.String(180))
    @staticmethod
    def get_or_put(steam_id):
        user = User.query.filter_by(steam_id=steam_id).first()
        if user is None:
            user = User()
            user.steam_id = steam_id
            db.session.add(user)
        return user

class Whitelist(db.Model):
    ''' Whitelist Database Model '''
    steam_id = db.Column(db.String(40), primary_key=True)
    @staticmethod
    def get(steam_id):
        if Whitelist.query.filter_by(steam_id=steam_id).first():
            return True
        return False
    @staticmethod
    def put(steam_id):
        whitelist = Whitelist.query.filter_by(steam_id=steam_id).first()
        if whitelist is None:
            whitelist = Whitelist()
            whitelist.steam_id = steam_id
            db.session.add(whitelist)
            db.session.commit()
        return None
    @staticmethod
    def remove(steam_id):
        whitelist = Whitelist.query.filter_by(steam_id=steam_id).first()
        if whitelist and not steam_id == config['steam-api']['steamID64']:
            db.session.delete(whitelist)
            db.session.commit()
        return None
    @staticmethod
    def fetch():
        users = []
        for user in Whitelist.query:
            users.append(user.steam_id)
        return users

def get_steamfriends(steam_id, communityvisibilitystate):
    ''' Retrieves a list of steamfriends from a given public steam_id '''
    steam_ids = []
    if communityvisibilitystate == 3:
        options = {'key': app.secret_key, 'steamid': steam_id, 'relationship': 'friend'}
        url = 'http://api.steampowered.com/ISteamUser/GetFriendList/v0001/?%s' % urllib.parse.urlencode(options)
        response = urllib.request.urlopen(url)
        str_response = response.read().decode('utf-8')
        rv = json.loads(str_response)
        for friend in rv['friendslist']['friends']:
            steam_ids.append(friend['steamid'])
    return steam_ids

def get_steamdata(steam_id):
    ''' Retrieves steamdata[] from a given steam_id '''
    options = {'key': app.secret_key, 'steamids': steam_id}
    url = 'http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0001/?%s' % urllib.parse.urlencode(options)
    response = urllib.request.urlopen(url)
    str_response = response.read().decode('utf-8')
    rv = json.loads(str_response)
    return rv['response']['players']['player'][0] or {}

def steam_64id_to_guid(steam_id):
    ''' Converts a 64bit Steam_ID to a BattlEye GUID '''
    lst = [0x42, 0x45, 0, 0, 0, 0, 0, 0, 0, 0]
    for i in range(2,10):
        lst[i] = steam_id & 0xFF
        steam_id >>= 8
    return hashlib.md5(bytearray(lst)).hexdigest()

''' Logging Setup '''
FORMAT = '%(asctime)-15s %(levelname)s %(message)s'
logging.basicConfig(filename='../data/'+config['flask']['logfile'], level=config['flask']['level'], format=FORMAT)

def parseLog(logfile):
    ''' Extracts important Log information (no older than a week) into a list of dictionaries '''
    currentDict, lastWeek = {}, (datetime.datetime.now()-datetime.timedelta(days=7)).date()
    for line in logfile:
        line = line.split(' ')
        if 'LOG' in line and datetime.date(int(line[0].split('-')[0]), int(line[0].split('-')[1]), int(line[0].split('-')[2])) > lastWeek:
            if currentDict:
                yield currentDict
            currentDict = {
                'date': str(line[0]),
                'time': str(line[1].split(",")[0]),
                'steam_id': str(line[5].replace('(', '').replace(')', '')),
                'action': str(line[6]),
                'server': str(line[7].rstrip())
            }
    yield currentDict

@app.route('/login')
@oid.loginhandler
def login():
    ''' OpenID Steam Login Handler '''
    if g.user is not None:
        return redirect(oid.get_next_url())
    return oid.try_login('http://steamcommunity.com/openid')

@oid.after_login
def create_or_login(resp):
    ''' Commits steamdata to database and returns redirect url '''
    _steam_id_re = re.compile('steamcommunity.com/openid/id/(.*?)$')
    match = _steam_id_re.search(resp.identity_url)
    g.user = User.get_or_put(match.group(1))
    steamdata = get_steamdata(g.user.steam_id)
    g.user.guid, g.user.personaname, g.user.avatar = steam_64id_to_guid(int(g.user.steam_id)), steamdata['personaname'], steamdata['avatarfull']
    db.session.commit()
    session['user_id'] = g.user.id
    logging.info('LOG '+str(g.user.personaname)+' ('+str(g.user.steam_id)+') LOGGED-IN AU1ST3IN.NET')
    return redirect(oid.get_next_url())

@app.before_request
def before_request():
    ''' Checks for existing User Session '''
    g.user = None
    if 'user_id' in session:
        g.user = User.query.get(session['user_id'])

@app.route('/logout')
def logout():
    ''' OpenID Steam Logout Handler '''
    logging.info('LOG '+str(g.user.personaname)+' ('+str(g.user.steam_id)+') SIGNED-OUT AU1ST3IN.NET')
    session.pop('user_id', None)
    return redirect(oid.get_next_url())

@app.route('/')
def home():
    ''' Main Site Page '''
    query = gameq(False)
    if g.user:
        '''rand=random.choice(query['online'])'''
        return render_template('index.html', year=date.today().year, version=(config['version']['materialize'], config['version']['jquery'], config['version']['font-awesome']), servers=query, user=g.user, whitelisted=Whitelist.get(g.user.steam_id))
    return render_template('index.html', year=date.today().year, version=(config['version']['materialize'], config['version']['jquery'], config['version']['font-awesome']), servers=query)

@app.route('/ts')
def ts():
    ''' ts Subdomain Redirect Page '''
    return render_template('ts.html', servers=gameq(False))

@app.route('/tsstatus')
def tsstatus():
    ''' TeamSpeak Status Page '''
    try:
        with tsquery.TS3Connection(str(socket.gethostbyname(config['dns']['main'])), config['ts3']['query']) as ts3conn:
            ts3conn.login(client_login_name=config['ts3']['username'], client_login_password=config['ts3']['password'])
            virtualserver = tsviewer(ChannelTreeNode.build_tree(ts3conn, 1))
    except:
        virtualserver = False
    return render_template('tsstatus.html', virtualserver=virtualserver, version=(config['version']['materialize'], config['version']['jquery'], config['version']['font-awesome']))

@app.route('/admin')
def admin():
    ''' Admin Panel Page '''
    if g.user:
        auth = Whitelist.get(g.user.steam_id)
    if g.user and auth:
        return render_template('admin.html', year=date.today().year, version=(config['version']['materialize'], config['version']['jquery'], config['version']['font-awesome']), servers=gameq(True), user=g.user, whitelisted=auth)
    return redirect('/')

@app.route('/admin/<state>')
def command(state):
    ''' Admin Server Control Commands '''
    if g.user:
        auth = Whitelist.get(g.user.steam_id)
    if g.user and auth:
        try:
            if state == 'restart' and request.args.get('server', type=str) == '' and not request.args.get('ts', None, type=str):
                print('1')
                execute('server')
                logging.info('LOG '+str(g.user.personaname)+' ('+str(g.user.steam_id)+') REBOOT WINDOWS-SERVER')
                execute('reboot')
            elif state == 'restart' and request.args.get('ts', type=str) == '' and not request.args.get('server', None, type=str):
                print('2')
                execute('nas')
                logging.info('LOG '+str(g.user.personaname)+' ('+str(g.user.steam_id)+') RESTART TS3-SERVER')
                execute('control', 'restart', 'ts3')
            elif state in {'start', 'restart'} and request.args.get('server', None, type=str):
                print('3')
                logging.info('LOG '+str(g.user.personaname)+' ('+str(g.user.steam_id)+') '+str(state).upper()+' '+request.args.get('server', type=str).upper()+'-'+request.args.get('mod', 'default', type=str).upper())
                execute('server')
                execute('control', str(state), request.args.get('server', '', type=str), request.args.get('mod', 'default', type=str))
            elif request.args.get('server', None, type=str):
                print('4')
                execute('server')
                logging.info('LOG '+str(g.user.personaname)+' ('+str(g.user.steam_id)+') '+str(state).upper()+' '+request.args.get('server', type=str).upper()+'-SERVER')
                execute('control', str(state), request.args.get('server', '', type=str))
        except:
            pass
        return redirect('/admin')
    return redirect('/')

@app.route('/settings', methods=['POST', 'GET'])
def settings():
    ''' User Settings Page '''
    if g.user:
        auth = Whitelist.get(g.user.steam_id)

    if not Whitelist.get(config['steam-api']['steamID64']):
        Whitelist.put(config['steam-api']['steamID64'])

    if g.user and auth:
        try:
            with open('../data/'+config['flask']['logfile']) as file:
                logs = list(parseLog(file))
        except:
            logs = [{}]

        steam_ids = Whitelist.fetch()
        steam_ids.remove(config['steam-api']['steamID64'])

        whitelist = [dict() for i in range(len(steam_ids))]
        for i in range(len(whitelist)):
            steamdata = get_steamdata(steam_ids[i])
            whitelist[i]['steam_id']=steam_ids[i]
            whitelist[i]['personaname']=steamdata['personaname']
            whitelist[i]['avatar']=steamdata['avatar']
        shuffle(whitelist)

        return render_template('settings.html', year=date.today().year, version=(config['version']['materialize'], config['version']['jquery'], config['version']['font-awesome']), user=g.user, whitelisted=auth, whitelist=whitelist, log=logs, admin=config['steam-api']['steamID64'])
    return redirect('/')

@app.route('/whitelist')
def whitelist():
    ''' Whitelist Edit Commands '''
    if g.user:
        auth = Whitelist.get(g.user.steam_id)
    if g.user and auth:
        try:
            if len(request.args) == 1:
                if request.args.get('add', None, type=str) and len(request.args.get('add', '', type=str)) == 17 and request.args.get('add', '', type=str).isdigit():
                    Whitelist.put(request.args.get('add', type=str))
                elif request.args.get('remove', None, type=str) and len(request.args.get('remove', '', type=str)) == 17 and request.args.get('remove', '', type=str).isdigit() and not request.args.get('remove', '', type=str) in {config['steam-api']['steamID64'], g.user.steam_id}:
                    Whitelist.remove(request.args.get('remove', type=str))
        except:
            pass
        return redirect('/settings')
    return redirect('/')

@app.route('/CNAME')
@app.route('/robots.txt')
@app.route('/sitemap.xml')
def static_from_root():
    ''' Static Site Content Redirect '''
    return send_from_directory(app.static_folder, request.path[1:])

@app.errorhandler(404)
def page_not_found(e):
    ''' Page Not Found Redirect '''
    return render_template('404.html'), 404

if __name__ == '__main__':
    if sys.version_info < (int(config['version']['python'][0]), int(config['version']['python'][2])):
        raise RuntimeError('Wrong Python Version.')
    app.run()
