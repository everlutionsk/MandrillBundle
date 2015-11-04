# Mandrill Bundle

This Symfony bundle provides *mail system* and *request processors* for [Email Bundle](https://github.com/everlutionsk/EmailBundle2). Bundle use [Mandrill](https://www.mandrill.com) transactional email platform.


# Installation

```sh
composer require everlutionsk/mandrill-bundle
```


### Enable the bundle

```php
// app/AppKernel.php
public function registerBundles()
{
    return array(
        // ...
        new Everlution\EmailBundle\EverlutionMandrillBundle()
    );
}
```


### Configure the bundle

Following configuration snippet describes how to configure the bundle.<br>

Firstly, you must modify EmailBundle configuration to work with MandrillBundle's services.

```yml
# app/config/config.yml

# EmailBundle Configuration
everlution_email:
    mail_system: everlution.mandrill.mail_system
    request_processors:
        inbound: everlution.mandrill.inbound.request_processor
        outbound_message_event: everlution.mandrill.outbound.message_event.request_processor
```

Secondly, you must configure MandrillBundle itself

```yml
# app/config/config.yml

# MandrillBundle Configuration
everlution_mandrill:
    api_key: SECRET_API_KEY
    async_mandrill_sending: true
    enforced_delivery_address: EMAIL_ADDRESS|NULL
```

**async_mandrill_sending** - If it is true, then Mandrill use a background sending mode that is optimized for bulk sending. In async mode, Mandrill will immediately return a status of "queued" for every message. This is a recommended setting, because bundle is able to handle message events, which describe message state, later and fully automatically.


**enforced_delivery_address** - [Optional] Email address, which will be used to override recipient address in every outbound message. Changes are fully transparent and data stored in database is not affected by this transformation. *Warning: Mail system ID could be associated with wrong recipient due to Mandrill API limitations. This limitation could lead to incorrect 'outbound message event' handling.*
# Usage

### Message transformers
*Mail system* service provided by this bundle transform [OutboundMessage](https://github.com/everlutionsk/EmailBundle2/blob/master/Outbound/Message/OutboundMessage.php) into JSON and then POST this JSON to [Mandrill API](https://mandrillapp.com/api/docs/messages.JSON.html).
However, this JSON can be modified just before it is posted to Mandrill. To do this you must create a service, which implements [RawMessageTransformer interface](Outbound/MailSystem/RawMessageTransformer.php) and add following tag:

```yml
everlution.mandrill.outbound.raw_message_transformer
```


# TODO
----
- Request signature calculation
- Webhook keys configuration