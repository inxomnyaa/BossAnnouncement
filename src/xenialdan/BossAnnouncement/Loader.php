<?php

/*
 * BossAnnouncement
 * A plugin by XenialDan aka thebigsmileXD
 * http://github.com/thebigsmileXD/BossAnnouncement
 * A simple boss bar tile plugin using apibossbar
 */

namespace xenialdan\BossAnnouncement;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use xenialdan\apibossbar\DiverseBossBar;

class Loader extends PluginBase implements Listener
{
    /** @var DiverseBossBar */
    public $bar = null;
    public $title = '', $subTitles = [], $changeSpeed = 0, $i = 0;
    public static $instance;

    public function onLoad()
    {
        self::$instance = $this;
    }

    /**
     * @return self
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    public function onEnable()
    {
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->title = $this->getConfig()->get('head-message', '');
        $this->subTitles = $this->getConfig()->get('changing-messages', []);
        $this->changeSpeed = max(1, $this->getConfig()->get('change-speed', 1));
        $this->bar = (new DiverseBossBar())->setTitle($this->title);//setTitle needed?
        $this->getScheduler()->scheduleRepeatingTask(new class extends Task
        {
            public function onRun(int $currentTick)
            {
                Loader::getInstance()->i++;
                if (Loader::getInstance()->i >= count(Loader::getInstance()->subTitles)) Loader::getInstance()->i = 0;
                foreach (Loader::getInstance()->getWorlds() as $world) {
                    foreach ($world->getPlayers() as $player) {
                        Loader::getInstance()->setText($player);
                    }
                }
            }
        }, 20 * $this->changeSpeed);
    }

    /**
     * Generates and sets the output
     *
     * @param Player $player
     */
    public function setText(Player $player): void
    {
        $currentMSG = $this->subTitles[$this->i % count($this->subTitles)];
        if (strpos($currentMSG, '%') > -1) {
            $percentage = substr($currentMSG, 1, strpos($currentMSG, '%') - 1);
            if (is_numeric($percentage)) $this->bar->setPercentageFor([$player], $percentage / 100);
            $currentMSG = substr($currentMSG, strpos($currentMSG, '%') + 2);
        }
        if (!empty($this->title)) {
            $this->bar->setTitleFor([$player], $this->formatText($player, $this->title))->setSubTitleFor([$player], $this->formatText($player, $currentMSG));
        } else {
            $this->bar->setTitleFor([$player], $this->formatText($player, $currentMSG));
        }
    }

    /**
     * Formats the string
     *
     * @param Player $player
     * @param string $text
     * @return string
     */
    public function formatText(Player $player, string $text)
    {
        $text = str_replace("{display_name}", $player->getDisplayName(), $text);
        $text = str_replace("{name}", $player->getName(), $text);
        $text = str_replace("{x}", $player->getFloorX(), $text);
        $text = str_replace("{y}", $player->getFloorY(), $text);
        $text = str_replace("{z}", $player->getFloorZ(), $text);
        $text = str_replace("{world}", !is_null($level = $player->getLevel()) ? $level->getName() : "", $text);
        $text = str_replace("{level_players}", count($player->getLevel()->getPlayers()), $text);
        $text = str_replace("{server_players}", count($player->getServer()->getOnlinePlayers()), $text);
        $text = str_replace("{server_max_players}", $player->getServer()->getMaxPlayers(), $text);
        $text = str_replace("{hour}", date('H'), $text);
        $text = str_replace("{minute}", date('i'), $text);
        $text = str_replace("{second}", date('s'), $text);
        // preg_match_all ("/(\{.*?\})/ig", $text, $brackets);

        //TODO auto function
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

    /** @return Level[] $worlds */
    public function getWorlds()
    {
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

    public function onDeath(PlayerDeathEvent $ev) {
        $this->bar->removePlayer($ev->getPlayer())->addPlayer($ev->getPlayer());
    }
}
