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
use Thunder33345\TPRequest\Utilities\Request;

class TPAcceptCommand extends PluginCommand implements CommandExecutor
{
	private $tpRequest, $requestList, $cooldownList, $ignoreList;

	//todo post tp immunity via config
	public function __construct(TPRequest $owner)
	{
		parent::__construct('tpraccept', $owner);
		$this->setDescription('TP Accept Command');
		$this->setUsage('/tpraccept <username|.>');
		$this->setAliases(['tpra']);
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
			$receiver->sendMessage(TPRequest::PREFIX_ERROR() . 'Please use this as a player');
			return true;
		}
		if(count($args) !== 1) return false;
		$user = (string)array_shift($args);

		$cd = $this->cooldownList->get($receiver->getName(), CooldownList::TYPE_ACCEPT);
		if($cd > 0){
			$receiver->sendMessage(TPRequest::PREFIX_COOLDOWN() . "You are on cooldown, Please wait for {$cd} second" . ($cd >= 2 ? 's' : '') . ".");
			return true;
		}

		$tpRequest = null;
		if($user == '.'){
			$last = $this->requestList->getLastFor($receiver->getName());
			$tpRequest = $last;
		} else {
			$matches = $this->requestList->matchRequestFor($receiver->getName(), $user);
			if(count($matches) === 1){
				$tpRequest = $matches[0];
			} elseif(count($matches) > 1) {
				$matchNames = [];
				foreach($matches as $request) $matchNames[] = $request->getSender();
				$receiver->sendMessage(TPRequest::PREFIX_ERROR() . "Multiple matches: \n" . implode(",", $matchNames));
				return true;
			}
		}
		if(!$tpRequest instanceof Request){
			$receiver->sendMessage(TPRequest::PREFIX_ERROR() . 'Cannot find any valid request!');
			return true;
		}
		if(!$tpRequest->isValid()){//unnecessary?
			$receiver->sendMessage(TPRequest::PREFIX_ERROR() . 'Your request has timed out');
			return true;
		}
		$requestSenderName = $tpRequest->getSender();
		$requestSender = $this->tpRequest->getServer()->getPlayer($requestSenderName);

		if(!$requestSender instanceof Player){
			$receiver->sendMessage(TPRequest::PREFIX_ERROR() . "Cannot TP to player " . $requestSenderName . " as they are currently not online.");
			return true;
		}

		$tpTo = $tpRequest->isTpTo();

		if($tpTo){
			$receiver->sendMessage(TPRequest::PREFIX_OUTGOING() . TPRequest::SUFFIX_MODE($tpTo) .
				TextFormat::GREEN . 'You' . TextFormat::RESET . " accepted " . TextFormat::RED . $requestSender->getLowerCaseName() . "'s request");
			$requestSender->sendMessage(TPRequest::PREFIX_INCOMING() . TPRequest::SUFFIX_MODE($tpTo) .
				TextFormat::RED . 'Your' . TextFormat::RESET . ' request has been accepted by ' . TextFormat::GREEN . $receiver->getLowerCaseName());
		} else {
			$receiver->sendMessage(TPRequest::PREFIX_INCOMING() . TPRequest::SUFFIX_MODE($tpTo) .
				TextFormat::GREEN . 'You' . TextFormat::RESET . " accepted " . TextFormat::RED . $requestSender->getLowerCaseName() . "'s request");
			$requestSender->sendMessage(TPRequest::PREFIX_OUTGOING() . TPRequest::SUFFIX_MODE($tpTo) .
				TextFormat::RED . 'Your' . TextFormat::RESET . ' request has been accepted by ' . TextFormat::GREEN . $receiver->getLowerCaseName());
		}
		//$receiver->sendMessage(TPRequest::PREFIX() . "You accepted " . $requestSender->getLowerCaseName() . "'s request");
		//$requestSender->sendMessage(TPRequest::PREFIX() . $receiver->getLowerCaseName() . ' accepted your request');

		if($receiver->getLowerCaseName() === $requestSender->getLowerCaseName()){
			$receiver->sendMessage(TPRequest::PREFIX() . 'Congratulations! You just accepted your own TP request');
		}

		//todo warmup+animation
		$this->requestList->remove($receiver->getName(), $requestSender->getName());
		if($tpTo){
			$requestSender->teleport($receiver);
		} else {
			$receiver->teleport($requestSender);
		}
		$this->cooldownList->add($tpRequest->getSender(), CooldownList::TYPE_ACCEPT, (int)$this->tpRequest->getConfig()->getNested('request.tpAccept', 10));
		//do pre warmup
		//do tp animation task + no move check
		//instant tp if warmup 0
		return true;
	}
}