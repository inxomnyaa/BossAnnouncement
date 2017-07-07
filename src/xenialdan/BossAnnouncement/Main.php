<?php

/*
 * BossAnnouncement
 * A plugin by XenialDan aka thebigsmileXD
 * http://github.com/thebigsmileXD/BossAnnouncement
 * A simple boss bar tile plugin using my BossBarAPI
 */
namespace xenialdan\BossAnnouncement;

use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use xenialdan\BossBarAPI\API;

class Main extends PluginBase implements Listener {
	public $entityRuntimeId = null, $headBar = '', $cmessages = [], $changeSpeed = 0, $i = 0;
	/** @var API $API */
	public $API;
	private $plugins = [];

	public function onEnable() {
		if (($this->API = $this->getServer()->getPluginManager()->getPlugin("BossBarAPI")) === null) {
			$this->getServer()->getPluginManager()->disablePlugin($this);
			return;
		}
		$this->saveDefaultConfig();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->headBar = $this->getConfig()->get('head-message', '');
		$this->cmessages = $this->getConfig()->get('changing-messages', []);
		$this->changeSpeed = $this->getConfig()->get('change-speed', 0);
		if ($this->changeSpeed > 0) $this->getServer()->getScheduler()->scheduleRepeatingTask(new SendTask($this), 20 * $this->changeSpeed);
	}

	public function onJoin(PlayerJoinEvent $ev) {
		if (in_array($ev->getPlayer()->getLevel(), $this->getWorlds())) {
			if ($this->entityRuntimeId === null) {
				$this->entityRuntimeId = API::addBossBar([$ev->getPlayer()], 'Loading..');
				$this->getServer()->getLogger()->debug($this->entityRuntimeId === NULL ? 'Couldn\'t add BossAnnouncement' : 'Successfully added BossAnnouncement with EID: ' . $this->entityRuntimeId);
			} else {
				API::sendBossBarToPlayer($ev->getPlayer(), $this->entityRuntimeId, $this->getText($ev->getPlayer()));
				$this->getServer()->getLogger()->debug('Sendt BossAnnouncement with existing EID: ' . $this->entityRuntimeId);
			}
		}
	}
	//////
	//fix mode 2

	public function onLevelChange(EntityLevelChangeEvent $ev) {
		if ($ev->isCancelled() || !$ev->getEntity() instanceof Player) return;
		if (in_array($ev->getTarget(), $this->getWorlds())) {
			if ($this->entityRuntimeId === null) {
				$this->entityRuntimeId = API::addBossBar([$ev->getEntity()], 'Loading..');
				$this->getServer()->getLogger()->debug($this->entityRuntimeId === NULL ? 'Couldn\'t add BossAnnouncement' : 'Successfully added BossAnnouncement with EID: ' . $this->entityRuntimeId);
			} else {
				API::removeBossBar([$ev->getEntity()], $this->entityRuntimeId);
				API::sendBossBarToPlayer($ev->getEntity(), $this->entityRuntimeId, $this->getText($ev->getEntity()));
				$this->getServer()->getLogger()->debug('Sendt BossAnnouncement with existing EID: ' . $this->entityRuntimeId);
			}
		} else {
			API::removeBossBar([$ev->getEntity()], $this->entityRuntimeId);
		}
	}


	public function sendBossBar() {
		if ($this->entityRuntimeId === null) return;
		$this->i++;
		$worlds = $this->getWorlds();
		foreach ($worlds as $world) {
			foreach ($world->getPlayers() as $player) {
				API::setTitle($this->getText($player), $this->entityRuntimeId, [$player]);
			}
		}
	}

	/**
	 * Generates the output
	 *
	 * @param Player $player
	 * @return string
	 */
	public function getText(Player $player) {
		$text = '';
		if (!empty($this->headBar)) $text .= $this->formatText($player, $this->headBar) . "\n" . "\n" . TextFormat::RESET;
		$currentMSG = $this->cmessages[$this->i % count($this->cmessages)];
		if (strpos($currentMSG, '%') > -1) {
			$percentage = substr($currentMSG, 1, strpos($currentMSG, '%') - 1);
			if (is_numeric($percentage)) API::setPercentage(intval($percentage) + 0.5, $this->entityRuntimeId);
			$currentMSG = substr($currentMSG, strpos($currentMSG, '%') + 2);
		}
		$text .= $this->formatText($player, $currentMSG);
		return mb_convert_encoding($text, 'UTF-8');
	}

	public function formatText(Player $player, $text) {
		$name = $player->getName();
		$server = $this->getServer();
		$text = str_replace("{display_name}", $player->getDisplayName(), $text);
		$text = str_replace("{name}", $player->getName(), $text);
		$text = str_replace("{x}", $player->getFloorX(), $text);
		$text = str_replace("{y}", $player->getFloorY(), $text);
		$text = str_replace("{z}", $player->getFloorZ(), $text);
		$text = str_replace("{world}", (($levelname = $player->getLevel()->getName()) === false ? "" : $levelname), $text);
		$text = str_replace("{level_players}", count($player->getLevel()->getPlayers()), $text);
		$text = str_replace("{server_players}", count($player->getServer()->getOnlinePlayers()), $text);
		$text = str_replace("{server_max_players}", $player->getServer()->getMaxPlayers(), $text);
		$text = str_replace("{hour}", date('H'), $text);
		$text = str_replace("{minute}", date('i'), $text);
		$text = str_replace("{second}", date('s'), $text);
		
////////////////////////////////////////////////////Added by CortexPE///////////////////////////////////////////////////////////////////
		$text = str_replace("{kills}", $server->getPluginManager()->getPlugin('KillChat')->getKills($name), $text);
		$text = str_replace("{deaths}", $server->getPluginManager()->getPlugin('KillChat')->getDeaths($name), $text);
		$text = str_replace("{faction}", $server->getPluginManager()->getPlugin('FactionsPro')->getPlayerFaction($name), $text);
		$text = str_replace("{money}", $server->getPluginManager()->getPlugin('EconomyAPI')->myMoney($name), $text);
		$text = str_replace("{pp_group}", $server->getPluginManager()->getPlugin('PurePerms')->getUserDataMgr()->getData($player)['group'], $text);
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
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

	private function getWorlds() {
		$mode = $this->getConfig()->get("mode", 0);
		$worldnames = $this->getConfig()->get("worlds", []);
		/** @var Level[] $worlds */
		$worlds = [];
		switch ($mode) {
			case 0://Every
				$worlds = $this->getServer()->getLevels();
				break;
			case 1://only
				foreach ($worldnames as $name) {
					if (!is_null($level = $this->getServer()->getLevelByName($name))) $worlds[] = $level;
					else $this->getLogger()->warning("Config error! World " . $name . " not found!");
				}
				break;
			case 2://not in
				$worlds = $this->getServer()->getLevels();
				foreach ($worlds as $world) {
					if (!in_array(strtolower($world->getName()), $worldnames)) {
						$worlds[] = $world;
					}
				}
				break;
		}
		return $worlds;
	}
}
