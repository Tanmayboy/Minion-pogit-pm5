<?php

namespace Tanmay\minion;

use pocketmine\scheduler\Task;
use pocketmine\item\Item;

class OreMinionTask extends Task {

    private $plugin;
    private $minion;
    private $minionType;

    public function __construct(Main $plugin, Human $minion, string $minionType) {
        $this->plugin = $plugin;
        $this->minion = $minion;
        $this->minionType = $minionType;
    }

    public function onRun(int $currentTick) {
        $minionInventory = $this->minion->getInventory();

        // Simulate ore generation by adding a block/item to the minion's inventory
        $minionInventory->addItem(Item::get($this->getOreItemId()));

        $ownerName = $this->minion->getOwner();
        $owner = $this->plugin->getServer()->getPlayer($ownerName);

        if ($owner !== null) {
            $owner->sendMessage("Your {$this->minionType} Ore Minion generated an ore!");
        }
    }

    private function getOreItemId(): int {
        switch ($this->minionType) {
            case "iron":
                return Item::IRON_ORE;
            case "gold":
                return Item::GOLD_ORE;
            case "diamond":
                return Item::DIAMOND_ORE;
            default:
                return Item::STONE;
        }
    }
}
