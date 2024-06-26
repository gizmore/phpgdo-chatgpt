<?php
namespace GDO\ChatGPT;

use GDO\Core\GDO;
use GDO\Core\GDO_DBException;
use GDO\Core\GDT_AutoInc;
use GDO\Core\GDT_CreatedAt;
use GDO\Core\GDT_CreatedBy;
use GDO\Core\GDT_Object;
use GDO\Core\GDT_Text;
use GDO\Date\GDT_Timestamp;
use GDO\Date\Time;
use GDO\Dog\DOG_Message;
use GDO\User\GDO_User;

final class GDO_GPTMessage extends GDO
{

    public function gdoCached(): bool
    {
        return false;
    }

    public static function log(GDO_GPTConversation $conversation, DOG_Message $message): void
    {
        echo "GPT logging {$message->text}\n";
        self::blank([
            'gptm_conversation' => $conversation->getID(),
            'gptm_text' => $message->text,
        ])->insert();
    }

    /**
     * @throws GDO_DBException
     * @return self[]
     */
    public static function getLast(GDO_GPTConversation $conversation, int $historyCount): array
    {
        $messages = self::table()->select()
            ->where("gptm_conversation={$conversation->getID()} AND gptm_sent IS NULL")
            ->order("gptm_created DESC")
            ->limit($historyCount)
            ->exec()->fetchAllObjects();

        self::clearMessageQueue($conversation);

        return $messages;
    }

    public static function clearMessageQueue(GDO_GPTConversation $conversation): void
    {
        $now = Time::getDate();
        self::table()->update()->set("gptm_sent='{$now}'")->where("gptm_conversation={$conversation->getID()} AND gptm_sent IS NULL")->exec();
    }

    public function gdoColumns(): array
    {
        return [
            GDT_AutoInc::make('gptm_id'),
            GDT_Object::make('gptm_conversation')->table(GDO_GPTConversation::table()),
            GDT_Text::make('gptm_text')->notNull(),
            GDT_CreatedBy::make('gptm_creator'),
            GDT_CreatedAt::make('gptm_created'),
            GDT_Timestamp::make('gptm_sent'),
        ];
    }

    public function getConversation(): GDO_GPTConversation
    {
        return $this->gdoValue('gptm_conversation');
    }

    /**
     * @throws GDO_DBException
     */
    public function renderChatGPT(): array
    {
        $user = $this->getUser();
        $username = $user->renderUserName();
        $message = $this->getText();
        if ($this->isGPT())
        {
            return [
                'role' => 'assistant',
                'content' => "{$username}::{$message}",
            ];
        }
        elseif ($this->isDog())
        {
            return [
                'role' => 'assistant',
                'content' => "Dog::{$message}",
            ];
        }
        return [
            'role' => 'user',
            'content' => "{$username}::{$message}",
        ];
    }

    public function getUser(): GDO_User
    {
        return $this->gdoValue('gptm_creator');
    }

    private function getText(): string
    {
        return $this->gdoVar('gptm_text');
    }

    /**
     * @throws GDO_DBException
     */
    private function isGPT(): bool
    {
        return $this->getUser() === Module_ChatGPT::instance()->cfgApiDogUser(DOG_Message::$LAST_MESSAGE->server)->getGDOUser();
    }

    private function isDog(): bool
    {
        $dog = $this->getConversation()->getRoom()->getServer()->getDog();
        return $dog->getGDOUser() === $this->getUser();
    }

}
