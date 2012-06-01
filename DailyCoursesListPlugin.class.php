<?php

class DailyCoursesListPlugin extends AbstractStudIPSystemplugin {

	public $config = array();

	function __construct() {

		parent::__construct();
		$this->me = get_class($this);
		$this->restoreConfig();
		$navigation = new PluginNavigation();

              if(!(empty($GLOBALS['auth']->auth['uid']) or $GLOBALS['auth']->auth['uid'] === 'nobody'))
              {
                  $navigation->setDisplayname($this->getDisplayTitle());
              } else {
                  $nav = new AutoNavigation(_('Veranstaltungen heute'), PluginEngine::GetLink($this, array(), 'show'));
                  $nav->setImage('blank.gif');
		    Navigation::addItem('/foo', $nav);
              }

		$navigation->setCommand('show');
		$this->setNavigation($navigation);
		$this->setDisplayType(SYSTEM_PLUGIN_STARTPAGE);
	}

	function getDisplayTitle(){
		return _("Veranstaltungen heute");
	}

	function restoreConfig(){
		$config = DBManager::get()
				->query("SELECT comment FROM config WHERE field = 'CONFIG_" . $this->getPluginName() . "' AND is_default=1")
				->fetchColumn();
		$this->config = unserialize($config);
		return $this->config != false;
	}

	function storeConfig(){
		$config = serialize($this->config);
		$field = "CONFIG_" . $this->getPluginName();
		$st = DBManager::get()
		->prepare("REPLACE INTO config (config_id, field, value, is_default, type, range, chdate, comment)
			VALUES (?,?,'do not edit',1,'string','global',UNIX_TIMESTAMP(),?)");
		return $st->execute(array(md5($field), $field, $config));
	}

	/**
	* This method dispatches and displays all actions. It uses the template
	* method design pattern, so you may want to implement the methods #route
	* and/or #display to adapt to your needs.
	*
	* @param  string  the part of the dispatch path, that were not consumed yet
	*
	* @return void
	*/
	function perform($unconsumed_path) {
	    if(!$unconsumed_path){
	        header("Location: " . PluginEngine::getUrl($this), 302);
	        return false;
	    }
		$trails_root = $this->getPluginPath();
        $dispatcher = new Trails_Dispatcher($trails_root, null, 'show');
		$dispatcher->current_plugin = $this;
		$dispatcher->dispatch($unconsumed_path);

	}

}
