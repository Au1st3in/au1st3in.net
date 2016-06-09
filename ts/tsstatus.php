<?php
/**
 * TSStatus: Teamspeak 3 viewer for php5
 * @author Sebastien Gerard <seb@sebastien.me>
 * @see http://tsstatus.sebastien.me/
 * @version 2013-08-31
 **/

class TSStatus
{
	private $_host;
	private $_queryPort;
	private $_serverDatas;
	private $_channelDatas;
	private $_userDatas;
	private $_serverGroupFlags;
	private $_channelGroupFlags;
	private $_login;
	private $_password;
	private $_cacheFile;
	private $_cacheTime;
	private $_channelList;
	private $_useCommand;
	private $_javascriptName;
	private $_socket;

	public $imagePath;
	public $showNicknameBox;
	public $timeout;
	public $hideEmptyChannels;
	public $hideParentChannels;

	public function TSStatus($host, $queryPort)
	{
		$this->_host = $host;
		$this->_queryPort = $queryPort;

		$this->_socket = null;
		$this->_serverDatas = array();
		$this->_channelDatas = array();
		$this->_userDatas = array();
		$this->_serverGroupFlags = array();
		$this->_channelGroupFlags = array();
		$this->_login = false;
		$this->_password = false;
		$this->_cacheTime = 0;
		$this->_cacheFile = __FILE__ . ".cache";
		$this->_channelList = array();
		$this->_useCommand = "use port=9987";

		$this->imagePath = "img/";
		$this->showNicknameBox = true;
		$this->showPasswordBox = false;
		$this->timeout = 2;
		$this->hideEmptyChannels = false;
		$this->hideParentChannels = false;
	}

	public function useServerId($serverId)
	{
		$this->_useCommand = "use sid=$serverId";
	}

	public function useServerPort($serverPort)
	{
		$this->_useCommand = "use port=$serverPort";
	}

	public function setLoginPassword($login, $password)
	{
		$this->_login = $login;
		$this->_password = $password;
	}

	public function setCache($time, $file = false)
	{
		$this->_cacheTime = $time;
		if($file !== false) $this->_cacheFile = $file;
	}

	public function clearServerGroupFlags()
	{
		$this->_serverGroupFlags = array();
	}

	public function setServerGroupFlag($serverGroupId, $image)
	{
		$this->_serverGroupFlags[$serverGroupId] = $image;
	}

	public function clearChannelGroupFlags()
	{
		$this->_channelGroupFlags = array();
	}

	public function setChannelGroupFlag($channelGroupId, $image)
	{
		$this->_channelGroupFlags[$channelGroupId] = $image;
	}

	public function limitToChannels()
	{
		$this->_channelList = func_get_args();
	}

	private function ts3decode($str, $reverse = false)
	{
		$find = array('\\\\', 	"\/", 		"\s", 		"\p", 		"\a", 	"\b", 	"\f", 		"\n", 		"\r", 	"\t", 	"\v");
		$rplc = array(chr(92),	chr(47),	chr(32),	chr(124),	chr(7),	chr(8),	chr(12),	chr(10),	chr(3),	chr(9),	chr(11));

		if(!$reverse) return str_replace($find, $rplc, $str);
		return str_replace($rplc, $find, $str);
	}

	private function toHTML($string)
	{
		return htmlentities($string, ENT_QUOTES, "UTF-8");
	}

	private function sortUsers($a, $b)
	{
		if($a["client_talk_power"] != $b["client_talk_power"]) return $a["client_talk_power"] > $b["client_talk_power"] ? -1 : 1;
		return strcasecmp($a["client_nickname"], $b["client_nickname"]);
	}

	private function parseLine($rawLine)
	{
		$datas = array();
		$rawItems = explode("|", $rawLine);
		foreach ($rawItems as $rawItem)
		{
			$rawDatas = explode(" ", $rawItem);
			$tempDatas = array();
			foreach($rawDatas as $rawData)
			{
				$ar = explode("=", $rawData, 2);
				$tempDatas[$ar[0]] = isset($ar[1]) ? $this->ts3decode($ar[1]) : "";
			}
			$datas[] = $tempDatas;
		}
		return $datas;
	}

	private function sendCommand($cmd)
	{
		fputs($this->_socket, "$cmd\n");
		$response = "";
		do
		{
			$response .= fread($this->_socket, 8096);
		}while(strpos($response, 'error id=') === false);
		if(strpos($response, "error id=0") === false)
		{
			throw new Exception("TS3 Server returned the following error: " . $this->ts3decode(trim($response)));
		}
		return $response;
	}

	private function queryServer()
	{
		$this->_socket = @fsockopen($this->_host, $this->_queryPort, $errno, $errstr, $this->timeout);
		if($this->_socket)
		{
			@socket_set_timeout($this->_socket, $this->timeout);
			$isTs3 = trim(fgets($this->_socket)) == "TS3";
			if(!$isTs3) throw new Exception("Not a Teamspeak 3 server/bad query port");

			if($this->_login !== false)
			{
				$this->sendCommand("login client_login_name=" . $this->_login . " client_login_password=" . $this->_password);
			}

			$response = "";
			$response .= $this->sendCommand($this->_useCommand);
			$response .= $this->sendCommand("serverinfo");
			$response .= $this->sendCommand("channellist -topic -flags -voice -limits");
			$response .= $this->sendCommand("clientlist -uid -away -voice -groups");
			$response .= $this->sendCommand("servergrouplist");
			$response .= $this->sendCommand("channelgrouplist");

			$this->disconnect();
			return $response;
		}
		else throw new Exception("Socket error: $errstr [$errno]");
	}

	private function disconnect()
	{
		@fputs($this->_socket, "quit\n");
		@fclose($this->_socket);
	}

	private function update()
	{
		$response = $this->queryServer();
		$lines = explode("error id=0 msg=ok\n\r", $response);
		if(count($lines) == 7)
		{
			$this->_serverDatas = $this->parseLine($lines[1]);
			$this->_serverDatas = $this->_serverDatas[0];

			$tmpChannels = $this->parseLine($lines[2]);
			$hide = count($this->_channelList) > 0 || $this->hideEmptyChannels;
			foreach ($tmpChannels as $channel)
			{
				$channel["show"] = !$hide;
				$this->_channelDatas[$channel["cid"]] = $channel;
			}

			$tmpUsers = $this->parseLine($lines[3]);
			usort($tmpUsers, array($this, "sortUsers"));
			foreach ($tmpUsers as $user)
			{
				if($user["client_type"] == 0)
				{
					if(!isset($this->_userDatas[$user["cid"]])) $this->_userDatas[$user["cid"]] = array();
					$this->_userDatas[$user["cid"]][] = $user;
				}
			}

			$serverGroups = $this->parseLine($lines[4]);
			foreach ($serverGroups as $sg) if($sg["iconid"] > 0) $this->setServerGroupFlag($sg["sgid"], 'group_' . $sg["iconid"]);

			$channelGroups = $this->parseLine($lines[5]);
			foreach ($channelGroups as $cg) if($cg["iconid"] > 0) $this->setChannelGroupFlag($cg["cgid"], 'group_' . $cg["iconid"]);
		}
		else throw new Exception("Invalid server response");
	}

	private function setShowFlag($channelIds)
	{
		if(!is_array($channelIds)) $channelIds = array($channelIds);
		foreach ($channelIds as $cid)
		{
			if(isset($this->_channelDatas[$cid]))
			{
				$this->_channelDatas[$cid]["show"] = true;
				if(!$this->hideParentChannels && $this->_channelDatas[$cid]["pid"] != 0)
				{
					$this->setShowFlag($this->_channelDatas[$cid]["pid"]);
				}
			}
		}
	}

	private function getCache()
	{
		if($this->_cacheTime > 0 && file_exists($this->_cacheFile) && (filemtime($this->_cacheFile) + $this->_cacheTime >= time()) )
		{
			return file_get_contents($this->_cacheFile);
		}
		return false;
	}

	private function saveCache($content)
	{
		if($this->_cacheTime > 0)
		{
			if(!@file_put_contents($this->_cacheFile, $content))
			{
				throw new Exception("Unable to write to file: " . $this->_cacheFile);
			}
		}
	}

	private function renderFlags($flags)
	{
		$content = "";
		foreach ($flags as $flag) $content .= '<i class="sprite sprite-' . $flag . '"></i>';
		return $content;
	}

	private function renderOptionBox($name, $label)
	{
		$key = "tsstatus-" . $this->_javascriptName . "-$name";
		$value = isset($_COOKIE[$key]) ? htmlspecialchars($_COOKIE[$key]) : "";
		return '<label>' . $label . ': <input type="text" id="' . $key . '" value="' . $value . '" /></label>';
	}

	private function renderUsers($channelId)
	{
		$content = "";
		if(isset($this->_userDatas[$channelId]))
		{
			$imagePath = $this->imagePath;
			foreach ($this->_userDatas[$channelId] as $user)
			{
				if($user["client_type"] == 0)
				{
					$name = $this->toHTML($user["client_nickname"]);

					$icon = "16x16_player_off";
					if($user["client_away"] == 1) $icon = "16x16_away";
					else if($user["client_flag_talking"] == 1) $icon = "16x16_player_on";
					else if($user["client_output_hardware"] == 0) $icon = "16x16_hardware_output_muted";
					else if($user["client_output_muted"] == 1) $icon = "16x16_output_muted";
					else if($user["client_input_hardware"] == 0) $icon = "16x16_hardware_input_muted";
					else if($user["client_input_muted"] == 1) $icon = "16x16_input_muted";

					$flags = array();

					if(isset($this->_channelGroupFlags[$user["client_channel_group_id"]]))
					{
						$flags[] = $this->_channelGroupFlags[$user["client_channel_group_id"]];
					}

					$serverGroups = explode(",", $user["client_servergroups"]);
					foreach ($serverGroups as $serverGroup)
					{
						if(isset($this->_serverGroupFlags[$serverGroup]))
						{
							$flags[] = $this->_serverGroupFlags[$serverGroup];
						}
					}
					$flags = $this->renderFlags($flags);

					$content .= <<<HTML
<div class="tsstatusItem">
	<i class="sprite sprite-$icon"></i>$name
	<div class="tsstatusFlags">
		$flags
	</div>
</div>
HTML;
				}
			}
		}
		return $content;
	}

	private function renderChannels($channelId)
	{
		$content = "";
		$imagePath = $this->imagePath;
		foreach ($this->_channelDatas as $channel)
		{
			if($channel["pid"] == $channelId)
			{
				if($channel["show"])
				{
					$name = $this->toHTML($channel["channel_name"]);
					$title = $name  . " [" . $channel["cid"] . "]";
					$link = "";
					/**
					$link = "javascript:tsstatusconnect('" . $this->_javascriptName . "'," . $channel["cid"] . ")";
					**/

					$icon = "16x16_channel_green";
					if( $channel["channel_maxclients"] > -1 && ($channel["total_clients"] >= $channel["channel_maxclients"])) $icon = "16x16_channel_red";
					else if( $channel["channel_maxfamilyclients"] > -1 && ($channel["total_clients_family"] >= $channel["channel_maxfamilyclients"])) $icon = "16x16_channel_red";
					else if($channel["channel_flag_password"] == 1) $icon = "16x16_channel_yellow";

					$flags = array();
					if($channel["channel_flag_default"] == 1) $flags[] = '16x16_default';
					if($channel["channel_needed_talk_power"] > 0) $flags[] = '16x16_moderated';
					if($channel["channel_flag_password"] == 1) $flags[] = '16x16_register';
					$flags = $this->renderFlags($flags);

					$users = $this->renderUsers($channel["cid"]);
					$childs = $this->renderChannels($channel["cid"]);

					$cid = $channel["cid"];

					$content .= <<<HTML
<div class="tsstatusItem">
	<a href="$link" title="$title">
		<i class="sprite sprite-$icon"></i>$name
		<div class="tsstatusFlags">
			$flags
		</div>
		$users
	</a>
	$childs
</div>
HTML;
				}
				else $content .= $this->renderChannels($channel["cid"]);
			}
		}
		return $content;
	}

	public function render()
	{
		try
		{
			$cache = $this->getCache();
			if($cache != false) return $cache;

			$this->update();

			if($this->hideEmptyChannels && count($this->_channelList) > 0) $this->setShowFlag(array_intersect($this->_channelList, array_keys($this->_userDatas)));
			else if($this->hideEmptyChannels) $this->setShowFlag(array_keys($this->_userDatas));
			else if(count($this->_channelList) > 0) $this->setShowFlag($this->_channelList);


			$host = $this->_host;
			$port = $this->_serverDatas["virtualserver_port"];
			$name = $this->toHTML($this->_serverDatas["virtualserver_name"]);
			$icon = "16x16_server_green";
			$this->_javascriptName = $javascriptName = preg_replace("#[^a-z-A-Z0-9]#", "-", $host . "-" . $port);

			$options = "";
			if ($this->showNicknameBox) $options .= $this->renderOptionBox("nickname", "Nickname");
			if($this->showPasswordBox && isset($this->_serverDatas["virtualserver_flag_password"]) && $this->_serverDatas["virtualserver_flag_password"] == 1) $options .= $this->renderOptionBox("password", "Password");

			$channels = $this->renderChannels(0);

			$content = <<<HTML
<div class="tsstatus">
	<input type="hidden" id="tsstatus-$javascriptName-hostport" value="$host:$port" />
	$options
	<div class="tsstatusItem tsstatusServer">
		<i class="sprite sprite-$icon"></i>$name
		$channels
	</div>
</div>
HTML;
			$this->saveCache($content);
		}
		catch (Exception $ex)
		{
			$this->disconnect();
			$content = '<div class="tsstatusError">' . $ex->getMessage() . '</div>';
		}

		return $content;
	}

}
?>
