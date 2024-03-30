<?php
namespace GDO\ChatGPT\Method;

use GDO\ChatGPT\GDO_GPTConversation;
use GDO\ChatGPT\GDO_GPTMessage;
use GDO\ChatGPT\GDT_LanguageModel;
use GDO\ChatGPT\Module_ChatGPT;
use GDO\Core\GDO_DBException;
use GDO\Core\GDT;
use GDO\Core\GDT_Float;
use GDO\Core\GDT_String;
use GDO\Core\GDT_UInt;
use GDO\Dog\Dog;
use GDO\Dog\DOG_Command;
use GDO\Dog\DOG_Message;
use GDO\Dog\DOG_Room;
use GDO\Dog\DOG_User;

final class Open extends DOG_Command
{

    const DETERMINISTIC = 0.0;

    const CREATIVE = 1.0;


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
            GDT_UInt::make('lookback')->initial('15'),
            GDT_Float::make('gpt_temperature')->initial('0.1')->notNull()->min(0)->max(1),
        ];
    }

    public function cfgTemperature(DOG_Room $room): float
    {
        return $this->getConfigValueRoom($room, 'gpt_temperature');
    }

    public function dogExecute(DOG_Message $message, string $model): GDT
    {
        if (GDO_GPTConversation::started($message))
        {
            return $this->error('err_conv_started');
        }
        GDO_GPTConversation::start($message, $model);
        return $this->message('msg_chatgpt_started', [$model]);
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
                if ($conv = GDO_GPTConversation::getConversation($room))
                {
                    $gpt = Module_ChatGPT::instance()->cfgApiDogUser($message->server);
                    $text = $message->text;
                    if ($message->user !== $gpt)
                    {
                        GDO_GPTMessage::log($conv, $message);
                    }
                    if (str_starts_with($text, "{$gpt->renderFullName()}: "))
                    {
                        $reply = $conv->promptTheAI($this->getConfigValueRoom($room, 'lookback'), $this->cfgTemperature($room));
                        if (preg_match('/^ChatGPT[:{}0-9 ]*::(.*)/', $reply, $matches))
                        {
                            $reply = trim($matches[1]);
                        }

                        $room->send("ChatGPT says {$reply}");

                        $message = new DOG_Message(false);
                        $message->user($gpt);
                        $message->room($room);
                        $message->server($room->getServer());
                        $message->text($reply);
                        $room->getServer()->enqueue($message);
//                        Dog::instance()->event('dog_message', $message);
                    }
//                    else
//                    {
//                        GDO_GPTMessage::log($conv, $message);
//                    }
                }
            }
        }
    }

//    /**
//     * @throws GDO_DBException
//     */
//    private function executeCommandForAI(DOG_Message $message, string $reply): void
//    {
//        $msg = new DOG_Message(true);
//        $msg->server($message->server);
//        $msg->room($message->room);
//        $msg->user(Module_ChatGPT::instance()->cfgApiDogUser($message->server));
//        $msg->text($reply);
//        $message->room->send($reply);
//        Dog::instance()->event('dog_message', $msg);
//    }

    /**
     * @throws GDO_DBException
     */
    public function dog_send_to_user(DOG_User $user, string $text): void
    {
        $gpt = Module_ChatGPT::instance()->cfgApiDogUser($user->getServer());
        if ($user === $gpt)
        {
            $room = DOG_Message::$LAST_MESSAGE->room;
            if ($conv = GDO_GPTConversation::getConversation($room))
            {
                $message = new DOG_Message(false);
                $message->room($room);
                $message->server($room->getServer());
                $message->text($text);
                $message->user($room->getServer()->getDog());
                GDO_GPTMessage::log($conv, $message);
            }
        }
    }

    /**
     * @throws GDO_DBException
     */
    public function dog_send_to_room(DOG_Room $room, string $text): void
    {
        if ($this->isEnabled())
        {
            if ($conv = GDO_GPTConversation::getConversation($room))
            {
                $message = new DOG_Message(false);
                $message->room($room);
                $message->server($room->getServer());
                $message->text($text);
                $message->user($room->getServer()->getDog());
                GDO_GPTMessage::log($conv, $message);

                $gpt = Module_ChatGPT::instance()->cfgApiDogUser($room->getServer());

                if (str_starts_with($text, "{$gpt->renderFullName()}: "))
                {
                   $reply = $conv->promptTheAI($this->getConfigValueRoom($room, 'lookback'), $this->cfgTemperature($room));
                    if (preg_match('/^ChatGPT[:{}0-9 ]*::(.*)/', $reply, $matches))
                    {
                        $reply = trim($matches[1]);
                    }
                    $room->send("ChatGPT says {$reply}");
                    $message = new DOG_Message(false);
                    $message->user($gpt);
                    $message->room($room);
                    $message->server($room->getServer());
                    $message->text($reply);
                    $room->getServer()->enqueue($message);
                }
            }
        }
    }


}
