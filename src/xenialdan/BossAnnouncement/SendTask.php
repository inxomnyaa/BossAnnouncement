<?php

namespace xenialdan\BossAnnouncement;

use pocketmine\scheduler\PluginTask;
use pocketmine\plugin\Plugin;

class SendTask extends PluginTask{

	public function onRun($currentTick){
		$this->getOwner()->sendBossBar();
	}

	public function cancel(){
		$this->getHandler()->cancel();
	}
}