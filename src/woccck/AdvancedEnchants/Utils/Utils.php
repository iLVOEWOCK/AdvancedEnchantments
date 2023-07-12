<?php

namespace woccck\AdvancedEnchants\Utils;

use muqsit\customsizedinvmenu\CustomSizedInvMenu;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\SpawnParticleEffectPacket;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use woccck\AdvancedEnchants\AdvancedCE;

class Utils {

    public static function getConfig(): Config {
        return new Config(AdvancedCE::getInstance()->getDataFolder() . "config.yml", Config::YAML);
    }

    public static function getMessagaesConfig(): Config {
        return new Config(AdvancedCE::getInstance()->getDataFolder() . "messages.yml", Config::YAML);
    }

    public static function getEnchanterConfig(): Config {
        return new Config(AdvancedCE::getInstance()->getDataFolder() . "menu/enchanter.yml", Config::YAML);
    }

    /**
     * @param Entity $player
     * @param string $sound
     * @param int $volume
     * @param int $pitch
     * @param int $radius
     */
    public static function playSound(Entity $player, string $sound, $volume = 1, $pitch = 1, int $radius = 5): void
    {
        foreach ($player->getWorld()->getNearbyEntities($player->getBoundingBox()->expandedCopy($radius, $radius, $radius)) as $p) {
            if ($p instanceof Player) {
                if ($p->isOnline()) {
                    $spk = new PlaySoundPacket();
                    $spk->soundName = $sound;
                    $spk->x = $p->getLocation()->getX();
                    $spk->y = $p->getLocation()->getY();
                    $spk->z = $p->getLocation()->getZ();
                    $spk->volume = $volume;
                    $spk->pitch = $pitch;
                    $p->getNetworkSession()->sendDataPacket($spk);
                }
            }
        }
    }

    public static function spawnParticleV2(Entity $entity, string $particleName): void
    {
        $particleCount = 10;
        $radius = 0.5;

        $position = $entity->getEyePos();

        for ($i = 0; $i < $particleCount; $i++) {
            $offsetX = mt_rand(-$radius * 100, $radius * 100) / 100;
            $offsetY = mt_rand(-$radius * 100, $radius * 100) / 100;
            $offsetZ = mt_rand(-$radius * 100, $radius * 100) / 100;

            $particleX = $position->getX() + $offsetX;
            $particleY = $position->getY() + $offsetY;
            $particleZ = $position->getZ() + $offsetZ;

            $particlePosition = new Vector3($particleX, $particleY, $particleZ);
            self::spawnParticleNear($entity, $particleName, $particlePosition);
        }
    }

    public static function spawnParticleNear(Entity $entity, string $particleName, Vector3 $position, int $radius = 5): void
    {
        $packet = new SpawnParticleEffectPacket();
        $packet->particleName = $particleName;
        $packet->position = $position;

        foreach ($entity->getWorld()->getNearbyEntities($entity->getBoundingBox()->expandedCopy($radius, $radius, $radius)) as $player) {
            if ($player instanceof Player && $player->isOnline()) {
                $player->getNetworkSession()->sendDataPacket($packet);
            }
        }
    }

    public static function getEnchanterGUI(Player $player): void
    {
        $enchantergui = CustomSizedInvMenu::create(18);
        $enchantertitle = Utils::getEnchanterConfig()->get("enchanter-title", "Server Enchanter");
        $enchantergui->setName(TextFormat::colorize($enchantertitle));
        $enchanterinv = $enchantergui->getInventory();

        $enchanterinv->setItem(2, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::WHITE())->asItem()->setCustomName(" ")->setLore([" "]));
        $enchanterinv->setItem(3, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::LIME())->asItem()->setCustomName(" ")->setLore([" "]));
        $enchanterinv->setItem(4, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::CYAN())->asItem()->setCustomName(" ")->setLore([" "]));
        $enchanterinv->setItem(5, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::YELLOW())->asItem()->setCustomName(" ")->setLore([" "]));
        $enchanterinv->setItem(6, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::ORANGE())->asItem()->setCustomName(" ")->setLore([" "]));
        $enchanterinv->setItem(13, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::PINK())->asItem()->setCustomName(" ")->setLore([" "]));
        $enchanterinv->setItem(9, VanillaBlocks::ANVIL()->asItem()->setCustomName("§r§fOpen §l§bTinkerer")->setLore(["§r§7Tinker books for Magic Dusts and EXP.", "", "§r§l§bClick to visit"]));
        $enchanterinv->setItem(17, VanillaBlocks::END_PORTAL_FRAME()->asItem()->setCustomName("§r§fOpen §l§bAlchemist")->setLore(["§r§7Combine books and Magic Dusts.", "", "§r§l§bClick to visit"]));

        $enchantergui->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction): void {

        }));

        self::playSound($player, self::getEnchanterConfig()->get("open-sound", "mob.enderdragon.flap"));
        $enchantergui->send($player);
    }
}
