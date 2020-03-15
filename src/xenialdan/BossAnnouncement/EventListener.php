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
        if (Loader::getInstance()->isWorldEnabled($ev->getPlayer()->getLevel()->getName())) {
            Loader::getInstance()->bar->addPlayer($ev->getPlayer());
        }
    }

    public function onLeave(PlayerQuitEvent $ev)
    {
        Loader::getInstance()->bar->removePlayer($ev->getPlayer());
    }

    public function onLevelChange(EntityLevelChangeEvent $ev)
    {
        if ($ev->isCancelled() || !$ev->getEntity() instanceof Player) return;
        Loader::getInstance()->bar->removePlayer($ev->getEntity());
        if (Loader::getInstance()->isWorldEnabled($ev->getTarget()->getName())) {
            Loader::getInstance()->bar->addPlayer($ev->getEntity());
        }
    }

}