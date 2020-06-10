<?php

namespace xenialdan\BossAnnouncement;

use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;

class EventListener implements Listener
{

    public function onJoin(PlayerJoinEvent $ev): void
    {
        if (Loader::getInstance()->isWorldEnabled($ev->getPlayer()->getLevel()->getName())) {
            Loader::getInstance()->bar->addPlayer($ev->getPlayer());
        }
    }

    public function onLeave(PlayerQuitEvent $ev): void
    {
        Loader::getInstance()->bar->removePlayer($ev->getPlayer());
    }

    public function onLevelChange(EntityLevelChangeEvent $ev): void
    {
        if ($ev->isCancelled() || !$ev->getEntity() instanceof Player) {
            return;
        }
        Loader::getInstance()->bar->removePlayer($ev->getEntity());
        if (Loader::getInstance()->isWorldEnabled($ev->getTarget()->getName())) {
            Loader::getInstance()->bar->addPlayer($ev->getEntity());
        }
    }

}