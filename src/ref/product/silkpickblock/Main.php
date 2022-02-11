<?php

/**            __   _____
 *  _ __ ___ / _| |_   _|__  __ _ _ __ ___
 * | '__/ _ \ |_    | |/ _ \/ _` | '_ ` _ \
 * | | |  __/  _|   | |  __/ (_| | | | | | |
 * |_|  \___|_|     |_|\___|\__,_|_| |_| |_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author  ref-team
 * @link    https://github.com/refteams
 *
 *  &   ／l、
 *    （ﾟ､ ｡ ７
 *   　\、ﾞ ~ヽ   *
 *   　じしf_, )ノ
 *
 * @noinspection PhpUnused
 */

declare(strict_types=1);

namespace ref\product\silkpickblock;

use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerBlockPickEvent;
use pocketmine\item\ItemFactory;
use pocketmine\plugin\PluginBase;

use function count;
use function is_dir;
use function rmdir;
use function scandir;

class Main extends PluginBase{
    /** @throws \ReflectionException */
    public function onEnable() : void{
        /**
         * This is a plugin that does not use data folders.
         * Delete the unnecessary data folder of this plugin for users.
         */
        $dataFolder = $this->getDataFolder();
        if(is_dir($dataFolder) && count(scandir($dataFolder)) <= 2){
            rmdir($dataFolder);
        }

        $this->getServer()->getPluginManager()->registerEvent(PlayerBlockPickEvent::class, static function(PlayerBlockPickEvent $event) : void{
            $player = $event->getPlayer();
            if(!$player->isSneaking()){
                return;
            }

            $inventory = $player->getInventory();
            $block = $event->getBlock();
            $blockId = $block->getId();
            $item = ItemFactory::getInstance()->get(
                $blockId > 255 ? 255 - $blockId : $blockId,
                $block->getMeta()
            );
            $existingSlot = $inventory->first($item);
            if($existingSlot === -1 && $player->hasFiniteResources()){
                return;
            }

            $event->cancel();
            if($existingSlot !== -1){
                if($existingSlot < $inventory->getHotbarSize()){
                    $inventory->setHeldItemIndex($existingSlot);
                }else{
                    $inventory->swap($inventory->getHeldItemIndex(), $existingSlot);
                }
            }else{
                $firstEmpty = $inventory->firstEmpty();
                if($firstEmpty === -1){ //full inventory
                    $inventory->setItemInHand($item);
                }elseif($firstEmpty < $inventory->getHotbarSize()){
                    $inventory->setItem($firstEmpty, $item);
                    $inventory->setHeldItemIndex($firstEmpty);
                }else{
                    $inventory->swap($inventory->getHeldItemIndex(), $firstEmpty);
                    $inventory->setItemInHand($item);
                }
            }
        }, EventPriority::LOWEST, $this);
    }
}
