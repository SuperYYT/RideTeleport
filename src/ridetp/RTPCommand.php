<?php

namespace ridetp;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;

class RTPCommand extends Command
{

	private $owner;


    public function __construct($owner)
    {
        parent::__construct("rtp", "ホームに戻ります", "/rtp");
        $this->setPermission("rtp.command");
        $this->owner = $owner;
    }


    public function execute(CommandSender $sender, string $label, array $params): bool
    {
        if(!$this->owner->isEnabled()) return false;
        if ($sender instanceof ConsoleCommandSender) {
            $sender->sendMessage($this->owner::TAG_ERROR."ゲーム内で使用してください");
            return false;
    	} else {
    		if (!isset($params[0])) {
    			$this->sendHow2Use($sender);
    			return false;
    		}
    		switch ($params[0]) {
    			case "from":
    				if (isset($sender->rtpdata["FROM"])) {
    					$sender->sendMessage($this->owner::TAG_WARN."既に設定されています");
    					$sender->sendMessage($this->owner::TAG_WARN."再設定は §l/rtp reset§r§e をしてください");
    					return false;
    				}
    				$sender->session = "from";
	    			$sender->sendMessage($this->owner::TAG_INFO."ワープ始点のポイントをタッチしてください");
	    			return true;
	    			break;

    			case "to":
    				if (isset($sender->rtpdata["TO"])) {
    					$sender->sendMessage($this->owner::TAG_WARN."既に設定されています");
    					$sender->sendMessage($this->owner::TAG_WARN."再設定は §l/rtp reset§r§e をしてください");
    					return false;
    				}
    				$sender->session = "to";
	    			$sender->sendMessage($this->owner::TAG_INFO."ワープ先のポイントをタッチしてください");
	    			return true;
	    			break;

    			case "load":
	    			if (!isset($sender->rtpdata["TO"], $sender->rtpdata["FROM"])) {
	    				$sender->sendMessage($this->owner::TAG_ERROR."座標の設定がされていません §l/rtp help§r§c でコマンドを確認してください");
	    				return false;
	    			}
	    			$this->getConfigData()->set($sender->rtpdata["FROM"], $sender->rtpdata["TO"]);
	    			$this->getConfigData()->save();
	    			unset($sender->rtpdata);
	    			$sender->sendMessage($this->owner::TAG_INFO."データを保存しました");
	    			return true;
	    			break;

    			case "del":
	    			$sender->session = "del";
	    			$sender->sendMessage($this->owner::TAG_INFO."削除したいポイントをタッチしてください");
	    			return true;
	    			break;

    			case "reset":
	    			if (isset($sender->rtpdata)) unset($sender->rtpdata);
	    			$sender->sendMessage($this->owner::TAG_INFO."設定項目のリセットをしました。");
	    			$sender->sendMessage($this->owner::TAG_INFO."もう一回やりなおす際は /rtp from をご利用ください");
	    			return true;
	    			break;

    			case "help":
    			default;
	    			$this->sendHow2Use($sender);
	    			return true;
	    			break;
    		}
    		return true;
        }
    }


    public function sendHow2Use($player)
    {
    	$player->sendMessage("§b/rtp from   現在地点をワープポータルに設定");
    	$player->sendMessage("§b/rtp to     ワープ先の座標を設定");
    	$player->sendMessage("§b/rtp load   データの保存");
    	$player->sendMessage("§b/rtp del    データの削除");
    	$player->sendMessage("§b/rtp reset  設定途中のデータリセット");
    	$player->sendMessage("§b/rtp help   使い方");
    }


    public function getConfigData()
	{
		return $this->owner->config;
	}
}