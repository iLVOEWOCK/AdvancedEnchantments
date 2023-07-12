<?php

namespace woccck\AdvancedEnchants\Tasks;

use pocketmine\item\Durable;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\item\Item;

class ItemUpdateTask extends Task
{
    private Player $player;
    private array $modifiedLore;
    private int $originalDurability;

    public function __construct(Player $player, array $modifiedLore, int $originalDurability)
    {
        $this->player = $player;
        $this->modifiedLore = $modifiedLore;
        $this->originalDurability = $originalDurability;
    }

    public function onRun(): void
    {
        $updatedItem = $this->player->getInventory()->getItemInHand();
        $updatedItem->setLore($this->modifiedLore);

        if ($updatedItem instanceof Durable) {
            $currentDurability = $updatedItem->getMaxDurability() - $updatedItem->getDamage();
            $originalDurability = $this->originalDurability;

            $updatedDurability = $originalDurability + ($currentDurability - $originalDurability) + 1;

            $updatedDurability = min($updatedDurability, $updatedItem->getMaxDurability());

            $this->player->getInventory()->setItemInHand($updatedItem);
            $updatedItem->setDamage($updatedItem->getMaxDurability() - $updatedDurability);
        }
    }
}

