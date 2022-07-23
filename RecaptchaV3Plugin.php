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
        if (craft()->plugins->getPlugin('formerly', true)) {
            if ($reCaptchaSecretKey = craft()->recaptchaV3->getSecretKey()) {
                craft()->on('formerly_submissions.onBeforePost', function (Event $event) {
                    $submission = $event->params['submission'];
                    $form = craft()->formerly_forms->getFormById($submission->formId);

                    if (in_array($form->handle, ['contact', 'devenirFormateur', 'examen', 'rendezVous'])) {
                        if (! craft()->recaptchaV3->verify(craft()->request->getPost('g-recaptcha-response', null))) {
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
            'secretKey' => array(AttributeType::Mixed, 'default' => ''),
            'scoreTreshold' => array(AttributeType::Mixed, 'default' => '0.5'),
        );
    }

    public function getSettingsHtml()
    {
        return craft()->templates->render('recaptchaV3/settings', array(
            'settings' => $this->getSettings()
        ));
    }

}
