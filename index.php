<!DOCTYPE html>
<html lang="en">
  <?php
    $serverIP = $_SERVER["REMOTE_ADDR"];
    $serverPort = "9987";
    $serverDNS = "ts.au1st3in.net";
    $serverDDNS = "www.au1st3in.net";

    include("php/SteamAuth.php");
    $auth = new SteamAuth();

    // You can use this to do other checks on the person, such as making an account in a database
    $auth->SetOnLoginCallback(function($steamid){
    	return true; // returning true will log them in, false will stop the login (you should put an error message in that case)
    });

    // This handler is for when a login fails Ex: canceled, auth failed, exploit attempt, etc
    $auth->SetOnLoginFailedCallback(function(){
    	return false;
    });

    // You can use this to do other checks on the person, such as making an modifying a database
    $auth->SetOnLogoutCallback(function($steamid){
    	return true;
    });

    // Always call Init() on pages you want to check a login from.  Call this AFTER you set handlers!
    $auth->Init();

    // Where we handle the POST logout from the form below
    if(isset($_POST['logout'])){
    	$auth->Logout(); // The logout function also refreshes the page
    }
  ?>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no"/>
    <title>Au1st3in</title>
    <meta name="author" content="Austin Rocha">
    <link rel="shortcut icon" href="img/steam.ico">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,700">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.6/css/materialize.min.css">
    <link href="css/parallax.min.css" type="text/css" rel="stylesheet" media="screen,projection"/>
  </head>
  <body>
    <nav class="white" role="navigation">
      <div class="nav-wrapper container">
        <a id="logo-container" href="#" class="brand-logo">au1st3in.net</a>
        <ul class="right hide-on-small-only">
          <li class="active"><a href="#">TeamSpeak</a></li>
          <?php if($auth->IsUserLoggedIn()){ ?>
            <li class="grey-text text-darken-4">
              <div class="valign-wrapper">
                &nbsp;&nbsp;&nbsp;<img src="<?php echo $steamprofile['avatarmedium']; ?>" class="circle valign" height="40px" width="40px">
              </div>
            </li>
            <li class="grey-text text-darken-4">
              <form method="POST">
                &nbsp;<?php echo $steamprofile['personaname']; ?>&nbsp;&nbsp;&nbsp;
                <input type="submit" name="logout" value="Logout"/>
              </form>
            </li>
          <?php }else{ ?>
            <li><a href="<?php echo $auth->GetLoginURL(); ?>" class="waves-effect waves-light btn light-green darken-2">Steam Login</a></li>
          <?php } ?>
        </ul>
        <ul id="nav-mobile" class="side-nav">
          <li class="active"><a href="#">TeamSpeak</a></li>

        </ul>
        <a href="#" data-activates="nav-mobile" class="button-collapse"><i class="material-icons">menu</i></a>
      </div>
    </nav>
    <div class="parallax-container valign-wrapper hide-on-small-only">
      <div class="parallax"><img src="img/arma3.png" width="1920" height="1080"></div>
    </div>
    <div class="container">
      <div class="section">
        <div class="row">
          <div class="col s12 m6">
            <div class="icon-block">
              <h2 class="center blue-grey-text"><i class="material-icons">settings_voice</i></h2>
              <h5 class="center">High Quality Voice Communication</h5>
              <p class="light">Our teamspeak is home of a small gaming community surrounded around games such as Arma 3, DayZ, Counter-Strike, Garry's Mod, H1Z1, and Rocket League. The server utilizes a mix of Speex Ultra Wideband and Opus Voice codec for the best audio quality, in addition to a nearly gigabit LAN connection.</p>
              <p><br></p>
            </div>
            <div class="row center">
              <a href="ts3server://<?php echo $serverDNS; ?>?port=<?php echo $serverPort; ?>" id="download-button" class="btn-large waves-effect waves-light blue-grey lighten-1">Enter Lobby</a>
            </div>
          </div>
          <div class="col s12 m6 hide-on-med-and-down">
            <div class="card large hoverable">
              <div id="IframeWrapper" class="featurette-image img-responsive" style="position: relative;">
                <iframe id="iframewebpage" style="z-index:1"  runat="server" src="http://<?php echo $serverDDNS; ?>/ts/client.php" width="500" height="500" marginheight="0" frameborder="0"></iframe>
                <div id="iframeBlocker" style="position:absolute; top: 0; left: 0; width:95%; height:95%;background-color:aliceblue;opacity:0.1;"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="parallax-container valign-wrapper hide-on-small-only">
      <div class="parallax"><img src="img/dayz.png" width="1920" height="1080"></div>
    </div>
    <footer class="page-footer blue-grey">
      <div class="container">
        <div class="row">
          <div class="col l6 s12 hide-on-small-only">
            <h5 class="white-text">Content Usage Disclaimer</h5>
            <p class="grey-text text-lighten-4">This website is not affiliated or authorized by <a class="grey-text text-lighten-4" href="https://www.bistudio.com/english/community/game-content-usage-rules">Bohemia Interactive</a> a.s. Bohemia Interactive, ARMA, DAYZ and all associated logos and designs are trademarks or registered trademarks of Bohemia Interactive a.s.</p>
          </div>
          <div class="col l4 offset-l2 s12">
            <h5 class="white-text">GitHub</h5>
            <ul>
              <script async defer id="github-bjs" src="https://buttons.github.io/buttons.js"></script>
              <li><a class="github-button" href="https://github.com/au1st3in" data-style="mega" data-count-href="/au1st3in/followers" data-count-api="/users/au1st3in#followers" data-count-aria-label="# followers on GitHub" aria-label="Follow @au1st3in on GitHub">Follow @au1st3in</a></li>
              <li><a class="github-button" href="https://github.com/au1st3in/au1st3in.net" data-icon="octicon-star" data-style="mega" data-count-href="/au1st3in/au1st3in.net/stargazers" data-count-api="/repos/au1st3in/au1st3in.net#stargazers_count" data-count-aria-label="# stargazers on GitHub" aria-label="Star au1st3in/au1st3in.net on GitHub">Star</a></li>
            </ul>
          </div>
        </div>
      </div>
      <div class="footer-copyright blue-grey darken-1">
        <div class="container grey-text text-lighten-4">
          <p class="grey-text text-lighten-4"><a class="grey-text text-lighten-4" href="http://www.austinrocha.com/">&copy; <?php echo date("Y") ?> Austin Rocha</a><a class="grey-text text-lighten-4 right" href="http://materializecss.com" target="_blank">Built with Materialize</a></p>
        </div>
      </div>
      <script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.6/js/materialize.min.js"></script>
      <script src="js/parallax.min.js"></script>
    </footer>
  </body>
</html>
