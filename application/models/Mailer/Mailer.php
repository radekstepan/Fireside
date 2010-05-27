<?php

/**
 * Description of Mailer.
 *
 * @package Application\Models\Mailer
 */
class Mailer extends Fari_Mail {

    private $message;

    public function sendMessage($recipients, $messageId, $topicId, Topics &$topics) {
        $this->addTo($recipients)->addFrom('fireside.app@gmail.com', 'Fireside App');

        $topic = $topics->findFirst()->where($topicId);
        
        $this->setSubject($topic['name']);

        // form a link
        $url = 'http://' . $_SERVER['HTTP_HOST'] . WWW_DIR.'/messages/show/'.$topicId.'/'.$messageId;
        $url = "<p><a href='{$url}'>{$url}</a></p>";

        $this->setBody($url . implode("\n", $this->message));

        $this->send();
    }

    public function addMessageNode($current, $parent) {
        $bq = <<<GMAIL
<blockquote style="border-left: 1px solid rgb(204, 204, 204); margin: 0pt 0pt 0pt 0.8ex; padding-left: 1ex;" class="gmail_quote">
GMAIL;

        $this->message[] = (!empty($parent)) ? "{$bq}{$parent}</blockquote><p>{$current}</p>" : "<p>{$current}</p>";
    }

    /**
     * Setup host, port, login and password.
     * @param string $host
     * @param integer $port
     * @param string $username
     * @param string $password
     */
    public function __construct($host=NULL, $port=NULL, $username=NULL, $password=NULL) {
        // include PEAR Mail:: if possible...

        // a hack of a production environment on Onebit servers
        if (Fari_ApplicationEnvironment::isProduction()) {
            set_include_path(BASEPATH . '/application/3rdparty/pear:'.get_include_path());
            include('Mail.php');
            include('Mail/mime.php');
            include('SMTP.php');
        } else {
            try {
                // get include paths
                $paths = explode(':/', get_include_path());

                foreach ($paths as $path) {
                    // 'fix' directory
                    if (substr($path, 0, 1) == '.') {
                        // this directory
                        $path = '';
                    } else {
                        // directory from the root
                        $path = "/{$path}/";
                    }

                    // can we call PEAR Mail:: ?
                    if (file_exists("{$path}Mail.php") && file_exists("{$path}Mail/mime.php")) {
                        // include
                        require_once "{$path}Mail.php";
                        require_once "{$path}Mail/mime.php";
                        // switch
                        $found = TRUE;
                        // we are done here
                        break;
                    }
                }
                if ($found !== TRUE) throw new Fari_Exception("PEAR Mail:: has not been found");
            } catch (Fari_Exception $exception) { $exception->fire(); }
        }

        // setup the connection details
        $this->setConnection($host, $port, $username, $password);
    }

    /**
     * Sender.
     * @return TRUE on succesfull sending, otherwise Fari Exception is thrown
     */
    public function send() {
        $message = new Mail_mime();
        // plaintext & HTML version
        $text = strip_tags($this->getBody());
        $html = $this->getBody();

        $message->setTXTBody($text);
        $message->setHTMLBody($html);
        $body = $message->get();

        $headers = array(
            'From' => $this->getHeader('From'),
            'To' => $this->getHeader('To'),
            'Subject' => $this->getHeader('Subject'),
        );

        $headers = $message->headers($headers);
        
        // connection
        $smtp = Mail::factory(
            'smtp',
            array(
                'host' => $this->prefix.$this->host,
                'port' => $this->port,
                'auth' => true,
                'username' => $this->username,
                'password' => $this->password,
                'timeout' => 3
            )
        );

        try {
            // did we build the email?
            if (PEAR::isError($smtp)) {
                throw new Fari_Exception("Failed to build mail: {$smtp->getMessage()}");
            } else {
                // send
                $mail = $smtp->send($this->getHeader('To'), $headers, $body);
                // did all went fine?
                if (PEAR::isError($mail)) {
                    throw new Fari_Exception("Failed to send mail: {$mail->getMessage()}");
                }
            }
        } catch (Fari_Exception $exception) { $exception->fire(); }

        return TRUE;
    }

}
