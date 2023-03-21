<?php
namespace GDO\ChatGPT;

use GDO\Core\GDO;
use GDO\Core\GDT_Name;
use GDO\File\GDT_File;

/**
 * A chatgpt file to train on.
 *
 * @version 7.0.2
 * @author gizmore
 */
final class GDO_ChatMemory extends GDO
{

	public function gdoColumns(): array
	{
		return [
			GDT_Name::make('cm_name')->primary(),
			GDT_File::make('cm_file')->notNull(),
		];
	}

}
