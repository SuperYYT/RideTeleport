<?php

namespace ridetp;

use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;
use pocketmine\level\Position;

class RTPEventListener implements Listener
{

	/**
	 * @var PluginBase
	 */
	private $owner;


	/**
	 * @var array | null
	 */
	private $data;


	public function __construct($owner)
	{
		$this->owner = $owner;
	}


	public function onPlace(BlockPlaceEvent $event)
	{
		$player = $event->getPlayer();
		if (isset($player->session)) $event->setCancelled();
	}


	public function onMove(PlayerMoveEvent $event)
	{
		$player = $event->getPlayer();
		$level = $player->getLevel();
		$array = [$player->getFloorX()+0.5, $player->getFloorY()-1, $player->getFloorZ()+0.5, $player->getLevel()->getName()];
		$key = implode(",", $array);
		if($this->getConfigData()->exists($key)){
			$xyz = $this->getConfigData()->get($key);
			if(isset($xyz)){
				$lev = $this->owner->getServer()->getLevelByName((string)$xyz[3]);
				$pos = new Position((float)$xyz[0], (float)$xyz[1],  (float)$xyz[2], $lev);
				$player->teleport($pos);
			}
		}
	}


	public function onClick(PlayerInteractEvent $event)
	{
		$player = $event->getPlayer();
		$block = $event->getBlock();
		if (isset($player->session)) {
			switch ($player->session) {

				case "from":
					$fxyz = [$block->getFloorX()+0.5, $block->getFloorY(), $block->getFloorZ()+0.5, $block->getLevel()->getName()];
					$key = implode(",", $fxyz);
					if (!$this->getConfigData()->exists($key)) {
	    				$player->rtpdata["FROM"] = $key;
	    				$player->sendMessage($this->owner::TAG_INFO."ワープポータルを現在の地点に設定しました");
	    				$player->sendMessage($this->owner::TAG_INFO."> 座標 : (".$fxyz[0].",".$fxyz[1].",".$fxyz[2].") に設定しました");
	    				$player->sendMessage($this->owner::TAG_INFO."> ワールド : (".$fxyz[3].") に設定しました");
	    			} else {
	    				$player->sendMessage("§cこのワープポータルは既に使われています");
	    			}
					break;

				case "to":
					$txyz = [$block->getFloorX()+0.5, $block->getFloorY()+1, $block->getFloorZ()+0.5, $block->getLevel()->getName()];
	    			$player->rtpdata["TO"] = $txyz;
	    			$player->sendMessage($this->owner::TAG_INFO."移動先を設定しました");
	    			$player->sendMessage($this->owner::TAG_INFO."> 座標 : (".$txyz[0].",".$txyz[1].",".$txyz[2].") に設定しました");
	    			$player->sendMessage($this->owner::TAG_INFO."> ワールド : (".$txyz[3].") に設定しました");
	    			$player->sendMessage($this->owner::TAG_WARN."/rtp load で設定を適応できます");
					break;

				case "del":
					$array = [$block->getFloorX()+0.5, $block->getFloorY(), $block->getFloorZ()+0.5, $block->getLevel()->getName()];
					$key = implode(",", $array);
					if ($this->getConfigData()->exists($key)) {
						$this->getConfigData()->remove($key);
						$this->getConfigData()->save();
						$player->sendMessage($this->owner::TAG_INFO."この座標のワープポータルを削除しました");
					} else {
						$player->sendMessage($this->owner::TAG_ERROR."この座標のワープポータルは使われていません");
					}
					break;
			}
		}
		unset($player->session);
	}


	public function getConfigData()
	{
		return $this->owner->config;
	}
}