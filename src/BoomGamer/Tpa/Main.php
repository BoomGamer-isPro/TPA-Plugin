<?php

namespace BoomGamer\Tpa;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener {

    private $pendingRequests = [];
    public function onEnable(): void {
        $this->getLogger()->info("Plugin Enabled!");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if ($command->getName() === "tpa") {
            if (!$sender instanceof Player) {
                $sender->sendMessage(TextFormat::RED . "This can only be used in-game!");
                return false;
            }

            if (empty($args)) {
                $sender->sendMessage(TextFormat::RED . "Please specify a player to request teleportation to.");
                return false;
            }

            $targetName = $args[0];
            $target = $this->getServer()->getPlayerByPrefix($targetName);

            if ($target === null) {
                $sender->sendMessage(TextFormat::Red . "Player not found.");
                return false;
            }

            $this->pendingRequests[$target->getName()] = $sender->getName();
            $target->sendMessage(TextFormat::YELLOW . $sender->getName() . " has requested to teleport to you. Type " . TextFormat::GOLD . "/tpaccept" . TextFormat::YELLOW . "to accept, " . TextFormat::GOLD . "/tpdeny" . TextFormat::YELLOW . " to deny.");
            $sender->sendMessage(TextFormat::YELLOW . "TP request sent to " . $target->getName());
            return true;
        }

        if ($command->getName() === "tpaccept") {
            if (!isset($this->pendingRequests[$sender->getName()])) {
                $sender->sendMessage(TextFormat::Red . "You have no pending requests");
                return false;
            }

            $requesterName = $this->pendingRequests[$sender->getName()];
            $requester = $this->getServer()->getPlayerByPrefix($requesterName);

            if ($requester === null) {
                $sender->sendMessage(TextFormat::RED . "The player is no longer online");
                unset($this->pendingRequests[$sender->getName()]);
                return false;
            }

            $requester->teleport($sender->getPosition());
            $requester->sendMessage(TextFormat::GREEN . "You have been teleported to " . $sender->getName());
            $sender->sendMessage(TextFormat::GREEN . $requester->getName() . " has been teleported to you.");
            unset($this->pendingRequests[$sender->getName()]);
            return true;
        }
        return false;
    }
}
