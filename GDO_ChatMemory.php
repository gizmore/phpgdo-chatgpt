<?php
namespace GDO\ChatGPT;

use GDO\Core\GDO;
use GDO\File\GDT_File;

/**
 * A chatgpt file to train on.
 * 
 * @author gizmore
 * @version 7.0.2
 */
final class GDO_ChatMemory extends GDO
{
	public function gdoColumns(): array
	{
		return [
			GDT_File::make()
		];
	}

}
