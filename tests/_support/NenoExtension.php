<?php

use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;

require_once realpath(dirname(__FILE__) . '/../../vendor/autoload.php');

class NenoExtension extends \Codeception\Platform\Extension
{
	// list events to listen to
	public static $events = array (
		Events::TEST_FAIL => 'testFailed',
	);

	public function _initialize()
	{
		$this->options['silent'] = false;
	}


	public function testFailed(\Codeception\Event\FailEvent $e)
	{
		$mail = new PHPMailer;

		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host       = 'smtp.mandrillapp.com';  // Specify main and backup SMTP servers
		$mail->SMTPAuth   = true;                               // Enable SMTP authentication
		$mail->Username   = 'info@notwebdesign.com';                 // SMTP username
		$mail->Password   = 'i8YaATdXRFRp56C975n_xA';                           // SMTP password
		$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
		$mail->Port       = 587;                                    // TCP port to connect to

		$mail->From     = 'info@neno-translate.com';
		$mail->FromName = 'Neno Travis';
		$mail->addAddress('victor@notwebdesign.com');

		/*$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
		$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
		$mail->isHTML(true);                                  // Set email format to HTML*/

		$mail->Subject = 'Here is the subject';
		$mail->Body    = 'This is the HTML message body <b>in bold!</b>';
		$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

		if (!$mail->send())
		{
			file_put_contents(dirname(__FILE__) . '/log.txt', $mail->ErrorInfo);
		}
	}
}