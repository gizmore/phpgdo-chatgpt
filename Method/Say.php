<?php
declare(strict_types=1);
namespace GDO\ChatGPT\Method;

use GDO\ChatGPT\Module_ChatGPT;
use GDO\CLI\Method\Ekko;
use GDO\Core\GDT;
use GDO\Form\GDT_Form;

/**
 * Reset your chatgpt history.
 */
final class Say extends Ekko
{

	public function getCLITrigger(): string
	{
		return 'gpt.say';
	}

	public function execute(): GDT
	{
		$gpt = Module_ChatGPT::instance()->getClient();
	}

}
