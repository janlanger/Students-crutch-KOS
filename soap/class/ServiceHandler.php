<?php

/**
 * Description of ServiceHandler
 *
 * @author Honza
 */
class ServiceHandler {

    private $user;
    private $latestError;
    private $revision;
    private $query;

    public function __call($name, $arguments) {
        try {
            if ($this->user == NULL) {
                throw new InvalidStateException('Unauthorized!');
            }
            $operation = Operation::getSQL(array("name" => $name, "rev_id" => $this->getRevision()->rev_id));
            if (!($operation instanceof DibiRow)) {
                throw new InvalidArgumentException("Operation " . $name . ' is not defined.');
            }
            $params = unserialize($operation->params);

            $this->proccessQuery($operation->sql, $params, $arguments);

            $q = call_user_func("dibi::query", $this->query);
            /* @var $q DibiResult */
            $q->detectTypes();
            if ($operation->return == 'array') {
                $returns = array();
                foreach ($q as $row) {
                    $returns[] = (array) $row;
                }
                return $returns;
            } else {
                return $q->fetch();
            }
        } catch (Exception $e) {
            $this->latestError = $e;
            NEnvironment::getContext()->getService('ILogger')->logMessage($this->getLastError(), Logger::WARNING, 'SOAP service');
            return FALSE;
        }
    }

    public function authenticate($client, $password) {
        $this->user = new SoapIdentity();
        try {
            $this->user->authenticate($client, $password);
            return TRUE;
        } catch (NAuthenticationException $e) {

            NEnvironment::getContext()->getService('ILogger')->logMessage("Failed login attempt for " . $client . "@" . $_SERVER['REMOTE_ADDR'], Logger::WARNING, 'SOAP service');
            $this->latestError = $e;
            return FALSE;
        }
    }

    public function getLastError() {
        if (!is_null($this->latestError)) {
            return get_class($this->latestError) . ': ' . $this->latestError->getMessage();
        }
        return NULL;
    }

    private function getRevision() {
        if ($this->revision == NULL) {
            $revision = @reset(Revision::find(array("app_id" => $this->user->getApp_id(), "isMain" => TRUE)));
            $this->revision = $revision;
            dibi::query("USE [" . $revision['db_name'] . "]");
        }
        return $this->revision;
    }

    private function proccessQuery($sql, $params, $values) {
        $order = array();
        foreach ($params as $key => $param) {
            $pos = strpos($sql, $param['name']);
            if ($pos !== FALSE) {
                $order[$pos] = $values[$key];
                $sql = str_replace($param['name'], $this->getModificator($param['type']), $sql);
            }
        }

        ksort($order);
        $this->query = array_merge(array($sql), $order);
    }

    private function getModificator($type) {
        switch ($type) {
            case 'int':
            case 'integer':
                return "%i";
                break;
            case 'string':
                return "%s";
                break;

            default:
                break;
        }
    }

    public function getQuery() {
        ob_start();
        call_user_func("dibi::test", $this->query);
        return ob_get_clean();
    }

}

?>
