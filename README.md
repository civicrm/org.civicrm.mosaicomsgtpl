# org.civicrm.mosaicomsgtpl

![Screenshot](/images/screenshot.png)

This extension enables you to use [CiviCRM-Mosaico](https://github.com/veda-consulting/uk.co.vedaconsulting.mosaico/)
templates with scheduled reminders, personal messages, etc. It does this by automatically copying each
template from Mosaico to a CiviCRM "Message Template".

When you install the extension, all existing `MosaicoTemplate`s will be automatically copied to `MessageTemplate`s.

When you create or update a `MosaicoTemplate`, the corresponding `MessageTemplate` will be updated.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v5.4+
* CiviCRM 4.7.28+ (recommended)
* CiviCRM-Mosaico v2.0-beta3+ ***Note:** You should ensure you are using the official beta3 from https://github.com/veda-consulting/uk.co.vedaconsulting.mosaico - some of the forked beta3 versions do not work with this extension.*

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

Simply enable the module. All ordinary tasks should be handled automatically.

CiviCRM Message Templates have both a *name* and an email *subject*, but Mosaico templates do not have a subject. By default the name of the Mosaico template will be used for both the message template name and also its subject. This might or might not be appropriate to your use case.

As a work-around, you can use the format `title | subject` to specify your Mosaico template name. e.g.

| Mosaico Template Title                    | Message Template Title   | Email subject     |
| ----------------------------------------- |--------------------------| ------------------|
| Welcome supporter                         | Welcome supporter        | Welcome supporter |
| Thanks - existing donors \| Well hi there | Thanks - existing donors | Well hi there     |


## API Usage

System administrators and developers may wish to use the synchronization API:

 * Synchronize all templates: `cv api Job.mosaico_msg_sync`
 * Synchronize one template: `cv api Job.mosaico_msg_sync id=<mosaicoTemplateId>`

## Known Issues

 * Synchronization is one-way. To make changes, you should only use the Mosaico template editor.
 * When you load a Mosaico template into a richtext editor (such as CKEditor or TinyMCE), you should tred
   carefully: only make small changes to the text. Changing the layout in a substantive way would be difficult.

