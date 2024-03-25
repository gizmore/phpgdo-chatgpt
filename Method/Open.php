<?php
namespace GDO\ChatGPT\Method;

use GDO\ChatGPT\GDO_Conversation;
use GDO\ChatGPT\GDO_GPTMessage;
use GDO\ChatGPT\GDT_LanguageModel;
use GDO\Core\GDO_DBException;
use GDO\Core\GDT;
use GDO\Core\GDT_String;
use GDO\Core\GDT_UInt;
use GDO\Dog\DOG_Command;
use GDO\Dog\DOG_Message;

final class Open extends DOG_Command
{

    public function getCLITrigger(): string
    {
        return 'gpt.listen';
    }

    public function gdoParameters(): array
    {
        return [
            GDT_LanguageModel::make('model'),
        ];
    }

    public function getConfigRoom(): array
    {
        return [
            GDT_UInt::make('lookback')->initial('4'),
            GDT_String::make('gpt_trigger')->initial('@gpt'),
        ];
    }

    public function cfgTrigger(DOG_Message $message): string
    {
        return $this->getConfigValueRoom($message->room, 'gpt_trigger');
    }

    public function dogExecute(DOG_Message $message, string $model): GDT
    {
        if (GDO_Conversation::started($message))
        {
            return $this->error('err_conv_started');
        }
        GDO_Conversation::start($message, $model);
        return $this->message('msg_chatgpt_started');
    }

    /**
     * @throws GDO_DBException
     */
    public function dog_message(DOG_Message $message): void
    {
        if ($room = $message->room)
        {
            if ($this->isEnabled())
            {
                if ($conv = GDO_Conversation::getConversation($room))
                {
                    $text = $message->text;
                    GDO_GPTMessage::log($conv, $message);
                    if (str_contains($text, $this->cfgTrigger($message)))
                    {
                        $message->reply($conv->promptTheAI($this->getConfigValueRoom($room, 'lookback')));
                    }
                }
            }
        }
    }

}
