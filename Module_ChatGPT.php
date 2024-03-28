<?php
namespace GDO\ChatGPT;

use GDO\Core\GDO_DBException;
use GDO\Core\GDO_Module;
use GDO\Core\GDT_Secret;
use GDO\Core\WithComposer;
use GDO\Dog\Connector\Bash;
use GDO\Dog\DOG_Message;
use GDO\Dog\DOG_Server;
use GDO\Dog\DOG_User;
use GDO\User\GDO_User;
use GDO\User\GDT_User;
use GDO\User\GDT_UserType;
use OpenAI\Client;
use OpenAI;
/**
 * ChatGPT bindings.
 *
 * @version 7.0.2
 * @author gizmore
 */
final class Module_ChatGPT extends GDO_Module
{

	use WithComposer;

	private Client $client;

    private OpenAI $openai;

	public function getDependencies(): array
	{
		return [
            'Dog',
		];
	}

	public function getClasses(): array
	{
		return [
            GDO_Conversation::class,
            GDO_GPTMessage::class,
		];
	}

	public function getConfig(): array
	{
		return [
			GDT_Secret::make('chatgpt_apikey'),
            GDT_User::make('chatgpt_user')->notNull(),
		];
	}

	public function onLoadLanguage(): void
	{
		$this->loadLanguage('lang/chatgpt');
	}

	public function onInstall(): void
	{
		$path = $this->filePath('secret.php');
		$apikey = @include($path);
		if ($apikey)
		{
			$this->saveConfigVar('chatgpt_apikey', $apikey);
		}
//        if (!$this->cfgApiUser())
//        {
//            if (!($user = GDO_User::getByName('ChatGPT')))
//            {
//                $user = $this->onInstallUser();
//            }
//            $this->saveConfigVar('chatgpt_user', $user->getID());
//        }
	}

	###########
	### API ###
	###########

//    public function getAPI(): OpenAI
//    {
//        if (!isset($this->openai))
//        {
//            $this->includeVendor();
//            $this->openai = new OpenAI($this->cfgApiKey());
//        }
//        return $this->openai;
//
//    }

	public function getClient(): Client
	{
		if (!isset($this->client))
		{
			$this->includeVendor();
            $key = $this->cfgApiKey();
			$this->client = OpenAI::client($key);
		}
		return $this->client;
	}

	public function cfgApiKey(): ?string
	{
		return $this->getConfigVar('chatgpt_apikey');
	}

//    public function cfgApiUser(): ?GDO_User
//    {
//        return $this->getConfigValue('chatgpt_user');
//    }

    /**
     * @throws GDO_DBException
     */
    public function cfgApiDogUser(DOG_Server $server): ?DOG_User
    {
        return DOG_User::getOrCreateUser($server, 'ChatGPT');
    }

//    /**
//     * @throws GDO_DBException
//     */
//    private function onInstallUser(): GDO_User
//    {
////        return GDO_User::blank([
////            'user_type' => GDT_UserType::BOT,
////            'user_name' => 'ChatGPT',
////        ])->insert();
//        $doguser = DOG_User::getOrCreateUser(Bash::instance()->server, 'ChatGPT');
//        $gdouser = $doguser->getGDOUser();
//        $gdouser->saveVar('user_type', GDT_UserType::BOT);
//        return $gdouser;
//    }


}
