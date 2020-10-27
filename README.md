# SendGrid Adapter for Wind

This extension adds expression functions to [Wind](https://github.com/rsthn/rose-ext-wind) to send emails using SendGrid.

> **NOTE:** The extension detects the presence of Wind, when not installed, this extension will simply not be loaded.

# Installation

```sh
composer require rsthn/rose-ext-wind-sendgrid
```

## Configuration Section: `Mail`


|Field|Type|Description|Default|
|----|----|-----------|-------|
|sendgrid|`string`|SendGrid API Key.|Required
|from|`string`|Email address of the sender.|Required
|fromName|`string`|Name of the sender.|Blank


## Expression Functions

### `mail::send` name:string value:string ...

Accepts one or more name:value pairs, the name describes the field to set in the `mail` object of PHPMailer. Currently supported:

|Name|Type|Description|
|----|----|-----------|
|RCPT|`string`|Email address of the recipient.
|RCPT|`array`|Email addresses of the recipients.
|FROM|`string`|Email of the sender.
|FROM-NAME|`string`|Name of the sender.
|SUBJECT|`string`|Subject of the message.
|BODY|`string`|HTML contents of the message.
|ATTACHMENT|`string`|Adds the specified file (path) as an attachment.
|ATTACHMENT|`map { name, data }`|Adds an attachment from a given data string.
|ATTACHMENT|`map { name, path }`|Adds an attachment from a given path.

Example:

```lisp
(mail::send
	RCPT 'example@host.com'
	SUBJECT 'This is a test.'
	BODY '<b>Thanks for reading this email.</b>'
)
```
