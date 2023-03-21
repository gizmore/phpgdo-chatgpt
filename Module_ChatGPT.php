<?php
namespace GDO\ChatGPT;

use GDO\Core\GDO_Module;
use GDO\Core\GDT_Secret;
use GDO\Core\WithComposer;
use OpenAI;
use OpenAI\Client;

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

	public function getDependencies(): array
	{
		return [
			'File',
		];
	}

	public function getClasses(): array
	{
		return [
			GDO_ChatMemory::class,
		];
	}

	public function getConfig(): array
	{
		return [
			GDT_Secret::make('chatgpt_apikey'),
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
	}

	###########
	### API ###
	###########

	public function getClient(): Client
	{
		if (!isset($this->client))
		{
			$this->includeVendor();
			$this->client = OpenAI::client($this->cfgApiKey());
		}
		return $this->client;
	}

	public function cfgApiKey(): ?string
	{
		return $this->getConfigVar('chatgpt_apikey');
	}

}
