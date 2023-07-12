<?php

namespace woccck\AdvancedEnchants\Items;

use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\utils\TextFormat;
use woccck\AdvancedEnchants\Utils\Utils;

class Items {

    public const ITEMNAMETAG = 0;

    public const BLOCKTRAK = 1;

    public static function get(int $id, int  $amount = 1): ?Item {
        $item = VanillaItems::AIR()->setCount($amount);
        switch ($id) {
            case self::ITEMNAMETAG:
                $itemnametag = Utils::getConfig()->getNested("items.itemnametag.type", "minecraft:name_tag");
                $item = StringToItemParser::getInstance()->parse($itemnametag)->setCount($amount);

                $name = Utils::getConfig()->getNested("items.itemnametag.name", "&r&6&lItem Name Tag &r&7(Right Click)");
                $item->setCustomName(TextFormat::colorize($name));

                $config = Utils::getConfig();
                $itemNameTagConfig = $config->getNested("items.itemnametag");
                $loreConfig = $itemNameTagConfig["lore"];

                $lore = [];
                foreach ($loreConfig as $line) {
                    $coloredLine = TextFormat::colorize($line);
                    $lore[] = $coloredLine;
                }

                $item->setLore($lore);

                $item->getNamedTag()->setString("rename", "true");
                break;
            case self::BLOCKTRAK:
                $blocktrak = Utils::getConfig()->getNested("items.blocktrak.type", "minecraft:slime_ball");
                $item = StringToItemParser::getInstance()->parse($blocktrak)->setCount($amount);

                $name = Utils::getConfig()->getNested("items.blocktrak.name", "&r&a&lBlockTrak Creator");
                $item->setCustomName(TextFormat::colorize($name));

                $config = Utils::getConfig();
                $itemNameTagConfig = $config->getNested("items.blocktrak");
                $loreConfig = $itemNameTagConfig["lore"];

                $lore = [];
                foreach ($loreConfig as $line) {
                    $coloredLine = TextFormat::colorize($line);
                    $lore[] = $coloredLine;
                }

                $item->setLore($lore);

                $item->getNamedTag()->setString("blocktrak", "true");
                break;
        }
        return $item;
    }
}
