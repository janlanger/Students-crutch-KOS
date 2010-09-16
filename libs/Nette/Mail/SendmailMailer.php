<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Mail
 */



/**
 * Sends e-mails via the PHP internal mail() function.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Mail
 */
class NSendmailMailer extends NObject implements IMailer
{

	/**
	 * Sends e-mail.
	 * @param  NMail
	 * @return void
	 */
	public function send(NMail $mail)
	{
		$tmp = clone $mail;
		$tmp->setHeader('Subject', NULL);
		$tmp->setHeader('To', NULL);

		$parts = explode(NMail::EOL . NMail::EOL, $tmp->generateMessage(), 2);

		NDebug::tryError();
		$res = mail(
			str_replace(NMail::EOL, PHP_EOL, $mail->getEncodedHeader('To')),
			str_replace(NMail::EOL, PHP_EOL, $mail->getEncodedHeader('Subject')),
			str_replace(NMail::EOL, PHP_EOL, $parts[1]),
			str_replace(NMail::EOL, PHP_EOL, $parts[0])
		);

		if (NDebug::catchError($msg)) {
			throw new InvalidStateException($msg);

		} elseif (!$res) {
			throw new InvalidStateException('Unable to send email.');
		}
	}

}
