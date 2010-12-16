<?php

use Nette\Application\PresenterRequest;

/**
 * CliRouter supplied with Nette Framework is broken - doesnt support defaults
 *
 * @author Jan Langer, kontakt@janlanger.cz
 */
class FixedCliRouter extends \Nette\Application\CliRouter {
    const PRESENTER_KEY = 'action';

    /**
     * Maps command line arguments to a PresenterRequest object.
     * @param  Nette\Web\IHttpRequest
     * @return PresenterRequest|NULL
     */
    public function match(Nette\Web\IHttpRequest $httpRequest) {

        if (empty($_SERVER['argv']) || !is_array($_SERVER['argv'])) {
            return NULL;
        }

        $names = array(self::PRESENTER_KEY);
        $params = $this->getDefaults();
        $args = $_SERVER['argv'];

        array_shift($args);
        $args[] = '--';

        foreach ($args as $arg) {
            $opt = preg_replace('#/|-+#A', '', $arg);
            if ($opt === $arg) {
                if (isset($flag) || $flag = array_shift($names)) {
                    $params[$flag] = $arg;
                } else {
                    $params[] = $arg;
                }
                $flag = NULL;
                continue;
            }

            if (isset($flag)) {
                $params[$flag] = TRUE;
                $flag = NULL;
            }

            if ($opt !== '') {
                $pair = explode('=', $opt, 2);
                if (isset($pair[1])) {
                    $params[$pair[0]] = $pair[1];
                } else {
                    $flag = $pair[0];
                }
            }
        }

        if (!isset($params[self::PRESENTER_KEY])) {
            throw new \InvalidStateException('Missing presenter & action in route definition.');
        }
        $presenter = $params[self::PRESENTER_KEY];
        if ($a = strrpos($presenter, ':')) {
            $params[self::PRESENTER_KEY] = substr($presenter, $a + 1);
            $presenter = substr($presenter, 0, $a);
        }
        elseif($presenter!="") {
            $defaults=  $this->getDefaults();
            $params['action'] =$presenter;
            $presenter = $defaults['presenter'];
        }

        return new PresenterRequest(
                $presenter,
                'CLI',
                $params
        );
    }

}

?>
