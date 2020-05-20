<?php

namespace Craft;

use Guzzle\Http\Client;

class RecaptchaV3Service extends BaseApplicationComponent
{
    private $settings;

    public function getSettings()
    {
        if (! is_null($this->settings)) {
            return $this->settings;
        }

        $plugin = craft()->plugins->getPlugin('recaptchaV3');

        return $this->settings = $plugin->getSettings();
    }

    public function getSiteKey()
    {
        $settings = $this->getSettings();

        return $settings->attributes['siteKey'];
    }

    public function getSecretKey()
    {
        $settings = $this->getSettings();

        return $settings->attributes['secretKey'];
    }

    public function verify($recaptchaResponse)
    {
        $base = 'https://www.google.com/recaptcha/api/siteverify';

        $params = array(
            'secret' =>  $this->getSecretKey(),
            'response' => $recaptchaResponse,
            'remoteip' => $_SERVER['REMOTE_ADDR'],
        );

        $client = new Client();

        $request = $client->post($base);
        $request->addPostFields($params);
        $result = $client->send($request);

        if ($result->getStatusCode() == 200) {
            $response = $result->json();

            return $response['success'] && $response['score'] > 0.5;
        } else {
            return false;
        }
    }
}
