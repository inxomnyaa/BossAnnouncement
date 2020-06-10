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
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use xenialdan\apibossbar\DiverseBossBar;

class Loader extends PluginBase implements Listener
{
    /** @var DiverseBossBar */
    public $bar;
    public $title = '', $subTitles = [], $changeSpeed = 0, $i = 0;
    public static $instance;

    public function onLoad(): void
    {
        self::$instance = $this;
    }

    /**
     * @return self
     */
    public static function getInstance(): self
    {
        return self::$instance;
    }

    /**
     * @throws PluginException
     */
    public function onEnable(): void
    {
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->title = $this->getConfig()->get('head-message', '');
        $this->subTitles = $this->getConfig()->get('changing-messages', []);
        $this->changeSpeed = max(1, $this->getConfig()->get('change-speed', 1));
        $this->bar = (new DiverseBossBar())->setTitle($this->title);//setTitle needed?
        $this->getScheduler()->scheduleRepeatingTask(new class extends Task {
            public function onRun(int $currentTick): void
            {
                Loader::getInstance()->i++;
                if (Loader::getInstance()->i >= count(Loader::getInstance()->subTitles)) {
                    Loader::getInstance()->i = 0;
                }
                foreach (Loader::getInstance()->bar->getPlayers() as $player) {
                    if ($player->isOnline() && Loader::getInstance()->isWorldEnabled($player->getLevel()->getName())) {
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
            if (is_numeric($percentage)) {
                $this->bar->setPercentageFor([$player], $percentage / 100);
            }
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
    public function formatText(Player $player, string $text): string
    {
        // preg_match_all ("/(\{.*?\})/ig", $text, $brackets);

        //TODO auto function
        $text = str_replace(['{display_name}', '{name}', '{x}', '{y}', '{z}', '{world}', '{level_players}', '{server_players}', '{server_max_players}', '{hour}', '{minute}', '{second}', '{BLACK}', '{DARK_BLUE}', '{DARK_GREEN}', '{DARK_AQUA}', '{DARK_RED}', '{DARK_PURPLE}', '{GOLD}', '{GRAY}', '{DARK_GRAY}', '{BLUE}', '{GREEN}', '{AQUA}', '{RED}', '{LIGHT_PURPLE}', '{YELLOW}', '{WHITE}', '{OBFUSCATED}', '{BOLD}', '{STRIKETHROUGH}', '{UNDERLINE}', '{ITALIC}', '{RESET}', '&0', '&1', '&2', '&3', '&4', '&5', '&6', '&7', '&8', '&9', '&a', '&b', '&c', '&d', '&e', '&f', '&k', '&l', '&m', '&n', '&o', '&r'], [$player->getDisplayName(), $player->getName(), $player->getFloorX(), $player->getFloorY(), $player->getFloorZ(), ($level = $player->getLevel()) !== null ? $level->getName() : '', count($player->getLevel()->getPlayers()), count($player->getServer()->getOnlinePlayers()), $player->getServer()->getMaxPlayers(), date('H'), date('i'), date('s'), '&0', '&1', '&2', '&3', '&4', '&5', '&6', '&7', '&8', '&9', '&a', '&b', '&c', '&d', '&e', '&f', '&k', '&l', '&m', '&n', '&o', '&r', TextFormat::BLACK, TextFormat::DARK_BLUE, TextFormat::DARK_GREEN, TextFormat::DARK_AQUA, TextFormat::DARK_RED, TextFormat::DARK_PURPLE, TextFormat::GOLD, TextFormat::GRAY, TextFormat::DARK_GRAY, TextFormat::BLUE, TextFormat::GREEN, TextFormat::AQUA, TextFormat::RED, TextFormat::LIGHT_PURPLE, TextFormat::YELLOW, TextFormat::WHITE, TextFormat::OBFUSCATED, TextFormat::BOLD, TextFormat::STRIKETHROUGH, TextFormat::UNDERLINE, TextFormat::ITALIC, TextFormat::RESET], $text);

        return $text;
    }

    /**
     * @param string $levelName
     * @return bool
     */
    public function isWorldEnabled(string $levelName): bool
    {
        $mode = $this->getConfig()->get('mode', 0);
        $configWorlds = array_map(static function (string $worldName): string {
            return strtolower(TextFormat::clean($worldName));
        }, $this->getConfig()->get('worlds', []));
        $levelName = strtolower(TextFormat::clean($levelName));
        switch ($mode) {
            case 0://Every world
                return true;
                break;
            case 1://Only config worlds
                return in_array($levelName, $configWorlds, true);
                break;
            case 2://Exclude config worlds
                return !in_array($levelName, $configWorlds, true);
                break;
        }
        return false;
    }

    public function onDeath(PlayerDeathEvent $ev)
    {
        $this->bar->removePlayer($ev->getPlayer())->addPlayer($ev->getPlayer());
    }
}
