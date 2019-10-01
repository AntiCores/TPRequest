<?php
declare(strict_types=1);
/** Created By Thunder33345 **/

namespace Thunder33345\TPRequest\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use Thunder33345\TPRequest\TPRequest;
use Thunder33345\TPRequest\Utilities\Request;

class TPAcceptCommand extends PluginCommand implements CommandExecutor
{
	private $tpRequest, $requestList, $cooldownList, $ignoreList;

	//todo . to accept last request
	//todo allow using username
	//todo allow using pin
	//todo post tp immunity via config
	//todo easter egg when tping to yourself
	public function __construct(TPRequest $owner)
	{
		parent::__construct('tpaccept', $owner);
		$this->setDescription('TP Accept Command');
		$this->setUsage('/tpaccept <username|.>');
		$this->setAliases(['tpa']);
		$this->setPermission('tprequest.accept');
		$this->setPermissionMessage(TPRequest::PREFIX() . 'Insufficient permissions.');
		$this->setExecutor($this);

		$this->tpRequest = $owner;
		$this->requestList = $owner->getRequestList();
		$this->cooldownList = $owner->getCooldownList();
		$this->ignoreList = $owner->getIgnoreList();
	}

	public function onCommand(CommandSender $receiver, Command $command, string $label, array $args):bool
	{
		if(!$receiver instanceof Player){
			$receiver->sendMessage('Please use this as a player');
			return true;
		}
		if(count($args) !== 1) return false;
		$user = (string)array_shift($args);

		$tpRequest = null;
		if($user == '.'){
			$last = $this->requestList->getLastFor($receiver->getName());
			$tpRequest = $last;
		} else{
			$matches = $this->requestList->matchRequestFor($receiver->getName(), $user);
			if(count($matches) === 1){
				$tpRequest = $matches[0];
			} elseif(count($matches) > 1){
				$matchNames = [];
				foreach($matches as $request) $matchNames[] = $request->getSender();
				$receiver->sendMessage("Multiple matches: \n" . implode(",", $matchNames));
				return true;
			}
		}
		if(!$tpRequest instanceof Request){
			$receiver->sendMessage('Cannot find any valid request!');
			return true;
		}
		if(!$tpRequest->isValid()){//unnecessary?
			$receiver->sendMessage('Your request has timed out');
			return true;
		}
		$requestSenderName = $tpRequest->getSender();
		$requestSender = $this->tpRequest->getServer()->getPlayer($requestSenderName);

		if(!$requestSender instanceof Player){
			$receiver->sendMessage("Cannot TP to player " . $requestSenderName . " as they are currently not online.");
			return true;
		}

		//todo warmup+animation
		$this->requestList->remove($receiver->getName(), $requestSender->getName());
		if($tpRequest->isTpaTo()){
			$requestSender->teleport($receiver);
		} else{
			$receiver->teleport($requestSender);
		}
		//do pre warmup
		//do tp animation task + no move check
		//instant tp if warmup 0
		return true;
	}
}