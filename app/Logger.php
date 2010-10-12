<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Logger
 *
 * @author Honza
 */
class Logger {
    const CRITICAL='critical';
    const WARNING = 'warning';
    const NOTICE = 'notice';
    const INFO = 'notice';
    const DEBUG = 'debug';

    private $defaultSeverity='warning';

    private $log_table = 'rozvrh_main.log';

    public function logMessage($message, $severity=NULL,$component=NULL) {
        if (is_null($severity)) {
            $severity = $this->defaultSeverity;
        }

        $message=iconv('ASCII', 'ASCII//IGNORE', $message); //removes all non-ASCII characters
        if(strlen($message)>255) {
            trigger_error('Log message was longer than 255 characters. Truncated.', E_USER_NOTICE);
        }
        dibi::insert($this->log_table, array(
                    'message' => $message,
                    'severity' => $severity,
                    'component' => $component
                ))->execute();
    }

}

?>
