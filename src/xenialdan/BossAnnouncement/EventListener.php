<?php

namespace xenialdan\BossAnnouncement;

use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;

class EventListener implements Listener
{

    public function onJoin(PlayerJoinEvent $ev): void
    {
        if (Loader::getInstance()->isWorldEnabled($ev->getPlayer()->getWorld()->getFolderName())) {
            Loader::getInstance()->bar->addPlayer($ev->getPlayer());
        }
    }

    public function onLeave(PlayerQuitEvent $ev): void
    {
        Loader::getInstance()->bar->removePlayer($ev->getPlayer());
    }

    public function onTeleport(EntityTeleportEvent $ev): void
    {
        if ($ev->getTo()->getWorld()->getId() !== $ev->getFrom()->getWorld()->getId() || $ev->isCancelled() || !$ev->getEntity() instanceof Player) {
            return;
        }
        Loader::getInstance()->bar->removePlayer($ev->getEntity());
        if (Loader::getInstance()->isWorldEnabled($ev->getTo()->getWorld()->getFolderName())) {
            Loader::getInstance()->bar->addPlayer($ev->getEntity());
        }
    }

}