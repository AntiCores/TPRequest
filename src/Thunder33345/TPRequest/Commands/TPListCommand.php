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

class TPListCommand extends PluginCommand implements CommandExecutor
{
	private $requestList;

	public function __construct(TPRequest $owner)
	{
		parent::__construct('tplist', $owner);
		$this->setDescription('TP List Command');
		$this->setUsage('/tplist');
		$this->setAliases(['tpl']);
		$this->setPermission('tprequest.list');
		$this->setPermissionMessage(TPRequest::PREFIX() . 'Insufficient permissions.');
		$this->setExecutor($this);

		$this->requestList = $owner->getRequestList();
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args):bool
	{
		if(!$sender instanceof Player){
			$sender->sendMessage('Please use this as a player');
			return true;
		}

		$for = $this->requestList->getAllFor($sender->getName());
		$forTxt = '';
		$forExp = '';
		foreach($for as $request){
			if(!$request->isValid()){
				$forExp .= TPRequest::SUFFIX_MODE($request->isTpaTo()) . 'From:' . $request->getSender() . ' Expired for:' . $request->getLastFor() . "\n";;
				continue;
			}
			$forTxt .= TPRequest::SUFFIX_MODE($request->isTpaTo()) . 'From:' . $request->getSender() . ' Timeout:' . $request->getLastFor() . "\n";
		}

		$by = $this->requestList->getAllBy($sender->getName());
		$byTxt = '';
		$byExp = '';
		foreach($by as $request){
			if(!$request->isValid()){
				$byExp .= TPRequest::SUFFIX_MODE($request->isTpaTo()) . 'To:' . $request->getReceiver() . ' Expired for:' . $request->getLastFor() . "\n";;
				continue;
			}
			$byTxt .= TPRequest::SUFFIX_MODE($request->isTpaTo()) . 'To:' . $request->getReceiver() . ' Timeout:' . $request->getLastFor() . "\n";
		}

		$sender->sendMessage("Pending Incoming Request");
		$sender->sendMessage($forTxt);
		$sender->sendMessage("Pending Outgoing Request");
		$sender->sendMessage($byTxt);

		$sender->sendMessage("Expired Incoming Request");
		$sender->sendMessage($forExp);
		$sender->sendMessage("Expired Outgoing Request");
		$sender->sendMessage($byExp);

		$this->requestList->cleanInvalidFor($sender->getName());

		return true;
	}
}