<?php

/**
 *  ____                           _   _  ___
 * |  _ \ _ __ ___  ___  ___ _ __ | |_| |/ (_)_ __ ___
 * | |_) | '__/ _ \/ __|/ _ \ '_ \| __| ' /| | '_ ` _ \
 * |  __/| | |  __/\__ \  __/ | | | |_| . \| | | | | | |
 * |_|   |_|  \___||___/\___|_| |_|\__|_|\_\_|_| |_| |_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author  PresentKim (debe3721@gmail.com)
 * @link    https://github.com/PresentKim
 * @license https://www.gnu.org/licenses/lgpl-3.0 LGPL-3.0 License
 *
 *   (\ /)
 *  ( . .) ♥
 *  c(")(")
 *
 * @noinspection PhpUnused
 */

declare(strict_types=1);

namespace kim\present\silkpickblock;

use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerBlockPickEvent;
use pocketmine\item\ItemBlock;
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
            $item = new ItemBlock($block);
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
