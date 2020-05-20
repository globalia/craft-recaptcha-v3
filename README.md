# ReCAPTCHA V3

Adds reCAPTCHA capability to forms

## How to use

### There is a little example snippet

Add this code in your \<form\> :
```
{% if craft.recaptchaV3.getRecaptchaSiteKey %}
    <input type="hidden" id="g-recaptcha-response-contact-form" name="g-recaptcha-response">
{% endif %}
```

In your \<head\> :
```
{% if renderReCaptcha | default %}
    {% set reCaptchaSiteKey = craft.recaptchaV3.getRecaptchaSiteKey %}

    {% if reCaptchaSiteKey %}
        <script src="https://www.google.com/recaptcha/api.js?render={{ reCaptchaSiteKey }}"></script>
        <script>
            grecaptcha.ready(function() {
                grecaptcha.execute('{{ reCaptchaSiteKey }}', { action: 'contact' })
                    .then(function(token) {
                        document.getElementById('g-recaptcha-response-contact-form').value = token;
                    });
            });
        </script>
    {% endif %}
{% endif %}
```

If you use <b>Formerly</b>, here is code example to use.<br/>
In craft/plugins/recaptchav3/RecaptchaV3Plugin.php
```
if (craft()->plugins->getPlugin('formerly', true)) {
    if ($reCaptchaSecretKey = craft()->recaptchaV3->getSecretKey()) {
        craft()->on('formerly_submissions.onBeforePost', function (Event $event) {
            $submission = $event->params['submission'];
            $form = craft()->formerly_forms->getFormById($submission->formId);

            if ($form->handle == 'contactUs' && ! empty(craft()->recaptchaV3->getSecretKey())) {
                if (! craft()->recaptchaV3->verify(craft()->request->getPost('g-recaptcha-response', null))) {
                    $error = Craft::t('recaptcha.error.message');
                    $submission->addError('recaptcha', $error);
                }
            }
        });
    }
}
```

and in your template :
```
Between {% extends %} and {% block content %}
{% set renderReCaptcha = true %}

In {% block content %} :
{% if submission is defined and submission.getAllErrors() | length %}
    <div class="error">
        {% for error in submission.getAllErrors() %}
            <li>{{ error }}</li>
        {% endfor %}
    </div>
{% endif %}
```

For <b>a&m forms</b><br/>
In craft/plugins/recaptchav3/RecaptchaV3Plugin.php
```
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
```

and in your template :
```
Between {% extends %} and {% block content %}
{% set renderReCaptcha = true %}

{% macro errorList(errors) %}
    {% if errors %}
        <ul class="errors">
            {% for error in errors %}
                <li>{{ error }}</li>
            {% endfor %}
        </ul>
    {% endif %}
{% endmacro %}
{% from _self import errorList %}

In {% block content %} :
{% if form is defined %}
    {{ errorList(formProject.getErrors('recaptcha')) }}
{% endif %}
```
