<?php

namespace ridetp;

use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
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


    public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        $player->rtpTick = 0;
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
        $tick = $this->owner->getServer()->getTick();
        if ($tick - $player->rtpTick <= 3) {
            $event->setCancelled();
            return;
        }
        $player->rtpTick = $tick;

        $block = $event->getBlock();
        if (isset($player->session)) {
            switch ($player->session) {

                case "from":
                    $fxyz = [$block->getFloorX()+0.5, $block->getFloorY(), $block->getFloorZ()+0.5, $block->getLevel()->getName()];
                    $key = implode(",", $fxyz);
                    if (!$this->getConfigData()->exists($key)) {
                        $player->rtpdata["FROM"] = $key;
                        $player->sendMessage("\n".$this->owner::TAG_INFO.$this->owner->getLanguageManager()->get("event.from.message", $fxyz));
                    } else {
                        $player->sendMessage("\n".$this->owner::TAG_ERROR.$this->owner->getLanguageManager()->get("event.from.error"));
                    }
                    break;


                case "to":
                    $txyz = [$block->getFloorX()+0.5, $block->getFloorY()+1, $block->getFloorZ()+0.5, $block->getLevel()->getName()];
                    $player->rtpdata["TO"] = $txyz;
                    $player->sendMessage("\n".$this->owner::TAG_INFO.$this->owner->getLanguageManager()->get("event.to.message", $txyz));

                    if (!isset($player->rtpdata["TO"], $player->rtpdata["FROM"])) {
                        $player->sendMessage("\n".$this->owner::TAG_ERROR.$this->owner->getLanguageManager()->get("event.to.error"));
                        return false;
                    }
                    $this->getConfigData()->set($player->rtpdata["FROM"], $player->rtpdata["TO"]);
                    $this->getConfigData()->save();
                    unset($player->rtpdata);
                    break;


                case "fromto":
                    $item = $event->getItem();
                    $iddmg = $item->getId().":".$item->getDamage();
                    $array = [$block->getFloorX()+0.5, $block->getFloorY(), $block->getFloorZ()+0.5, $block->getLevel()->getName()];
                    if ($iddmg == "351:10") {
                        $key = implode(",", $array);
                        $player->sendPopUp($this->owner->getLanguageManager()->get("event.fromto.firstmessage", $array));
                        $player->towardRtp["F"] = $key;
                    }
                    if ($iddmg == "351:12") {
                        $player->sendPopUp($this->owner->getLanguageManager()->get("event.fromto.lastmessage", $array));
                        $array[1] += 1;
                        $player->towardRtp["T"] = $array;
                    }
                    break;


                case "del":
                    $array = [$block->getFloorX()+0.5, $block->getFloorY(), $block->getFloorZ()+0.5, $block->getLevel()->getName()];
                    $key = implode(",", $array);
                    if ($this->getConfigData()->exists($key)) {
                        $this->getConfigData()->remove($key);
                        $this->getConfigData()->save();
                        $player->sendMessage("\n".$this->owner::TAG_INFO.$this->owner->getLanguageManager()->get("event.del.message"));
                    } else {
                        $player->sendMessage("\n".$this->owner::TAG_ERROR.$this->owner->getLanguageManager()->get("event.del.error"));
                    }
                    break;


                case "tapdel":
                    $array = [$block->getFloorX()+0.5, $block->getFloorY(), $block->getFloorZ()+0.5, $block->getLevel()->getName()];
                    $key = implode(",", $array);
                    if (isset($player->tapdel[$key])) {
                        unset($player->tapdel[$key]);
                        $player->sendPopUp($this->owner->getLanguageManager()->get("event.tapdel.delmessage", $array));
                    } else {
                        $player->tapdel[$key] = true;
                        $player->sendPopUp($this->owner->getLanguageManager()->get("event.tapdel.addmessage", $array));
                    }
                    break;
            }
        }
        if (isset($player->session)) {
            if ($player->session == "tapdel") return;
            if ($player->session == "fromto") return;
            unset($player->session);
        }
    }


    public function getConfigData()
    {
        return $this->owner->config;
    }
}