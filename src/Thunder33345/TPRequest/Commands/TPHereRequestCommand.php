<?php
declare(strict_types=1);
/** Created By Thunder33345 **/

namespace Thunder33345\TPRequest\Commands;

use pocketmine\command\PluginCommand;
use Thunder33345\TPRequest\TPRequest;


class TPHereRequestCommand extends PluginCommand
{
	public function __construct(TPRequest $owner, TPRequestCommand $requestCommand)
	{
		parent::__construct('tphererequest', $owner);
		$this->setDescription('Send a TP Here Request Command');
		$this->setUsage('/tphererequest <username>');
		$this->setAliases(['tphr']);
		$this->setPermission('tprequest.request');
		$this->setPermissionMessage(TPRequest::PREFIX() . 'Insufficient permissions.');
		$this->setExecutor($requestCommand);
	}
}