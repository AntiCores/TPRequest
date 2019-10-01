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

class TPDenyCommand extends PluginCommand implements CommandExecutor
{
	private $tpRequest, $requestList, $cooldownList;

	public function __construct(TPRequest $owner)
	{
		parent::__construct('tpdeny', $owner);
		$this->setDescription('TP Deny Command');
		$this->setUsage('/tpdeny <username>');
		$this->setAliases(['tpd']);
		$this->setPermission('tprequest.deny');
		$this->setPermissionMessage(TPRequest::PREFIX() . 'Insufficient permissions.');
		$this->setExecutor($this);

		$this->tpRequest = $owner;
		$this->requestList = $owner->getRequestList();
		$this->cooldownList = $owner->getCooldownList();
	}

	public function onCommand(CommandSender $receiver, Command $command, string $label, array $args):bool
	{
		if(!$receiver instanceof Player){
			$receiver->sendMessage('Please run this as a player');
			return true;
		}
		if(count($args) !== 1) return false;
		$user = array_shift($args);
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
				$receiver->sendMessage("Multiple matches: \n" . implode(",", $matchNames));
				return true;
			}
		}
		if(!$tpRequest instanceof Request){
			$receiver->sendMessage('Cannot find any valid request!');
			return true;
		}

		$this->cooldownList->add($tpRequest->getSender(), (int)$this->tpRequest->getConfig()->get('tpDeniedCooldown', 45));

		$requestSender = $this->tpRequest->getServer()->getPlayer($tpRequest->getSender());

		if(!$requestSender instanceof Player){
			$requestSender->sendMessage($receiver->getName() . ' has denied your tp request!');
		}
		return true;
	}
}