<?php
namespace GDO\ChatGPT\Method;

use GDO\Core\GDT;
use GDO\Core\GDT_Response;
use GDO\Core\Method;
use GDO\Dog\DOG_Command;

final class Ack extends DOG_Command
{

    public function getCLITrigger(): string
    {
        return 'ack';
    }

    public function dogExecute(): GDT
    {
        return GDT_Response::make();
    }

}
