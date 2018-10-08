<?php

namespace ridetp;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\item\Item;

class RTPCommand extends Command
{

    private $owner;


    public function __construct($owner)
    {
        parent::__construct("rtp", "RideTeleport", "/rtp");
        $this->setPermission("rtp.command");
        $this->owner = $owner;
    }


    public function execute(CommandSender $sender, string $label, array $params): bool
    {
        if(!$this->owner->isEnabled()) return false;
        if ($sender instanceof ConsoleCommandSender) {
            $sender->sendMessage($this->owner::TAG_ERROR.$this->owner->getLanguageManager()->get("command.fromconsole"));
            return false;
        } else {
            if (!isset($params[0])) {
                $this->owner->sendHow2Use($sender);
                return false;
            }
            switch ($params[0]) {

                case "from":
                    if (isset($sender->rtpdata["FROM"])) {
                        $sender->sendMessage("\n".$this->owner::TAG_WARN.$this->owner->getLanguageManager()->get("command.from.error"));
                        return false;
                    }
                    $sender->session = "from";
                    $sender->sendMessage("\n".$this->owner::TAG_INFO.$this->owner->getLanguageManager()->get("command.from.message"));
                    return true;
                    break;


                case "to":
                    if (isset($sender->rtpdata["TO"])) {
                        $sender->sendMessage("\n".$this->owner::TAG_WARN.$this->owner->getLanguageManager()->get("command.to.error"));
                        return false;
                    }
                    $sender->session = "to";
                    $sender->sendMessage("\n".$this->owner::TAG_INFO.$this->owner->getLanguageManager()->get("command.to.message"));
                    return true;
                    break;


                case "del":
                    $sender->session = "del";
                    $sender->sendMessage("\n".$this->owner::TAG_INFO.$this->owner->getLanguageManager()->get("command.del.message"));
                    return true;
                    break;


                case "reset":
                    if (isset($sender->rtpdata)) unset($sender->rtpdata);
                    if (isset($sender->tapdel)) unset($sender->tapdel);
                    if (isset($sender->towardRtp)) unset($sender->towardRtp);
                    if (isset($player->session)) unset($player->session);
                    $sender->sendMessage("\n".$this->owner::TAG_INFO.$this->owner->getLanguageManager()->get("command.reset.message"));
                    return true;
                    break;


                case "tapdel":
                    $sender->session = "tapdel";
                    $sender->sendMessage("\n".$this->owner::TAG_INFO.$this->owner->getLanguageManager()->get("command.tapdel.message"));
                    return true;
                    break;


                case "delall":
                    if (!is_array($sender->tapdel) || empty($sender->tapdel)) {
                        $sender->sendMessage("\n".$this->owner::TAG_WARN.$this->owner->getLanguageManager()->get("command.delall.error"));
                        return false;
                    }
                    foreach ($sender->tapdel as $key => $value) {
                        if ($this->getConfigData()->exists($key)) {
                            $this->getConfigData()->remove($key);
                        }
                    }
                    $this->getConfigData()->save();
                    $sender->sendMessage("\n".$this->owner::TAG_INFO.$this->owner->getLanguageManager()->get("command.delall.message"));
                    unset($sender->tapdel);
                    unset($sender->session);
                    return true;
                    break;


                case "fromto":
                    if (!$sender->isCreative()) {
                        $sender->sendMessage("\n".$this->owner::TAG_WARN.$this->owner->getLanguageManager()->get("command.fromto.error"));
                        return false;
                    }
                    $sender->session = "fromto";
                    $sender->sendMessage("\n".$this->owner::TAG_INFO.$this->owner->getLanguageManager()->get("command.fromto.message"));
                    $item1 = Item::get(351, 10, 1)->setCustomName($this->owner->getLanguageManager()->get("command.fromto.firstp"));
                    $item2 = Item::get(351, 12, 1)->setCustomName($this->owner->getLanguageManager()->get("command.fromto.endp"));
                    $sender->getInventory()->setItem(0, $item1);
                    $sender->getInventory()->setItem(1, $item2);
                    return true;
                    break;


                case "setfromto":
                    if (!isset($sender->towardRtp["T"], $sender->towardRtp["F"])) {
                        $sender->sendMessage("\n".$this->owner::TAG_WARN.$this->owner->getLanguageManager()->get("command.setfromto.error"));
                        if (isset($sender->session)) unset($sender->session);
                        return false;
                    }
                    $this->getConfigData()->set($sender->towardRtp["F"], $sender->towardRtp["T"]);
                    $this->getConfigData()->save();
                    unset($sender->towardRtp);
                    $sender->sendMessage("\n".$this->owner::TAG_WARN.$this->owner->getLanguageManager()->get("command.setfromto.message"));
                    $item1 = Item::get(351, 10, 1);
                    $item2 = Item::get(351, 12, 1);
                    $sender->getInventory()->removeItem($item1);
                    $sender->getInventory()->removeItem($item2);
                    if (isset($sender->session)) unset($sender->session);
                    return true;
                    break;


                case "help":
                default;
                    $this->owner->sendHow2Use($sender);
                    return true;
                    break;
            }
            return true;
        }
    }


    public function getConfigData()
    {
        return $this->owner->config;
    }
}