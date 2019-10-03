<?php
declare(strict_types=1);
/** Created By Thunder33345 **/

namespace Thunder33345\TPRequest\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use Thunder33345\TPRequest\TPRequest;
use Thunder33345\TPRequest\Utilities\CooldownList;


class TPRequestCommand extends PluginCommand implements CommandExecutor
{
	private $tpRequest, $requestList, $cooldownList, $ignoreList;

	public function __construct(TPRequest $owner)
	{
		parent::__construct('tprequest', $owner);
		$this->setDescription('Send a TP Request Command');
		$this->setUsage('/tprequest <username>');
		$this->setAliases(['tpr']);
		$this->setPermission('tprequest.request');
		$this->setPermissionMessage(TPRequest::PREFIX() . 'Insufficient permissions.');
		$this->setExecutor($this);

		$this->tpRequest = $owner;
		$this->requestList = $owner->getRequestList();
		$this->cooldownList = $owner->getCooldownList();
		$this->ignoreList = $owner->getIgnoreList();
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args):bool
	{
		if(!$sender instanceof Player){
			$sender->sendMessage(TPRequest::PREFIX_ERROR() . "This command can only be used by players.");
			return true;
		}

		if(count($args) !== 1) return false;
		$cd = $this->cooldownList->get($sender->getName(), CooldownList::TYPE_REQUEST);
		if($cd > 0){
			$sender->sendMessage(TPRequest::PREFIX_COOLDOWN() . "You are on cooldown, Please wait for {$cd} second" . ($cd >= 2 ? 's' : '') . ".");
			return true;
		}

		$target = $this->tpRequest->getServer()->getPlayer($args[0]);
		if(!$target instanceof Player){
			$sender->sendMessage(TPRequest::PREFIX_ERROR() . "Cannot find requested player \"{$args[0]}\", Make sure they are online.2");
			return true;
		}

		// Normal teleport request
		// sender gets tp to receiver
		$tpTo = true;
		$tpSender = TPRequest::PREFIX_OUTGOING() . TPRequest::SUFFIX_MODE($tpTo) .
			TextFormat::GREEN . 'You' . TextFormat::RESET . ' sent a teleport request to ' . TextFormat::RED . $target->getLowerCaseName();
		$tpReceiver = TPRequest::PREFIX_INCOMING() . TPRequest::SUFFIX_MODE($tpTo) .
			TextFormat::RED . 'You' . TextFormat::RESET . ' received a teleport request from ' . TextFormat::GREEN . $sender->getLowerCaseName();
		if($label == 'tphererequest'){
			// This means it's a teleport here request
			// receiver get teleport to sender
			$tpTo = false;
			$tpSender = TPRequest::PREFIX_INCOMING() . TPRequest::SUFFIX_MODE($tpTo) .
				TextFormat::RED . 'You' . TextFormat::RESET . ' sent a teleport here request for ' . TextFormat::GREEN . $target->getLowerCaseName();
			$tpReceiver = TPRequest::PREFIX_OUTGOING() . TPRequest::SUFFIX_MODE($tpTo) .
				TextFormat::GREEN . 'You' . TextFormat::RESET . ' received a teleport there request from ' . TextFormat::RED . $sender->getLowerCaseName();
		}

		$this->requestList->add($target->getName(), $sender->getName(), $tpTo);
		$this->cooldownList->add($sender->getName(), CooldownList::TYPE_REQUEST, (int)$this->tpRequest->getConfig()->getNested('request.requestCooldown', 20));

		$sender->sendMessage($tpSender);

		if($this->ignoreList->isIgnored($target, $sender) OR $this->ignoreList->getIgnoreAll($target)){//true, the target added sender as ignored or ignore all is on
			if($this->ignoreList->isAsTip($target)){//notify as tip
				$target->sendTip($tpReceiver);
			}
		} else {//not ignored
			$target->sendMessage($tpReceiver);
		}
		return true;
	}
}