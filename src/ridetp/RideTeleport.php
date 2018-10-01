<?php

namespace ridetp;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

use ridetp\RTPEventListener;

class RideTeleport extends PluginBase
{

	const TAG_ERROR = "§l§7RTP§r§l>§r§l§cError§r§l>§r §c";
	const TAG_INFO  = "§l§7RTP§r§l>§r§l§bInfo§r§l>§r §b";
	const TAG_WARN = "§l§7RTP§r§l>§r§l§eWarn§r§l>§r §e";

	public function onEnable()
	{
		if (!file_exists($this->getDataFolder())) @mkdir($this->getDataFolder(), 0744, true);
		$this->config = new Config($this->getDataFolder()."coordinations.json", Config::JSON, []);
		$this->getLogger()->info("RideTeleport v".$this->getDescription()->getVersion()."を起動しました");
		$this->getServer()->getPluginManager()->registerEvents(new RTPEventListener($this), $this);

		$this->getServer()->getCommandMap()->register("rtp", new RTPCommand($this));
	}


	public function onDisable()
	{
		$this->getLogger()->info("RideTeleport v".$this->getDescription()->getVersion()."を終了しました");
	}

}