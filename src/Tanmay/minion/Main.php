<?php

namespace Tanmay\minion;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\item\Item;
use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener {

    private $minionConfig;

    public function onEnable(): void {
        $this->getLogger()->info("Minion plugin has been enabled!");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        // Load or create minion configuration
        $this->minionConfig = new Config($this->getDataFolder() . "minion.yml", Config::YAML, []);
    }

    public function onDisable(): void {
        $this->getLogger()->info("Minion plugin has been disabled!");
    }

    public function onPlayerInteract(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        $item = $event->getItem();

        if ($item->getId() === Item::STICK) {
            $this->spawnMinion($player, "iron"); // Change "iron" to "gold" or "diamond" for different ore types
        }
    }

    public function onEntityDamage(EntityDamageEvent $event) {
        $entity = $event->getEntity();

        if ($entity instanceof Human && $entity->namedtag->hasTag("minion")) {
            $event->setCancelled(); // Cancel damage to minions
        }
    }

    private function spawnMinion(Human $owner, string $minionType) {
        $nbt = Entity::createBaseNBT($owner);
        $minion = new Human($owner->getLevel(), $nbt);
        $minion->setNameTag(TextFormat::YELLOW . ucfirst($minionType) . " Ore Minion");
        $minion->setNameTagVisible(true);
        $minion->setNameTagAlwaysVisible(true);
        $minion->setHealth(20);
        $minion->setOwner($owner->getName()); // Assign owner to the minion
        $minion->namedtag->setTag("minion", true); // Mark the entity as a minion

        // Add any additional properties or effects to the minion
        $minion->addEffect(Effect::getEffect(Effect::SPEED)->setAmplifier(1)->setDuration(999999)); // Example: Speed effect

        $minion->spawnToAll();

        $this->getScheduler()->scheduleRepeatingTask(new OreMinionTask($this, $minion, $minionType), 20 * 5); // Every 5 seconds
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($command->getName() === "minionegg") {
            if ($sender instanceof Human) {
                $minionType = array_shift($args) ?? "iron"; // Default to iron if no type is specified
                $this->spawnMinion($sender, $minionType);
                $sender->sendMessage("You received a {$minionType} Ore Minion Egg!");
            } else {
                $sender->sendMessage("This command can only be used by players in-game.");
            }
            return true;
        }
        return false;
    }
}
