# org.civicrm.mosaicomsgtpl

![Screenshot](/images/screenshot.png)

This extension enables you to use [CiviCRM Mosaico](https://github.com/veda-consulting/uk.co.vedaconsulting.mosaico/)
templates with scheduled reminders, personal messages, etc. It does this by automatically copying each
template from Mosaico to a CiviCRM "Message Template".

When you install the extension, all existing `MosaicoTemplate`s will be automatically copied to `MessageTemplate`s.

When you create or update a `MosaicoTemplate`, the corresponding `MessageTemplate` will be updated.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v5.4+
* CiviCRM 4.7.28+ (recommended)
* CiviCRM-Mosaico v2 **Note:** *You should ensure you are using the latest stable version of Mosaico.*

## Installation

This extension has not yet been published for installation via the web UI.

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl org.civicrm.mosaicomsgtpl@https://github.com/civicrm/org.civicrm.mosaicomsgtpl/archive/master.zip
```

Alternatively, sysadmins and developers may clone the
[Git](https://en.wikipedia.org/wiki/Git) repo for this extension and install
it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/civicrm/org.civicrm.mosaicomsgtpl.git
cv en mosaicomsgtpl
```
## Normal Usage

Simply enable the module. All ordinary tasks should be handled automatically,
but see Special Links below.

CiviCRM Message Templates have both a *name* and an email *subject*, but Mosaico templates do not have a subject. By default the name of the Mosaico template will be used for both the message template name and also its subject. This might or might not be appropriate to your use case.

As a work-around, you can use the format `title | subject` to specify your Mosaico template name. e.g.

| Mosaico Template Title                    | Message Template Title   | Email subject     |
| ----------------------------------------- | ------------------------ | ----------------- |
| Welcome supporter                         | Welcome supporter        | Welcome supporter |
| Thanks - existing donors \| Well hi there | Thanks - existing donors | Well hi there     |


## Special Links (unsubscribe, show online)

CiviMail mailings can include unsubscribe links and "show online" links. Mosaico complicates this a bit further by using  its own custom tokens for this, `[unsubscribe_link]` and `[show_link]`.

Most message template use is for *transactional* email not *bulk* email, but there are cases when this boundary is blurred (e.g. "Welcome to our newsletter list" type emails that the recipient should still be able to unsubscribe from, or automated mailings based on message templates, e.g. from the [Chassé extension](https://github.com/artfulrobot/chasse)).

It's your responsibility to check that these links are used properly (or not used). The options are:

### Unsubscribe Links

If you are writing a message template that will be sent via CiviMail you should be covered because this extension will change the Mosaico tokens for CiviMail ones, and CiviMail will then substitute those.

If you are writing a message template that will be sent by some other means, e.g. sending an email as an activity, or using the MessageTemplate.send API method, then you will need to either:

1. Remove all unsubscribe links (e.g. in Mosaico remove the "Pre Header" section, as well as any `{action.unsubscribeUrl}` links). This may be fine for transactional email that does not require the unsubscribe offer.

2. Write your own code to substitute the `{action.unsubscribeUrl}` with an appropriate other link, e.g. maybe you have a WebForm set up that can give users a way to unsubscribe from certain mailings, and you could construct a URL to that including the contact hash. You can do this with [`hook_civicrm_alterMailContent`](https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterMailContent/). However please note that as this extension also uses that hook and may get called after your extension, you should look to also substitute `[unsubscribe_link]` in your code.

### Show Online Link

This one is more tricky because there is no way in Mosaico's template to remove the Show Online link if using the preheader. So you can either remove the preheader element for these mailings or write your own code using the hook mentioned above and a regular expression to remove the full link.

### Other CiviMail tokens

There are many [tokens available](https://docs.civicrm.org/user/en/latest/common-workflows/tokens-and-mail-merge/#available-tokens) that may not be appropriate for Message Template use. You should check that any tokens in use still behave properly.


## API Usage

System administrators and developers may wish to use the synchronization API:

 * Synchronize all templates: `cv api Job.mosaico_msg_sync`
 * Synchronize one template: `cv api Job.mosaico_msg_sync id=<mosaicoTemplateId>`

## Known Issues

 * Synchronization is one-way. To make changes, you should only use the Mosaico template editor.
 * When you load a Mosaico template into a richtext editor (such as CKEditor or TinyMCE), you should tread carefully: only make small changes to the text. Changing the layout in a substantive way would be difficult.
* There is a known issue using when using this extension if you have the smarty in email setting turned on. See https://github.com/civicrm/org.civicrm.mosaicomsgtpl/issues/9

