<?php

namespace Craft;

class RecaptchaV3Variable
{
    public function getRecaptchaSiteKey()
    {
        return craft()->recaptchaV3->getSiteKey();
    }
}
