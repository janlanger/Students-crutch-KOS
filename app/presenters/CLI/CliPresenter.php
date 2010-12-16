<?php

/**
 * Description of CliPresenter
 *
 * @author Honza
 */
class CliPresenter extends BasePresenter {

	/**
	 * (non-phpDoc)
	 *
	 * @see Nette\Application\Presenter#startup()
	 */
	protected function startup() {
		parent::startup();
                if(!\Nette\Environment::isConsole() && \Nette\Environment::isProduction()) {
                    echo 'This presenter should be called only from CLI interface.';
                    $this->terminate();
                }
	}
        
}