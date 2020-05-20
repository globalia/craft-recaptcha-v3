<?php
namespace Craft;

class RecaptchaV3Plugin extends BasePlugin
{
    function getName()
    {
        return Craft::t('reCAPTCHA V3');
    }

    function getVersion()
    {
        return '1.0';
    }

    function getDeveloper()
    {
        return 'Globalia';
    }

    function getDeveloperUrl()
    {
        return 'https://www.globalia.ca';
    }

    public function init()
    {
        if (craft()->plugins->getPlugin('amforms', true)) {
            if ($reCaptchaSecretKey = craft()->recaptchaV3->getSecretKey()) {
                craft()->on('amForms_submissions.onBeforeSaveSubmission', function (Event $event) {
                    $submission = $event->params['submission'];
                    $form = $submission->getAttribute('form');

                    if (in_array($form->handle, ['contact-en', 'contact-fr']) && ! empty(craft()->recaptchaV3->getSecretKey())) {
                        if (! craft()->recaptchaV3->verify(craft()->request->getPost('g-recaptcha-response', null))) {
                            $event->performAction = false;
                            $error = Craft::t('recaptcha.error.message');
                            $submission->addError('recaptcha', $error);
                        }
                    }
                });
            }
        }
    }

    protected function defineSettings()
    {
        return array(
            'siteKey' => array(AttributeType::Mixed, 'default' => ''),
            'secretKey' => array(AttributeType::Mixed, 'default' => '')
        );
    }

    public function getSettingsHtml()
    {
        return craft()->templates->render('recaptchaV3/settings', array(
            'settings' => $this->getSettings()
        ));
    }

}
