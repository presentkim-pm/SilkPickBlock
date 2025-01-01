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
 * @author       PresentKim (debe3721@gmail.com)
 * @link         https://github.com/PresentKim
 * @license      https://www.gnu.org/licenses/lgpl-3.0 LGPL-3.0 License
 *
 *   (\ /)
 *  ( . .) ♥
 *  c(")(")
 *
 * @noinspection PhpUnused
 */

declare(strict_types=1);

namespace kim\present\silkpickblock;

use pocketmine\block\tile\Tile;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerBlockPickEvent;
use pocketmine\item\ItemBlock;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;

use function count;
use function is_dir;
use function rmdir;
use function scandir;

class Main extends PluginBase implements Listener{

    public function onEnable() : void{
        /**
         * This is a plugin that does not use data folders.
         * Delete the unnecessary data folder of this plugin for users.
         */
        $dataFolder = $this->getDataFolder();
        if(is_dir($dataFolder) && count(scandir($dataFolder)) <= 2){
            rmdir($dataFolder);
        }

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    /**
     * @handleCancelled
     * @priority LOWEST
     */
    public function onPlayerBlockPickEvent(PlayerBlockPickEvent $event) : void{
        $player = $event->getPlayer();
        if(!$player->isSneaking()){
            return;
        }

        $inventory = $player->getInventory();
        $block = $event->getBlock();

        /**
         * Find the appropriate pick item
         * If it failed, return without further action
         */
        // Create a new ItemBlock instance based on the provided block
        $pickItem = new ItemBlock($block);
        if($player->hasFiniteResources()){
            $pickSlot = $inventory->first($pickItem);

            // If the pick item is not found in the inventory, try finding the default pick item
            if($pickSlot === -1){
                $pickItem = $block->getPickedItem();
                $pickSlot = $inventory->first($pickItem);
            }

            // If the pick item is still not found in the inventory, return without further action
            if($pickSlot === -1){
                return;
            }
        }else{
            // If the provided block is a tile object, store tile data into the item's NBT
            $pos = $block->getPosition();
            $tile = $pos->getWorld()->getTile($pos);
            if($tile instanceof Tile){
                $nbt = $tile->getCleanedNBT();
                if($nbt instanceof CompoundTag){
                    $pickItem->setCustomBlockData($nbt);
                    $pickItem->setLore(["+(DATA)"]);
                }
            }

            // If player doesn't have pick item, give item to players
            $pickSlot = $inventory->first($pickItem);
            if($pickSlot === -1){
                $firstEmpty = $inventory->firstEmpty();
                if($firstEmpty === -1){
                    $pickSlot = $inventory->getHeldItemIndex();
                }else{
                    $pickSlot = $firstEmpty;
                }
                $inventory->setItem($pickSlot, $pickItem);
            }
        }

        /**
         * Move the pick item to the appropriate slot in the player's inventory
         * If the pick item is in the hotbar, update the hotbar index to the item's slot.
         * Otherwise, swap the current hotbar item with the pick item.
         */
        if($pickSlot < $inventory->getHotbarSize()){
            $inventory->setHeldItemIndex($pickSlot);
        }else{
            $inventory->swap($inventory->getHeldItemIndex(), $pickSlot);
        }

        $event->cancel();
    }
}
