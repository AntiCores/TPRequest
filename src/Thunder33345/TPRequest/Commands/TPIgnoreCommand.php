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
use Thunder33345\TPRequest\Utilities\IgnoreList;

class TPIgnoreCommand extends PluginCommand implements CommandExecutor
{
	private $tpRequest;
	/**
	 * @var $ignoreList IgnoreList
	 */
	private $ignoreList;

	public function __construct(TPRequest $owner)
	{
		parent::__construct('tprignore', $owner);

		$this->setDescription('TP Ignore Command');
		$this->setUsage('/tprignore  <add[+]|del[-] player>|<list>|<all on|off>|<tip on|off>');
		$this->setAliases(['tpri']);
		$this->setPermission('tprequest.ignore');
		$this->setPermissionMessage(TPRequest::PREFIX() . 'Insufficient permissions.');
		$this->setExecutor($this);
		$this->tpRequest = $owner;
		$this->ignoreList = $owner->getIgnoreList();
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args):bool
	{
		if(!$sender instanceof Player){
			$sender->sendMessage(TPRequest::PREFIX() . "This command can only be used by players.");
			return true;
		}
		if(count($args) < 1) return false;
		$ignoreList = $this->ignoreList;
		switch(strtolower($args[0])){
			case 'list':
				$sender->sendMessage(TPRequest::PREFIX() . 'Ignored users: ' . implode(', ', $this->ignoreList->getIgnore($sender)));
				return true;

			case 'add':
			case '+':
			case 'del':
			case '-':
				if(!isset($args[1]))
					return false;
				$name = $args[1];
				switch(strtolower($args[0])){
					case 'add':
					case '+':
						$this->ignoreList->ignore($sender, $name);
						$sender->sendMessage(TPRequest::PREFIX() . 'Added "' . $name . '" to your ignore list');
						break;
					case 'del':
					case '-':
						$this->ignoreList->unIgnore($sender, $name);
						$sender->sendMessage(TPRequest::PREFIX() . 'Removed "' . $name . '" from your ignore list');
						break;
					default:
						return false;
				}
				return true;
				break;

			case 'all':
				if(!isset($args[1]))
					return false;
				switch(strtolower($args[1])){
					case 'on':
						$ignoreList->setIgnoreAll($sender, true);
						$sender->sendMessage(TPRequest::PREFIX() . 'Enabled ignoring all mode, all TP request will now be ignored.');
						break;
					case 'off':
						$ignoreList->setIgnoreAll($sender, false);
						$sender->sendMessage(TPRequest::PREFIX() . 'Disabled ignoring all mode, now you can receive TP request.');
						break;
					default:
						return false;
				}
				return true;
				break;

			case 'tip':
				if(!isset($args[1]))
					return false;
				switch(strtolower($args[1])){
					case 'on':
						$ignoreList->setAsTip($sender, true);
						$sender->sendMessage(TPRequest::PREFIX() . 'Enabled ignore as tip, From now on ignored request will show up as tip instead of messages.');
						break;
					case 'off':
						$ignoreList->setAsTip($sender, false);
						$sender->sendMessage(TPRequest::PREFIX() . 'Disabled ignore as tip, From now on all ignored request wont show at all.');
						break;
					default:
						return false;
				}
				return true;
				break;
			default:
				return false;
		}
	}
}