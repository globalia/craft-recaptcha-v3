# reCAPTCHA v3

Adds reCAPTCHA capability to Craft CMS 2 forms.

## How to use

### There is a little example snippet

Add this code in your \<form\> :
```
{% if craft.plugins.getPlugin('recaptchaV3', true) and craft.recaptchaV3.hasRecaptchaKeys %}
    <input type="hidden" data-action="contact" name="g-recaptcha-response">
{% endif %}
```

The `data-action="contact"` attribute will be the action sent to reCAPTCHA. It's useful to know 
from which form the data is coming from. In this case, this is the Contact form.

In your \<head\> :

** Remove `and renderReCaptcha | default` if you want to load reCAPTCHA on all pages)
```
{% if craft.plugins.getPlugin('recaptchaV3', true) and renderReCaptcha | default and craft.recaptchaV3.hasRecaptchaKeys %}
    {% set reCaptchaSiteKey = craft.recaptchaV3.getRecaptchaSiteKey %}

    {% if reCaptchaSiteKey %}
        <script src="https://www.google.com/recaptcha/api.js?render={{ reCaptchaSiteKey }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function(event) {
                var recaptchaResponses = document.querySelectorAll('input[name="g-recaptcha-response"]');

                for (var index = 0; index < recaptchaResponses.length; index++) {
                    var form = recaptchaResponses[index].closest('form');

                    if (form !== null) {
                        form.addEventListener('submit', getRecaptchaToken);
                    }
                }
            });

            function getRecaptchaToken(event) {
                event.preventDefault();
                var form = event.target;

                if (form !== null) {
                    var recaptchaResponse = form.querySelector('input[name="g-recaptcha-response"]');
                    var recaptchaAction = recaptchaResponse.dataset.action !== undefined ? recaptchaResponse.dataset.action : 'submit';

                    if (recaptchaResponse !== null) {
                        grecaptcha.ready(function() {
                            grecaptcha.execute('{{ reCaptchaSiteKey }}', { action: recaptchaAction }).then(function(token) {
                                recaptchaResponse.value = token;
                                form.submit();
                            });
                        });
                    }
                }
            }
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

## In your template

If you want to render reCAPTCHA only in one (or more) specific template, you can set 
a variable between {% extends %} and {% block content %}
```
{% set renderReCaptcha = true %}
```

You can import macro in  the template directly :

```
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
```

or create a file with all your macros and import it, as described below.

Create a file _macros.twig in templates folder and paste this code :
```
{% macro errorList(errors) %}
    {% if errors %}
        <ul class="errors">
            {% for error in errors %}
                <li>{{ error }}</li>
            {% endfor %}
        </ul>
    {% endif %}
{% endmacro %}
```

In your template :
```
{% import "_macros" as macros %}
```

In {% block content %}, copy this code where you want the reCAPTCHA error to be outputted :
```
{% if formHandle is defined %}
    {{ errorList(formHandle.getErrors('recaptcha')) }}
{% endif %}
```

or if your macro is in a file and has been imported :
```
{% if formHandle is defined %}
    {{ macros.errorList(formHandle.getErrors('recaptcha')) }}
{% endif %}
```
