<?php
namespace GDO\ChatGPT\Method;

use GDO\ChatGPT\GDO_GPTConversation;
use GDO\ChatGPT\Module_ChatGPT;
use GDO\Core\GDO_DBException;
use GDO\Core\GDT;
use GDO\Core\GDT_String;
use GDO\Dog\Dog;
use GDO\Dog\DOG_Command;
use GDO\Dog\DOG_Message;
use GDO\UI\GDT_Repeat;
use GDO\User\GDO_User;

/**
 * Let the AI execute dog commands.
 */
final class AGI extends DOG_Command
{

    public function isPrivateMethod(): bool
    {
        return false;
    }

    public function getCLITrigger(): string
    {
        return 'agi';
    }

    public function gdoParameters(): array
    {
        return [
            GDT_String::make('answer')->notNull(),
            GDT_String::make('prompt')->notNull(),
            GDT_Repeat::makeAs('command', GDT_String::make()->label('command'))->notNull(),
        ];
    }

    /**
     * @throws GDO_DBException
     */
    public function dogExecute(DOG_Message $message, string $answer, string $prompt, array $commandParts): GDT
    {
        $room = $message->room;
        if ($conv = GDO_GPTConversation::getConversation($room))
        {
            $gpt = Module_ChatGPT::instance()->cfgApiDogUser($message->server);
            if ($message->user === $gpt)
            {
                $commandParts = array_map(function ($part) {
                    return trim($part, '"$.');
                }, $commandParts);
                $room->send("ChatGPT says {$answer}");
                $line = $room->getTrigger() . implode(',', $commandParts);
                $msg = new DOG_Message();
                $msg->user($gpt);
                $msg->room($room);
                $msg->server($message->server);
                $msg->text($line);
                Dog::instance()->event('dog_message', $msg);
                return $this->message('msg_agi', [$prompt]);
            }
        }
        return $this->error('err_agi');
    }

}
