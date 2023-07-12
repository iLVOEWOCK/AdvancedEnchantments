<?php

namespace woccck\AdvancedEnchants\Events;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Durable;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\AnvilFallSound;
use pocketmine\world\sound\XpLevelUpSound;
use woccck\AdvancedEnchants\AdvancedCE;
use woccck\AdvancedEnchants\Items\Items;
use woccck\AdvancedEnchants\Tasks\ItemUpdateTask;
use woccck\AdvancedEnchants\Utils\Utils;

class ItemListener implements Listener
{

    /** @var array $itemRenamer */
    public array $itemRenamer = [];

    /** @var array $lorerenamer */
    public array $lorerenamer = [];

    /** @var array $nameTagMessage */
    public array $nameTagMessage = [];

    public function playerJoin(PlayerJoinEvent $event)
    {
        //$event->getPlayer()->getInventory()->addItem(Items::get(1, 64));
    }

    /**
     * @priority HIGHEST
     */
    public function onItemRename(PlayerChatEvent $event): void
    {
        $player = $event->getPlayer();
        if (!isset($this->itemRenamer[$player->getName()])) {
            return;
        }

        $message = $event->getMessage();
        $hand = $player->getInventory()->getItemInHand();
        $event->cancel();

        if ($hand->getTypeId() === VanillaItems::AIR()->getTypeId()) {
            $messageFormats = Utils::getConfig()->getNested("items.itemnametag.messages.air", [""]);

            foreach ($messageFormats as $messageFormat) {
                $player->sendMessage(TextFormat::colorize($messageFormat));
            }
            return;
        }

        if (count($hand->getEnchantments()) === 0) {
            $messageFormats = Utils::getConfig()->getNested("items.itemnametag.messages.renaming-non-enchanted-item", [""]);

            foreach ($messageFormats as $messageFormat) {
                $player->sendMessage(TextFormat::colorize($messageFormat));
            }
            return;
        }

        if ($message === "cancel") {
            $player->sendMessage("§r§c§l** §r§cYou have unqueued your Itemtag for this usage.");
            Utils::playSound($player, "mob.enderdragon.flap", 2);
            $player->getInventory()->addItem(Items::get(0));
            unset($this->itemRenamer[$player->getName()]);
            if (isset($this->nameTagMessage[$player->getName()])) unset($this->nameTagMessage[$player->getName()]);
        }
        if ($event->getMessage() === "confirm" && isset($this->nameTagMessage[$player->getName()])) {
            $messageFormats = Utils::getConfig()->getNested("items.itemnametag.messages.success", [""]);
            $customName = $this->nameTagMessage[$player->getName()];

            foreach ($messageFormats as $messageFormat) {
                $message = str_replace("{item_name}", $customName, $messageFormat);
                $player->sendMessage(TextFormat::colorize($message));
            }

            $player->getLocation()->getWorld()->addSound($player->getLocation(), new XpLevelUpSound(100));
            $hand->setCustomName($this->nameTagMessage[$player->getName()]);
            $player->getInventory()->setItemInHand($hand);
            unset($this->itemRenamer[$player->getName()]);
            unset($this->nameTagMessage[$player->getName()]);
        }
        if (strlen($event->getMessage()) > 26) {
            $player->sendMessage("§r§cYour custom name exceeds the 36 character limit.");
            return;
        }
        if (!isset($this->nameTagMessage[$player->getName()]) && $event->getMessage() !== "cancel" && $event->getMessage() !== "confirm") {
            $formatted = TextFormat::colorize($message);
            $player->sendMessage("§r§e§l(!) §r§eItem Name Preview: $formatted");
            $player->sendMessage("§r§7Type '§r§aconfirm§7' if this looks correct, otherwise type '§ccancel§7' to start over.");
            $this->nameTagMessage[$player->getName()] = $formatted;
        }
    }

    public function onPlayerUse(PlayerItemUseEvent $event)
    {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $hand = $player->getInventory()->getItemInHand();
        $tag = $item->getNamedTag();
        if ($tag->getString("rename", "") !== "") {
            if (isset($this->itemRenamer[$player->getName()])) {
                $player->sendMessage("§r§c§l(!) §r§cYou are already in queue for a item tag type cancel to remove it!");
                return;
            }
            if (isset($this->lorerenamer[$player->getName()])) {
                $player->sendMessage("§r§c§l(!) §r§cYou are already in queue for a lore rename tag type cancel to remove it!");
                return;
            }
            $this->itemRenamer[$player->getName()] = $player;
            $hand->setCount($hand->getCount() - 1);
            $player->getInventory()->setItemInHand($hand);
            $messageFormats = Utils::getConfig()->getNested("items.itemnametag.messages.activate", [""]);

            foreach ($messageFormats as $messageFormat) {
                $player->sendMessage(TextFormat::colorize($messageFormat));
            }
            Utils::playSound($player, "mob.enderdragon.flap", 2);
        }
    }

    /**
     * @priority HIGHEST
     */
    public function dropBlockTrak(InventoryTransactionEvent $event): void
    {
        $transaction = $event->getTransaction();
        $actions = array_values($transaction->getActions());
        if (count($actions) === 2) {
            foreach ($actions as $i => $action) {
                $ids = [VanillaItems::DIAMOND_PICKAXE()->getTypeId(), VanillaItems::IRON_PICKAXE()->getTypeId(), VanillaItems::GOLDEN_PICKAXE()->getTypeId(), VanillaItems::NETHERITE_PICKAXE()->getTypeId(), VanillaItems::GOLDEN_PICKAXE()->getTypeId(), VanillaItems::WOODEN_PICKAXE()->getTypeId(), VanillaItems::STONE_PICKAXE()->getTypeId()];
                if ($action instanceof SlotChangeAction && ($otherAction = $actions[($i + 1) % 2]) instanceof SlotChangeAction && ($itemClickedWith = $action->getTargetItem())->getTypeId() === StringToItemParser::getInstance()->parse(Utils::getConfig()->getNested("items.blocktrak.type"))->getTypeId() && ($itemClicked = $action->getSourceItem())->getTypeId() !== VanillaItems::AIR()->getTypeId() && in_array($itemClicked->getTypeId(), $ids) && $itemClickedWith->getCount() === 1 && $itemClickedWith->getNamedTag()->getString("blocktrak", "") !== "") {
                    if ($itemClicked->getNamedTag()->getString("blocktrak", "") === "true") {
                        $event->getTransaction()->getSource()->sendMessage("§r§c§l(!) §r§cYou cannot do that!");
                        $transaction->getSource()->getWorld()->addSound($transaction->getSource()->getLocation(), new AnvilFallSound());
                        return;
                    }
                    $event->cancel();

                    if ($itemClicked->getNamedTag()->getString("blocktrak", "") !== "true") {
                        $lore = Utils::getConfig()->getNested("items.blocktrak.settings.lore-display", "&r&7BlockTrak Stats: {stats}");
                        $lore = str_replace("&", "§", $lore);
                        $itemClicked->setLore([$lore]);
                        $itemClicked->getNamedTag()->setString("blocktrak", "true");
                    }

                    Utils::spawnParticleV2($event->getTransaction()->getSource(), "minecraft:villager_happy");
                    $action->getInventory()->setItem($action->getSlot(), $itemClicked);
                    $otherAction->getInventory()->setItem($otherAction->getSlot(), VanillaItems::AIR());
                    $transaction->getSource()->getWorld()->addSound($transaction->getSource()->getLocation(), new XpLevelUpSound(100));
                    return;
                }
            }
        }
    }

    public function onBlockBreak(BlockBreakEvent $event): void
    {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $pitem = $player->getInventory()->getItemInHand();

        if ($pitem instanceof Durable && $item instanceof Durable) {
            $originalDurability = $pitem->getDamage();

            if ($item->getNamedTag()->getString("blocktrak", "") === "true") {
                $lore = $pitem->getLore();
                $loreDisplay = Utils::getConfig()->getNested("items.blocktrak.settings.lore-display", "&r&eBlockTrak Blocks Broken: {stats}");
                $loreDisplay = str_replace("&", "§", $loreDisplay);

                $keyword = Utils::getConfig()->getNested("items.blocktrak.settings.keyword", "&r&eBlockTrak Blocks Broken:");
                $keyword = str_replace("&", "§", $keyword);

                $updatedLore = $this->updateBlockTrakLore($lore, $loreDisplay, $keyword);

                if ($updatedLore !== null) {
                    $pitem->setLore($updatedLore);
                    $task = new ItemUpdateTask($player, $updatedLore, $originalDurability);
                    AdvancedCE::getInstance()->getScheduler()->scheduleDelayedTask($task, 0);
                }
            }
        }
    }

    private function updateBlockTrakLore(array $lore, string $loreDisplay, string $keyword): ?array
    {
        $updatedLore = $lore; // Create a copy of the original lore array
        $existingLineIndex = null; // Track the index of the existing lore line

        foreach ($updatedLore as $index => $line) {
            $line = trim($line);

            if (stripos($line, $keyword) !== false) {
                $existingLineIndex = $index;
                break; // Stop the loop since we found the lore line
            }
        }

        if ($existingLineIndex !== null) {
            $counter = (int) trim(substr($updatedLore[$existingLineIndex], strlen($keyword) + 1));
            $counter++;
            $updatedLore[$existingLineIndex] = $keyword . " " . $counter;
        }

        // Remove any other existing lore lines with the same keyword
        $updatedLore = array_filter($updatedLore, function ($line) use ($keyword) {
            return stripos(trim($line), $keyword) === false;
        });

        return $updatedLore;
    }

}
