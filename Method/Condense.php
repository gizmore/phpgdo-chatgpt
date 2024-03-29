<?php
namespace GDO\ChatGPT\Method;

use GDO\ChatGPT\GDO_GPTConversation;
use GDO\ChatGPT\Module_ChatGPT;
use GDO\Core\GDT;
use GDO\Core\GDT_String;
use GDO\Dog\DOG_Command;
use GDO\Dog\DOG_Message;

final class Condense extends DOG_Command
{

    public function getCLITrigger(): string
    {
        return 'condense';
    }

    protected function isPrivateMethod(): bool
    {
        return false;
    }

    public function gdoParameters(): array
    {
        return [
            GDT_String::make('genome')->max(1024)->notNull(),
            GDT_String::make('prompt')->notNull(),

        ];
    }

    public function dogExecute(DOG_Message $message, string $genome, string $prompt): GDT
    {
        $room = $message->room;
        if ($conv = GDO_GPTConversation::getConversation($room))
        {
            $gpt = Module_ChatGPT::instance()->cfgApiDogUser($message->server);
            if ($message->user === $gpt)
            {
                $conv->saveVar('gptc_genome', "\n{$genome}");
                return $message->rply('msg_condensed', [$prompt]);
            }
        }
        return $this->error('err_evolve');

    }

}
