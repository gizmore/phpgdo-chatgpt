<?php
namespace GDO\ChatGPT;

use GDO\Core\GDO;
use GDO\Core\GDO_DBException;
use GDO\Core\GDT_AutoInc;
use GDO\Core\GDT_CreatedAt;
use GDO\Core\GDT_CreatedBy;
use GDO\Core\GDT_DeletedAt;
use GDO\Core\GDT_DeletedBy;
use GDO\Core\GDT_Secret;
use GDO\Core\GDT_Text;
use GDO\Core\GDT_UInt;
use GDO\Date\Time;
use GDO\Dog\DOG_Message;
use GDO\Dog\DOG_Room;
use GDO\Dog\GDT_Room;
use GDO\UI\GDT_Title;
use OpenAI\Client;

final class GDO_Conversation extends GDO
{

    public static function started(DOG_Message $message): bool
    {
        return self::getConversation($message->room) !== null;
    }

    /**
     * @throws GDO_DBException
     */
    public static function getConversation(DOG_Room $room): ?self
    {
        return self::table()->getWhere("gptc_room={$room->getID()} AND gptc_deleted IS NULL");
    }

    public static function start(DOG_Message $message, string $model): self
    {
        $module = Module_ChatGPT::instance();
        $openai = $module->getClient();
//        $chat = $openai->chat()->create()->create()
//        $conversation = $openai->completions()->create([
//            'model' => 'text-davinci-002',
//            'messages' => [
//                ['role' => 'system', 'content' => $model]
//            ]]);
        return self::blank([
//            'gptc_conversation_id' => $conversation->id,
            'gptc_room' => $message->room->getID(),
            'gptc_model' => $model,
        ])->insert();

    }

    public function gdoColumns(): array
    {
        return [
            GDT_AutoInc::make('gptc_id'),
            GDT_Secret::make('gptc_conversation_id')->max(32),
            GDT_Room::make('gptc_room'),
            GDT_Title::make('gptc_title')->max(256)->notNull(false),
            GDT_LanguageModel::make('gptc_model'),
//            GDT_UInt::make('gptc_history_count')->notNull()->bytes(2)->initial('4'),
            GDT_CreatedAt::make('gptc_created'),
            GDT_CreatedBy::make('gptc_creator'),
            GDT_DeletedAt::make('gptc_deleted'),
            GDT_DeletedBy::make('gptc_deletor'),
        ];
    }

    public function getCID(): string
    {
        return $this->getCID();
    }

    public function getAI(): Client
    {
        return Module_ChatGPT::instance()->getClient();
    }


    /**
     * @throws GDO_DBException
     */
    public function close(): bool
    {
        GDO_GPTMessage::clearMessageQueue($this);
        $this->delete();
        return true;
    }

    /**
     * @throws GDO_DBException
     */
    public function promptTheAI(int $historyCount): string
    {
        $openai = $this->getAI();
        $messages = [
            ['role' => 'system', 'content' => $this->getAIModel()],
        ];

        $history = GDO_GPTMessage::getLast($this, $historyCount);
        foreach (array_reverse($history) as $message)
        {
            $messages[] = $message->renderChatGPT();
        }

        $response = $openai->chat()->create([
            'model' => $this->getAIModelName(),
            'messages' => $messages,
        ]);

        if ($aitext = @$response['choices'][0]['message']['content'])
        {
            GDO_GPTMessage::blank([
                'gptm_conversation' => $this->getID(),
                'gptm_text' => $aitext,
                'gptm_creator' => Module_ChatGPT::instance()->cfgApiUser()->getID(),
                'gptm_sent' => Time::getDate(),
            ])->insert();
        }

        return $aitext;
    }

    private function getAIModelColumn(): GDT_LanguageModel
    {
        return $this->gdoColumn('gptc_model');
    }

    private function getAIModel(): ?string
    {
        return $this->getAIModelColumn()->modelDescription();
    }

    private function getAIModelName(): string
    {
        return $this->getAIModelColumn()->modelName();
    }

}
