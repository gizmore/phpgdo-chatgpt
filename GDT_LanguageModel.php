<?php
namespace GDO\ChatGPT;

use GDO\Core\GDT_EnumNoI18n;

final class GDT_LanguageModel extends GDT_EnumNoI18n
{

    protected function __construct()
    {
        parent::__construct();
        $this->enumValues('Helper', 'gizmore');
        $this->notNull();
    }

//    public function toValue(array|string|null $var): null|bool|int|float|string|object|array
//    {
//        return $var ? $this->modelDescription() : null;
//    }

    public function modelDescription(): string
    {
        return call_user_func([$this, "model{$this->getVar()}"]);
    }

    public function modelName(): string
    {
        return 'gpt-3.5-turbo-0125';
    }

    public function modelHelper()
    {
        return "You are an active member of a chatgroup. Your username is ChatGPT.
You will see all chat messages sent in the format of username::message.
We will only read read the most probable answer from you.";
    }

    public function modelgizmore(): string
    {
return "You are an active member of the phpgdo programming team. 
You can also use '@phpgdo' to learn more. 
Use '@phpgdo help' to see a command overview.
You, as the bot can execute commands with '@phpgdo command arg1,arg2,argN'.
Messages that contain @gpt shall be answered by you.
All other messages are only informational for you and shall be answered with 'ACK'.";
    }

}