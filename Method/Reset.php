<?php
declare(strict_types=1);
namespace GDO\ChatGPT\Method;

use GDO\CLI\Method\Ekko;

/**
 * Reset your chatgpt history.
 */
final class Reset extends Ekko
{

	public function getCLITrigger(): string
	{
		return 'gpt.reset';
	}



}
