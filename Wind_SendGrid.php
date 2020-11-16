<?php
/*
**	Rose\Ext\Wind\SendGrid
**
**	Copyright (c) 2019-2020, RedStar Technologies, All rights reserved.
**	https://rsthn.com/
**
**	THIS LIBRARY IS PROVIDED BY REDSTAR TECHNOLOGIES "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
**	INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A 
**	PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL REDSTAR TECHNOLOGIES BE LIABLE FOR ANY
**	DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT 
**	NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; 
**	OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, 
**	STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE
**	USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

namespace Rose\Ext\Wind;

use Rose\Errors\Error;
use Rose\Errors\ArgumentError;

use Rose\Configuration;
use Rose\Extensions;
use Rose\Text;
use Rose\Arry;
use Rose\Expr;

if (!Extensions::isInstalled('Wind'))
	return;

$sendgrid_sendmail = function ($args, $parts, $data)
{
	$mail = new \SendGrid\Mail\Mail();
	$config = Configuration::getInstance()->Mail;

	$from = $config->from;
	$fromName = $config->fromName;

	for ($i = 1; $i < $args->length; $i++)
	{
		switch (Text::toUpperCase($args->get($i)))
		{
			case 'RCPT': case 'TO':
				$value = $args->get(++$i);

				if (\Rose\typeOf($value) == 'Rose\\Arry')
				{
					$value->forEach(function($value) use(&$mail) {
						$mail->addTo($value);
					});
				}
				else
					$mail->addTo($value);

				break;

			case 'CC':
				$tmp = trim($args->get(++$i));
				if ($tmp) $mail->addTo($tmp);
				break;

			case 'BCC':
				$tmp = trim($args->get(++$i));
				if ($tmp) $mail->addBcc($tmp);
				break;
	
			case 'FROM':
				$from = $args->get(++$i);
				break;

			case 'FROM-NAME':
				$fromName = $args->get(++$i);
				break;

			case 'SUBJECT':
				$mail->setSubject ($args->get(++$i));
				break;

			case 'BODY':
				$mail->addContent("text/html", $args->get(++$i));
				break;

			case 'ATTACHMENT':
				$value = $args->get(++$i);

				if (\Rose\typeOf($value) == 'Rose\\Map')
				{
					if ($value->has('data'))
					{
						$attachment = new \SendGrid\Mail\Attachment();
						$attachment->setContent($value->data);
						//$attachment->setType(...);
						$attachment->setFilename($value->name);
						$attachment->setDisposition("attachment");
						$mail->addAttachment($attachment);
					}
					else if ($value->has('path'))
					{
						$attachment = new \SendGrid\Mail\Attachment();
						$attachment->setContent(file_get_contents($value->path));
						//$attachment->setType(...);
						$attachment->setFilename($value->name);
						$attachment->setDisposition("attachment");
						$mail->addAttachment($attachment);
					}
				}
				else
				{
					$attachment = new \SendGrid\Mail\Attachment();
					$attachment->setContent(file_get_contents($value));
					$attachment->setFilename(basename($value));
					$attachment->setDisposition("attachment");
					$mail->addAttachment($attachment);
				}

				break;
		}
	}

	$mail->setFrom($from, $fromName);

	try
	{
		$sendgrid = new \SendGrid($config->sendgrid);
		$response = $sendgrid->send($mail);

		if ($response->statusCode() != 200 && $response->statusCode() != 201 && $response->statusCode() != 202)
			throw new \Exception ('(' . $response->statusCode() . ') ' . json_decode($response->body())->errors[0]->message);

		return true;
	}
	catch (\Exception $e)
	{
		\Rose\trace('(SendGrid) ' . $e->getMessage());
		return false;
	}
};

Expr::register('mail::send', $sendgrid_sendmail);
Expr::register('sendgrid::send', $sendgrid_sendmail);
