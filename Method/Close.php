<?php
namespace GDO\ChatGPT\Method;

use GDO\ChatGPT\GDO_GPTConversation;
use GDO\Core\GDO_DBException;
use GDO\Core\GDT;
use GDO\Dog\DOG_Command;
use GDO\Dog\DOG_Message;

final class Close extends DOG_Command
{

    public function getCLITrigger(): string
    {
        return 'gpt.close';
    }


    /**
     * @throws GDO_DBException
     */
    public function dogExecute(DOG_Message $message): GDT
    {
        if (!($conv = GDO_GPTConversation::getConversation($message->room)))
        {
            return $this->error('err_gpt_not_open');
        }
        $conv->close();
        return $this->message('msg_gpt_closed');
    }

}
