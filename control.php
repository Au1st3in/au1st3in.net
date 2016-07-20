<!DOCTYPE html>
<html lang="en">
  <?php
    require("php/config.php");
    require("php/SteamAuth.php");
    if($auth->IsUserLoggedIn()){
    	if(in_array($steamprofile['steamid'],$whitelist)){
        include("php/GameQuery.php");
        include($actionPath . "/" . $actionPHP . ".php");
  ?>
        <head>
          <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
          <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no"/>
          <title>Au1st3in</title>
          <meta name="author" content="Austin Rocha">
          <link rel="shortcut icon" href="<?php echo $mainPath; ?>/img/steam.ico">
          <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
          <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,700">
          <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.6/css/materialize.min.css">
          <link href="<?php echo $mainPath; ?>/css/parallax.min.css" type="text/css" rel="stylesheet" media="screen,projection"/>
        </head>
        <body>
          <nav class="white" role="navigation">
            <div class="nav-wrapper container">
              <a href="#" class="brand-logo">au1st3in.net</a>
              <ul class="right hide-on-small-only">
                <li><a href="<?php echo $mainPath; ?>/">Home</a></li>
                <li class="active"><a href="#">Control Panel</a></li>
                  <li class="grey-text text-darken-4">
                    <div class="valign-wrapper">
                      &nbsp;&nbsp;&nbsp;<img src="<?php echo $steamprofile['avatarmedium']; ?>" class="circle valign" height="40px" width="40px">
                    </div>
                  </li>
                  <li class="grey-text text-darken-4">
                    <form method="POST">
                      &nbsp;&nbsp;<?php echo $steamprofile['personaname']; ?>&nbsp;&nbsp;&nbsp;&nbsp;
                      <input type="submit" name="logout" value="Logout"/>
                    </form>
                  </li>
              </ul>
            </div>
          </nav>
          <div class="parallax-container hide-on-small-only">
            <div class="section no-pad-bot">
              <div class="container">
                <p><br></p>
                <h3 class="header center white-text"><?php echo $compName; ?></h3>
                <div class="row center">
                  <p></p>
                  <?php
                    $ch = curl_init($actionPath . '/' . $actionPHP . '.php');
                    curl_setopt($ch, CURLOPT_NOBODY, true);
                    curl_exec($ch);
                    $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // $retcode >= 400 -> not found, $retcode = 200, found.
                    curl_close($ch);
                    if($retcode == 200){
                  ?>
                    <h5 class="header center white-text">Server Online</h5>
                    <p></p>
                    <a href="<?php echo $actionPath; ?>/<?php echo $actionPHP; ?>.php&<?php echo $actionQuery; ?>=<?php echo $rebootCase; ?>" class="btn waves-effect waves-light blue-grey lighten-1">Reboot Server</a>
                  <?php } else { ?>
                    <h5 class="header center white-text">Server Offline</h5>
                    <p></p>
                    <a class="btn waves-effect waves-light blue-grey lighten-1 disabled">Reboot Server</a>
                  <?php } ?>
                </div>
              </div>
            </div>
            <div class="parallax"><img src="img/dayz.png"></div>
          </div>
          <div class="parallax-container valign-wrapper hide-on-small-only">
        <?php foreach($results as $key => $server){
                if(!(isset($server['gq_mod']) && $server['gq_mod'] == "arma2arrowpc") && !(isset($server['gq_mod']) && $server['gq_mod'] == "dayz")){ ?>
                  <div class="section no-pad-bot">
                    <div class="container">
                      <?php if($server['gq_online']){ ?>
                        <script>
                          window.onload = function() {
                            Materialize.toast('Arma 2: OA Server is Online.', 5000);
                          };
                        </script>
                      <?php } ?>
                      <div class="section">
                        <div class="row">
                          <div class="col s12 m6 hide-on-med-and-down">
                            <div><br><br><br></div>
                            <div class="card hoverable center-align">
                              <div class="card-image">
                                <?php if($server['gq_online']){ ?>
                                  <?php if(strpos($server['game_descr'], 'DayZ') !== false){ ?>
                                    <img src="<?php echo $mainPath; ?>/img/dayzmod-server.png">
                                    <span class="card-title"><?php echo str_replace('Au1st3in.net - ', '', $server['gq_hostname']); ?></span>
                                  <?php } else { ?>
                                    <img src="<?php echo $mainPath; ?>/img/arma2oa-server.png">
                                    <span class="card-title"><?php echo str_replace('Au1st3in.net - ', '', $server['gq_hostname']); ?></span>
                                  <?php } ?>
                                <?php } else { ?>
                                  <img src="<?php echo $mainPath; ?>/img/arma2oa-server.png">
                                <?php } ?>
                              </div>
                            </div>
                          </div>
                          <div class="col s12 m6">
                            <div class="center">
                              <?php if($server['gq_online']){ ?>
                                <h4 class="header center"><br><?php echo $server['game_descr']; ?></h4>
                                <h5 class="header center light"><?php echo "Players: " . $server['gq_numplayers'] . "/" . $server['gq_maxplayers']; ?></h5><p></p>
                              <?php } else { ?>
                                <h4 class="header center"><br>Arma 2: OA Server</h4>
                                <h5 class="header center light"><i>Currently Offline</i></h5><p></p>
                              <?php } ?>
                                <p></p>
                            </div>
                            <div class="row center center-align">
                              <?php if($server['gq_online'] && $retcode == 200){ ?>
                                <a class="btn waves-effect waves-light blue-grey lighten-1 disabled"><i class="material-icons">play_arrow</i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <a href="<?php echo $actionPath; ?>/<?php echo $actionPHP; ?>.php&<?php echo $actionQuery; ?>=<?php echo $restartCase; ?>" class="btn waves-effect waves-light blue-grey lighten-1 tooltipped" data-position="bottom" data-delay="50" data-tooltip="Restart"><i class="material-icons">loop</i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <a href="<?php echo $actionPath; ?>/<?php echo $actionPHP; ?>.php&<?php echo $actionQuery; ?>=<?php echo $stopCase; ?>" class="btn waves-effect waves-light blue-grey lighten-1 tooltipped" data-position="bottom" data-delay="50" data-tooltip="Stop"><i class="material-icons">stop</i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <a href="<?php echo $actionPath; ?>/<?php echo $actionPHP; ?>.php&<?php echo $actionQuery; ?>=<?php echo $killCase; ?>" class="btn waves-effect waves-light blue-grey lighten-1 tooltipped" data-position="bottom" data-delay="50" data-tooltip="Kill Process"><i class="material-icons">not_interested</i></a>
                              <?php } elseif($retcode == 200) { ?>
                                <a href="<?php echo $actionPath; ?>/<?php echo $actionPHP; ?>.php&<?php echo $actionQuery; ?>=<?php echo $startCase; ?>" class="btn waves-effect waves-light blue-grey lighten-1 tooltipped" data-position="bottom" data-delay="50" data-tooltip="Start"><i class="material-icons">play_arrow</i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <a class="btn waves-effect waves-light blue-grey lighten-1 disabled"><i class="material-icons">loop</i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <a class="btn waves-effect waves-light blue-grey lighten-1 disabled"><i class="material-icons">stop</i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <a class="btn waves-effect waves-light blue-grey lighten-1 disabled"><i class="material-icons">not_interested</i></a>
                              <?php } else { ?>
                                <a class="btn waves-effect waves-light blue-grey lighten-1 disabled"><i class="material-icons">play_arrow</i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <a class="btn waves-effect waves-light blue-grey lighten-1 disabled"><i class="material-icons">loop</i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <a class="btn waves-effect waves-light blue-grey lighten-1 disabled"><i class="material-icons">stop</i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <a class="btn waves-effect waves-light blue-grey lighten-1 disabled"><i class="material-icons">not_interested</i></a>
                              <?php } ?>
                            </div>
                            <div class="row center">
                              <?php if($server['gq_online'] && (strpos($server['game_descr'], 'DayZ') !== false)){ ?>
                                <a href="<?php echo $dayzccPath; ?>" target="_blank" class="btn waves-effect waves-light blue-grey lighten-1"><i class="material-icons left">launch</i>Control Center</a>
                              <?php } ?>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <?php if(strpos($server['game_descr'], 'Exile') !== false){ ?>
                    <div class="parallax"><img src="<?php echo $mainPath; ?>/img/dayzmod.png" width="1920" height="1080"></div>
                  <?php } else { ?>
                    <div class="parallax"><img src="<?php echo $mainPath; ?>/img/arma2chernarus.png" width="1920" height="1080"></div>
                  <?php } ?>
          <?php }
              } ?>
          </div>
          <div class="parallax-container valign-wrapper hide-on-small-only">
        <?php foreach($results as $key => $server){
                if(!(isset($server['gq_mod']) && $server['gq_mod'] == "arma2arrowpc") && !(isset($server['gq_mod']) && $server['gq_mod'] == "dayz")){ ?>
                  <div class="section no-pad-bot">
                    <div class="container">
                      <?php if($server['gq_online']){ ?>
                        <script>
                          window.onload = function() {
                            Materialize.toast('Arma 3 Server is Online.', 5000);
                          };
                        </script>
                      <?php } ?>
                      <div class="section">
                        <div class="row">
                          <div class="col s12 m6 hide-on-med-and-down">
                            <div><br><br><br></div>
                            <div class="card hoverable center-align">
                              <div class="card-image">
                                <?php if($server['gq_online']){ ?>
                                  <?php if(strpos($server['game_descr'], 'Exile') !== false){ ?>
                                    <img src="<?php echo $mainPath; ?>/img/exilemod-server.png">
                                    <span class="card-title"><?php echo str_replace('Au1st3in.net - ', '', $server['gq_hostname']); ?></span>
                                  <?php } else { ?>
                                    <img src="<?php echo $mainPath; ?>/img/arma3-server.png">
                                    <span class="card-title"><?php echo str_replace('Au1st3in.net - ', '', $server['gq_hostname']); ?></span>
                                  <?php } ?>
                                <?php } else { ?>
                                  <img src="<?php echo $mainPath; ?>/img/arma3-server.png">
                                <?php } ?>
                              </div>
                            </div>
                          </div>
                          <div class="col s12 m6">
                            <div class="center">
                              <?php if($server['gq_online']){ ?>
                                <h4 class="header center"><br><?php echo $server['game_descr']; ?></h4>
                                <h5 class="header center light"><?php echo "Players: " . $server['gq_numplayers'] . "/" . $server['gq_maxplayers']; ?></h5><p></p>
                              <?php } else { ?>
                                <h4 class="header center"><br>Arma 3 Server</h4>
                                <h5 class="header center light"><i>Currently Offline</i></h5><p></p>
                              <?php } ?>
                                <p></p>
                            </div>
                            <div class="row center center-align">
                              <?php if($server['gq_online'] && $retcode == 200){ ?>
                                <a class="btn waves-effect waves-light blue-grey lighten-1 disabled"><i class="material-icons">play_arrow</i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <a href="<?php echo $actionPath; ?>/<?php echo $actionPHP; ?>.php&<?php echo $actionQuery; ?>=<?php echo $restartCase; ?>" class="btn waves-effect waves-light blue-grey lighten-1 tooltipped" data-position="bottom" data-delay="50" data-tooltip="Restart"><i class="material-icons">loop</i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <a href="<?php echo $actionPath; ?>/<?php echo $actionPHP; ?>.php&<?php echo $actionQuery; ?>=<?php echo $stopCase; ?>" class="btn waves-effect waves-light blue-grey lighten-1 tooltipped" data-position="bottom" data-delay="50" data-tooltip="Stop"><i class="material-icons">stop</i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <a href="<?php echo $actionPath; ?>/<?php echo $actionPHP; ?>.php&<?php echo $actionQuery; ?>=<?php echo $killCase; ?>" class="btn waves-effect waves-light blue-grey lighten-1 tooltipped" data-position="bottom" data-delay="50" data-tooltip="Kill Process"><i class="material-icons">not_interested</i></a>
                              <?php } elseif($retcode == 200) { ?>
                                <a href="<?php echo $actionPath; ?>/<?php echo $actionPHP; ?>.php&<?php echo $actionQuery; ?>=<?php echo $startCase; ?>" class="btn waves-effect waves-light blue-grey lighten-1 tooltipped" data-position="bottom" data-delay="50" data-tooltip="Start"><i class="material-icons">play_arrow</i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <a class="btn waves-effect waves-light blue-grey lighten-1 disabled"><i class="material-icons">loop</i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <a class="btn waves-effect waves-light blue-grey lighten-1 disabled"><i class="material-icons">stop</i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <a class="btn waves-effect waves-light blue-grey lighten-1 disabled"><i class="material-icons">not_interested</i></a>
                              <?php } else { ?>
                                <a class="btn waves-effect waves-light blue-grey lighten-1 disabled"><i class="material-icons">play_arrow</i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <a class="btn waves-effect waves-light blue-grey lighten-1 disabled"><i class="material-icons">loop</i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <a class="btn waves-effect waves-light blue-grey lighten-1 disabled"><i class="material-icons">stop</i></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <a class="btn waves-effect waves-light blue-grey lighten-1 disabled"><i class="material-icons">not_interested</i></a>
                              <?php } ?>
                      			</div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <?php if(strpos($server['game_descr'], 'Exile') !== false){ ?>
                    <div class="parallax"><img src="<?php echo $mainPath; ?>/img/exilemod.png" width="1920" height="1080"></div>
                  <?php } else { ?>
                    <div class="parallax"><img src="<?php echo $mainPath; ?>/img/arma3tanoa.png" width="1920" height="1080"></div>
                  <?php } ?>
          <?php }
              } ?>
          </div>
          <footer class="page-footer blue-grey">
            <div class="container">
              <div class="row">
                <div class="col l6 s12 hide-on-small-only">
                  <h5 class="white-text"><a class="white-text" href="https://www.bistudio.com/english/community/game-content-usage-rules">Content Usage Disclaimer</a></h5>
                  <p class="grey-text text-lighten-4">This website is not affiliated or authorized by Bohemia Interactive a.s. Bohemia Interactive, ARMA, DAYZ, and all associated logos and designs are trademarks or registered trademarks of Bohemia Interactive a.s.</p>
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
                <p class="grey-text text-lighten-4">
                  <a class="grey-text text-lighten-4" href="http://www.austinrocha.com/">&copy; <?php echo date("Y") ?> Austin Rocha, </a>
                  <a class="grey-text text-lighten-4" href="https://creativecommons.org/licenses/by-nc-sa/4.0/" target="_blank">Some Rights Reserved</a>
                  <a class="grey-text text-lighten-4 right" href="http://materializecss.com" target="_blank">Built with Materialize</a>
                </p>
              </div>
            </div>
            <script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.6/js/materialize.min.js"></script>
            <script src="<?php echo $mainPath; ?>/js/parallax.min.js"></script>
          </footer>
        </body>
  <?php
      } else {
        header('Location: ' . $mainPath);
      }
    } else {
      header('Location: ' . $mainPath);
    }
  ?>
</html>
