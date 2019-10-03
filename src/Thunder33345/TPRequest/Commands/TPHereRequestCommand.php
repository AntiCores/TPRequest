<?php
declare(strict_types=1);
/** Created By Thunder33345 **/

namespace Thunder33345\TPRequest\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use Thunder33345\TPRequest\TPRequest;


class TPHereRequestCommand extends PluginCommand implements CommandExecutor
{
	private $requestCommand;
	public function __construct(TPRequest $owner, TPRequestCommand $requestCommand)
	{
		parent::__construct('tphererequest', $owner);
		$this->setDescription('Send a TP Here Request Command');
		$this->setUsage('/tphererequest <username>');
		$this->setAliases(['tphr','tprh']);
		$this->setPermission('tprequest.request');
		$this->setPermissionMessage(TPRequest::PREFIX() . 'Insufficient permissions.');
		$this->setExecutor($this);
		$this->requestCommand = $requestCommand;
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args):bool
	{
		return $this->requestCommand->onCommand($sender,$command,'tphererequest',$args);//Lol
	}
}