<?php

namespace woccck\AdvancedEnchants;

use muqsit\invmenu\InvMenuHandler;
use pocketmine\plugin\PluginBase;
use woccck\AdvancedEnchants\Commands\EnchanterCommand;
use woccck\AdvancedEnchants\Events\ItemListener;

class AdvancedCE extends PluginBase {

    /** @var AdvancedCE */
    private static AdvancedCE $instance;

    public function onLoad(): void
    {
        self::$instance = $this;
    }

    public function onEnable(): void
    {
        $this->registerCommands();
        $this->registerEvents();
        if(!InvMenuHandler::isRegistered()){
            InvMenuHandler::register($this);
        }
    }

    public function registerCommands() {
        $this->getServer()->getCommandMap()->registerAll("advancedcustomenchants", [
            new EnchanterCommand($this)
        ]);
    }

    public function registerEvents() {
        $pluginMngr = $this->getServer()->getPluginManager();
        $pluginMngr->registerEvents(new ItemListener(), $this);
    }

    public static function getInstance(): AdvancedCE
    {
        return self::$instance;
    }
}
