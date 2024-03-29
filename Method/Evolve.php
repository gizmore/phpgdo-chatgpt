<?php
namespace GDO\ChatGPT\Method;

use GDO\ChatGPT\GDO_GPTConversation;
use GDO\ChatGPT\Module_ChatGPT;
use GDO\Core\GDO_DBException;
use GDO\Core\GDT;
use GDO\Core\GDT_String;
use GDO\Dog\DOG_Command;
use GDO\Dog\DOG_Message;
use GDO\Dog\DOG_User;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Validator;
use GDO\Form\MethodForm;
use GDO\User\GDO_User;

final class Evolve extends DOG_Command
{

    public function isPrivateMethod(): bool
    {
        return false;
    }

    public function getCLITrigger(): string
    {
        return 'evolve';
    }

//    protected function createForm(GDT_Form $form): void
//    {
//        $form->addFields(
//        );
//    }

    public function gdoParameters(): array
    {
        return [
            GDT_String::make('genome')->max(255)->notNull(),
            GDT_String::make('prompt')->max(255)->notNull(),
        ];
    }

    /**
     * @throws GDO_DBException
     */
    public function dogExecute(DOG_Message $message, string $genome, string $prompt): GDT
    {
        $room = $message->room;
        if ($conv = GDO_GPTConversation::getConversation($room))
        {
            $gpt = Module_ChatGPT::instance()->cfgApiDogUser($message->server);
            if ($message->user === $gpt)
            {
                if (str_contains($conv->getGenome(), $genome))
                {
                    return $this->error('err_already_evolved', [$genome]);
                }
                $conv->addAxiom($genome);
                return $message->rply('msg_evolved', [$prompt]);
            }
        }
        return $this->error('err_evolve');
    }

}

