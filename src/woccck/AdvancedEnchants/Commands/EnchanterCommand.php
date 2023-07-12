<?php

namespace woccck\AdvancedEnchants\Commands;

use muqsit\customsizedinvmenu\CustomSizedInvMenu;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\Dye;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use woccck\AdvancedEnchants\AdvancedCE;
use woccck\AdvancedEnchants\Utils\Utils;

class EnchanterCommand extends Command implements PluginOwned {


    /** @var AdvancedCE */
    public AdvancedCE $plugin;

    public function __construct(AdvancedCE $plugin){
        parent::__construct("enchanter", "Opens the Server custom enchants GUI.", "/enchanter", ["ce", "e"]);
        $this->setPermission("advancedcustomenchants.enchanter");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): mixed
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "This command can only be used in-game.");
            return false;
        }

        Utils::getEnchanterGUI($sender);
        return true;
    }


    public function getOwningPlugin(): AdvancedCE
    {
        return $this->plugin;
    }
}
