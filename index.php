<!DOCTYPE html>
<html lang="en">
<?php
  $serverIP = $_SERVER["REMOTE_ADDR"];
  $serverPort = "9987";
  $serverDNS = "ts.au1st3in.net";
  $serverDDNS = "www.au1st3in.net";
?>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no"/>
    <title>Au1st3in</title>
    <meta name="author" content="Austin Rocha">
    <meta name="description" content="Au1st3in's Teamspeak Server Community">
    <link rel="shortcut icon" href="steam.ico">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,700,300' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.5/css/materialize.min.css">
    <link href="css/parallax.min.css" type="text/css" rel="stylesheet" media="screen,projection"/>
  </head>
  <body>
    <nav class="white" role="navigation">
      <div class="nav-wrapper container">
        <a id="logo-container" href="#" class="brand-logo">au1st3in.net</a>
        <ul class="right hide-on-med-and-down">
          <li><a href="#">Teamspeak Server</a></li>
        </ul>
        <ul id="nav-mobile" class="side-nav">
          <li><a href="#">Teamspeak Server</a></li>
        </ul>
        <a href="#" data-activates="nav-mobile" class="button-collapse"><i class="material-icons">menu</i></a>
      </div>
    </nav>
    <div class="parallax-container valign-wrapper">
      <div class="container">
        <div class="row center">
          <h5 class="header col s12 light">Authentic, diverse, open - Arma 3 sends you to war.</h5>
        </div>
      </div>
      <div class="parallax"><img src="img/arma3.png" width="1920" height="1200"></div>
    </div>
    <div class="container">
      <div class="section">
        <div class="row">
          <div class="col s12 m6">
            <div class="icon-block">
              <h2 class="center blue-grey-text"><i class="material-icons">settings_voice</i></h2>
              <h5 class="center">High Quality Voice Communication</h5>
              <p class="light">Our teamspeak is home of a small gaming community surrounded around games such as Arma 3, DayZ, Counter-Strike, Garry's Mod, H1Z1, and Rocket League. The server utilizes a mix of Speex Ultra Wideband and Opus Voice codec for the best audio quality, in addition to a gigabit LAN connection.</p>
              <p><br></p>
            </div>
            <div class="row center">
              <a href="ts3server://<?php echo $serverDNS; ?>?port=<?php echo $serverPort; ?>" id="download-button" class="btn-large waves-effect waves-light blue-grey lighten-1">Enter Lobby</a>
            </div>
          </div>
          <div class="col s12 m6">
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
    <div class="parallax-container valign-wrapper">
      <div class="section no-pad-bot">
        <div class="container">
          <div class="row center">
            <h5 class="header col s12 light">This is DayZ, this is your story.</h5>
          </div>
        </div>
      </div>
      <div class="parallax"><img src="img/dayz.png" width="1920" height="1080"></div>
    </div>
    <footer class="page-footer blue-grey">
      <div class="container">
        <div class="row">
          <div class="col l6 s12">
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
          &copy; <?php echo date("Y") ?> <a class="grey-text text-lighten-4" href="http://www.austinrocha.com/">Austin Rocha</a>
          <p class="grey-text text-lighten-4 right">Built with <a class="grey-text text-lighten-4" href="http://materializecss.com" target="_blank">Materialize</a></p>
        </div>
      </div>
      <script src="https://code.jquery.com/jquery-2.2.0.min.js"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.5/js/materialize.min.js"></script>
      <script src="js/parallax.min.js"></script>
    </footer>
  </body>
</html>
