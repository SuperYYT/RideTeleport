<?php

namespace ridetp;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\command\ConsoleCommandSender;

use ridetp\RTPEventListener;
use ridetp\language\LanguageManager;

class RideTeleport extends PluginBase
{

    const TAG_ERROR = "§l§7RTP§r§l>§r§l§cError§r§l>§r §c";
    const TAG_INFO  = "§l§7RTP§r§l>§r§l§bInfo§r§l>§r §b";
    const TAG_WARN = "§l§7RTP§r§l>§r§l§eWarn§r§l>§r §e";

    public $languageManager;

    public function onEnable()
    {
        $this->loadConfig();
        $this->loadLanguage();
        if (!file_exists($this->getDataFolder())) @mkdir($this->getDataFolder(), 0744, true);
        $this->config = new Config($this->getDataFolder()."coordinations.json", Config::JSON, []);
        $this->getLogger()->info("RideTeleport v".$this->getDescription()->getVersion()." was enabled.");
        $this->getServer()->getPluginManager()->registerEvents(new RTPEventListener($this), $this);

        $this->getServer()->getCommandMap()->register("rtp", new RTPCommand($this));
    }


    public function sendHow2Use($player)
    {
        $player->sendMessage($this->languageManager->get("command.help"));
    }


    public function loadConfig()
    {
        $this->saveDefaultConfig();
        $this->reloadConfig();
        if(!file_exists($this->getDataFolder())) @mkdir($this->getDataFolder(), 0744, true);
        $this->config = new Config($this->getDataFolder()."config.yml", Config::YAML);
    }


    public function loadLanguage()
    {
        $languageCode = $this->config->get("language");
        $resources = $this->getResources();
        foreach ($resources as $resource) {
            if ($resource->getFilename() === "eng.ini") {
                $default = parse_ini_file($resource->getPathname());
            }
            if ($resource->getFilename() === $languageCode.".ini") {
                $setting = parse_ini_file($resource->getPathname());
            }
        }

        if (isset($setting)) {
            $langJson = $setting;
        } else {
            $langJson = $default;
        }
        $this->languageManager = new LanguageManager($this, $langJson);
    }


    public function getLanguageManager()
    {
        return $this->languageManager;
    }

}