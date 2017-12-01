# org.civicrm.mosaicomsgtpl

> This extension is EXPERIMENTAL and UNUSPPORTED. If you would like to become a maintainer,
> please post an issue and mention `totten`.

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
* CiviCRM-Mosaico v2.0-beta3+

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

## Usage

Simply enable the module. All ordinary tasks should be handled automatically.

System administrators and developers may wish to use the synchronization API:

 * Synchronize all templates: `cv api Job.mosaico_msg_sync`
 * Synchronize one template: `cv api Job.mosaico_msg_sync id=<mosaicoTemplateId>`

## Known Issues

 * Synchronization is one-way. To make changes, you should only use the Mosaico template editor.
 * When you load a Mosaico template into a richtext editor (such as CKEditor or TinyMCE), you should tred
   carefully: only make small changes to the text. Changing the layout in a substantive way would be difficult.
