<?php
namespace GDO\ChatGPT\Method;

use GDO\ChatGPT\Module_ChatGPT;
use GDO\Core\GDT;
use GDO\Core\GDT_Checkbox;
use GDO\Core\GDT_EnumNoI18n;
use GDO\Core\GDT_Text;
use GDO\Core\GDT_UInt;
use GDO\Form\GDT_AntiCSRF;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\MethodForm;

/**
 * Generate an image out of a prompt using ChatGPT.
 */
final class Image extends MethodForm
{

    public function isCLI(): bool
    {
        return true;
    }

    public function getCLITrigger(): string
    {
        return 'gpt.image';
    }

    protected function createForm(GDT_Form $form): void
    {
        $form->addFields(
            GDT_EnumNoI18n::make('model')->enumValues('dall-e-3')->notNull()->initial('dall-e-3'),
            GDT_EnumNoI18n::make('size')->enumValues('256x256', '512x512', '1024x1024', '1024x1792', '1792x1024')->notNull()->initial('1792x1024'),
            GDT_Text::make('prompt')->notNull(),
            GDT_AntiCSRF::make(),
        );
        $form->actions()->addField(GDT_Submit::make());
    }

    public function formValidated(GDT_Form $form): GDT
    {
        $client = Module_ChatGPT::instance()->getClient();
        $image = $client->images()->create([
            'model' => $form->getFormVar('model'),
            'prompt' => $form->getFormVar('prompt'),
            'size' => $form->getFormVar('size'),
            'quality' => "standard",
            'n' => 1,
        ]);
        if ($url = @$image->data[0]->url)
        {
            return $this->message('msg_gpt_image_generated', [$url]);
        }
        return $this->error('err_gpt_image');
    }

}
