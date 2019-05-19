<?php


namespace xenialdan\BossAnnouncement;


use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;

class EventListener implements Listener
{

    public function onJoin(PlayerJoinEvent $ev)
    {
        if (in_array($ev->getPlayer()->getLevel(), Loader::getInstance()->getWorlds())) {
            Loader::getInstance()->bar->addPlayer($ev->getPlayer());
        }
    }

    public function onLeave(PlayerQuitEvent $ev)
    {
        Loader::getInstance()->bar->removePlayer($ev->getPlayer());
    }
    //////
    //fix mode 2

    public function onLevelChange(EntityLevelChangeEvent $ev)
    {
        if ($ev->isCancelled() || !$ev->getEntity() instanceof Player) return;
        if (in_array($ev->getTarget(), Loader::getInstance()->getWorlds())) {
            Loader::getInstance()->bar->addPlayer($ev->getEntity());
        } else {
            Loader::getInstance()->bar->removePlayer($ev->getEntity());
        }
    }

}