<?php

/*
 * BossAnnouncement
 * A plugin by XenialDan aka thebigsmileXD
 * http://github.com/thebigsmileXD/BossAnnouncement
 * A simple boss bar tile plugin using my BossBarAPI
 */
namespace xenialdan\BossAnnouncement;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerJoinEvent;
use xenialdan\BossBarAPI\API;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\Player;

class Main extends PluginBase implements Listener{
	public $eid = null, $headBar = '', $cmessages = [], $changeSpeed = 0, $i = 0;

	public function onEnable(){
		if(($API = $this->getServer()->getPluginManager()->getPlugin("BossBarAPI")) !== null){}
		else{
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}
		$this->saveDefaultConfig();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->headBar = $this->getConfig()->get('head-message', '');
		$this->cmessages = $this->getConfig()->get('changing-messages', []);
		$this->changeSpeed = $this->getConfig()->get('change-speed', 0);
		if($this->changeSpeed > 0) $this->getServer()->getScheduler()->scheduleRepeatingTask(new SendTask($this), 20 * $this->changeSpeed);
	}

	public function onJoin(PlayerJoinEvent $ev){
		if($this->eid === null){
			$this->eid = API::addBossBar([$ev->getPlayer()], 'Loading..');
			$this->getServer()->getLogger()->debug($this->eid === NULL?'Couldn\'t add BossAnnouncement':'Successfully added BossAnnouncement with EID: ' . $this->eid);
		}
		else{
			API::sendBossBarToPlayer($ev->getPlayer(), $this->eid, $this->getText($ev->getPlayer()));
			$this->getServer()->getLogger()->debug('Sendt BossAnnouncement with existing EID: ' . $this->eid);
		}
	}

	public function sendBossBar(){
		if($this->eid === null) return;
		$this->i++;
		foreach($this->getServer()->getDefaultLevel()->getPlayers() as $player){
			API::setTitle($this->getText($player), $this->eid);
		}
	}

	public function onPlayerMove(PlayerMoveEvent $event){
		if($this->eid === null) return;
		$pk = API::playerMove($event->getTo(), $this->eid);
		$this->getServer()->broadcastPacket($event->getPlayer()->getLevel()->getPlayers(), $pk);
		unset($event);
	}

	/**
	 * Generates the output
	 *
	 * @param Player $player 
	 */
	public function getText(Player $player){
		$text = '';
		if(!empty($this->headBar)) $text .= $this->formatText($player, $this->headBar) . PHP_EOL . PHP_EOL . TextFormat::RESET;
		$currentMSG = $this->cmessages[$this->i % count($this->cmessages)];
		// @preg_match_all("/(\{.*?\})/ig", $currentMSG, $maybepercentage);
		// print_r($maybepercentage);
		// preg_match_all('/(\{(\d+)%\})/i', $maybepercentage[0], $percentages);
		// print_r($percentages);
		if(strpos($currentMSG, '%') > -1){
			$percentage = substr($currentMSG, 1, strpos($currentMSG, '%') - 1);
			if(is_numeric($percentage)) API::setPercentage(intval($percentage) + 0.5, $this->eid);
			$currentMSG = substr($currentMSG, strpos($currentMSG, '%') + 2);
		}
		$text .= $this->formatText($player, $currentMSG);
		return $text;
	}

	public function formatText(Player $player, $text){
		$text = str_replace("{display_name}", $player->getDisplayName(), $text);
		$text = str_replace("{name}", $player->getName(), $text);
		$text = str_replace("{world}", (($levelname = $player->getLevel()->getName()) === false?"":$levelname), $text);
		$text = str_replace("{level_players}", count($player->getLevel()->getPlayers()), $text);
		$text = str_replace("{server_players}", count($player->getServer()->getOnlinePlayers()), $text);
		$text = str_replace("{server_max_players}", $player->getServer()->getMaxPlayers(), $text);
		$text = str_replace("{hour}", date('H'), $text);
		$text = str_replace("{minute}", date('i'), $text);
		$text = str_replace("{second}", date('s'), $text);
		// preg_match_all ("/(\{.*?\})/ig", $text, $brackets);
		
		$text = str_replace("{BLACK}", "&0", $text);
		$text = str_replace("{DARK_BLUE}", "&1", $text);
		$text = str_replace("{DARK_GREEN}", "&2", $text);
		$text = str_replace("{DARK_AQUA}", "&3", $text);
		$text = str_replace("{DARK_RED}", "&4", $text);
		$text = str_replace("{DARK_PURPLE}", "&5", $text);
		$text = str_replace("{GOLD}", "&6", $text);
		$text = str_replace("{GRAY}", "&7", $text);
		$text = str_replace("{DARK_GRAY}", "&8", $text);
		$text = str_replace("{BLUE}", "&9", $text);
		$text = str_replace("{GREEN}", "&a", $text);
		$text = str_replace("{AQUA}", "&b", $text);
		$text = str_replace("{RED}", "&c", $text);
		$text = str_replace("{LIGHT_PURPLE}", "&d", $text);
		$text = str_replace("{YELLOW}", "&e", $text);
		$text = str_replace("{WHITE}", "&f", $text);
		$text = str_replace("{OBFUSCATED}", "&k", $text);
		$text = str_replace("{BOLD}", "&l", $text);
		$text = str_replace("{STRIKETHROUGH}", "&m", $text);
		$text = str_replace("{UNDERLINE}", "&n", $text);
		$text = str_replace("{ITALIC}", "&o", $text);
		$text = str_replace("{RESET}", "&r", $text);
		
		$text = str_replace("&0", TextFormat::BLACK, $text);
		$text = str_replace("&1", TextFormat::DARK_BLUE, $text);
		$text = str_replace("&2", TextFormat::DARK_GREEN, $text);
		$text = str_replace("&3", TextFormat::DARK_AQUA, $text);
		$text = str_replace("&4", TextFormat::DARK_RED, $text);
		$text = str_replace("&5", TextFormat::DARK_PURPLE, $text);
		$text = str_replace("&6", TextFormat::GOLD, $text);
		$text = str_replace("&7", TextFormat::GRAY, $text);
		$text = str_replace("&8", TextFormat::DARK_GRAY, $text);
		$text = str_replace("&9", TextFormat::BLUE, $text);
		$text = str_replace("&a", TextFormat::GREEN, $text);
		$text = str_replace("&b", TextFormat::AQUA, $text);
		$text = str_replace("&c", TextFormat::RED, $text);
		$text = str_replace("&d", TextFormat::LIGHT_PURPLE, $text);
		$text = str_replace("&e", TextFormat::YELLOW, $text);
		$text = str_replace("&f", TextFormat::WHITE, $text);
		$text = str_replace("&k", TextFormat::OBFUSCATED, $text);
		$text = str_replace("&l", TextFormat::BOLD, $text);
		$text = str_replace("&m", TextFormat::STRIKETHROUGH, $text);
		$text = str_replace("&n", TextFormat::UNDERLINE, $text);
		$text = str_replace("&o", TextFormat::ITALIC, $text);
		$text = str_replace("&r", TextFormat::RESET, $text);
		
		return $text;
	}
}
