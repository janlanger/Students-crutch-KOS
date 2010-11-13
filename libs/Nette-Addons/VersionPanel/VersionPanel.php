<?php
/**
 * Nella
 *
 * @copyright  Copyright (c) 2006, 2010 Patrik Votoček
 * @license    http://nellacms.com/license  New BSD License
 * @link       http://nellacms.com
 * @category   Nella
 * @package    Nella
 */

namespace Nella\Panels;

use Nette\Reflection\ClassReflection;

/**
 * Version panel for nette
 *
 * @copyright	Copyright (c) 2008, 2010 Patrik Votoček
 * @author		Patrik Votoček
 * @package		Nella
 */
class VersionPanel implements \Nette\IDebugPanel
{
	/** @var array */
	public $updates = array();
	/** @var bool */
	private static $registered = FALSE;

	const VERSION = "2.6";

	/**
	 * Get panel id
	 *
	 * @return string
	 */
	public function getId()
	{
		return 'version-panel';
	}

	/**
	 * Get rendered panel
	 *
	 * @return string
	 */
	public function getPanel()
	{
		if (count($this->updates) < 1)
			$this->loadUpdates();

		ob_start();
		require_once __DIR__ . "/version.panel.phtml";
		return ob_get_clean();
	}

	/**
	 * Get rendered tab
	 *
	 * @return string
	 */
	public function getTab()
	{
		if (count($this->updates) < 1)
			$this->loadUpdates();
		
		$data = '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAjpJREFUeNpi/P//PwMlgAWb4M/f/xhWX3qltPrKW5P/7CxCinxsT0JU+I6byfC+ZediQ1HLiO6CX//+M0WsulW1/tKbIgZmBkGGf0B5ZiYGhu+/7/urC+Qvi9PZzMXGjN2Aj19/MSStu5Wz7sqbyQwMQPHffz8BldwFqtJk4mDj+Pfr36cF4Rrm8SYSN2B6mJBtP//sE+e6Yw9ymH58BVr96/HCKC2ng9lGJjH6oqn/Pnz8z/D9C9/CU4+9cYYB079/zAyfPvz+B3S2gZH0xDgTqbMg8aP3Pz1i+HYd6BhGhv/fvqJYisIxlOX/sjDDItJBTSCu3Flp6ndgYC48fEe/ffnheez/fzEyfPj4KdFMZjNKoIHCABdOm3HAisGz/wV72LT/DN793wI7tgZ8+/EbRQ0Trvi9fP815/Id5yZzsfwT//Xx07swC4XAFUXuGzjZUWMepwGv331W+vzyrcHfL58ZTGX5G5aU++5kY2XGUIfTAAlB7tfSfKzH/nz8dL0y2mYjKxbNeA1QlhF+pSbKvYDhy+d1f3/8fIMzLeMKwMLGZSZsinH/OVXi//sl9zfgUgcPkbdv3zKsWL6cfdWqVewszMyMQjL6rLxc7L8YmdjYLpzc/9PJcSMfKOXKysr+zczM/G5hafkXxQVnz5xhMDExEQK5Hog1gVidgZHfl4FdJg7I1oZiDSCWz0hP54Tpw8hM165eZXj77h0DI8jwf39AOYKBiQkUgIxgtrCQEIOWtjZcPUCAAQD2kictFO3NpAAAAABJRU5ErkJggg==">versions';
		if (count($this->updates) > 0) {
			$data .= '<span style="background: #47d; border: 1px solid #126; border-bottom-left-radius: 3px 3px; border-bottom-right-radius: 3px 3px; border-top-left-radius: 3px 3px; border-top-right-radius: 3px 3px; bottom: 0px; color: white; display: block; font-size: 75%; font-weight: bold; line-height: 100%; position: absolute;">' . count($this->updates) . '</span>';
		}
		return $data;
	}

	/**
	 * Load all updates
	 */
	private function loadUpdates()
	{
		$cache = \Nette\Environment::getCache('Nette.VersionPanel');
		$this->updates = array();
		if (extension_loaded('curl') && !isset($cache['updates'])) {
			$files = array();
			$files[] = ClassReflection::from('Nette\Framework')->getFileName();
			$data = $this->getLatestByGithub('nette', 'nette', \Nette\Framework::VERSION, \Nette\Framework::REVISION);
			if (!empty($data))
				$this->updates[\Nette\Framework::NAME] = $data;
			if (class_exists('dibi')) {
				$files[] = ClassReflection::from('dibi')->getFileName();
				$data = $this->getLatestByGithub('nette', 'dibi', \dibi::VERSION, \dibi::REVISION);
				if (!empty($data))
					$this->updates['dibi'] = $data;
			}
			if (class_exists('Texy')) {
				$files[] = ClassReflection::from('Texy')->getFileName();
				$data = $this->getLatestByGithub('dg', 'texy', \Texy::VERSION, \Texy::REVISION);
				if (!empty($data))
					$this->updates['Texy!'] = $data;
			}
			if (class_exists('ActiveMapper\ORM')) {
				$files[] = ClassReflection::from('ActiveMapper\ORM')->getFileName();
				$data = $this->getLatestByGithub('Nella', 'ActiveMapper', \ActiveMapper\ORM::VERSION, \ActiveMapper\ORM::REVISION);
				if (!empty($data))
					$this->updates['ActiveMapper'] = $data;
			}
			if (class_exists('Doctrine\Common\Version')) {
				$files[] = ClassReflection::from('Doctrine\Common\Version')->getFileName();
				$data = $this->getLatestByGithub('doctrine', 'common', \Doctrine\Common\Version::VERSION);
				if (!empty($data))
					$this->updates['Doctrine Common'] = $data;
			}
			if (class_exists('Doctrine\DBAL\Version')) {
				$files[] = ClassReflection::from('Doctrine\DBAL\Version')->getFileName();
				$data = $this->getLatestByGithub('doctrine', 'dbal', \Doctrine\DBAL\Version::VERSION);
				if (!empty($data))
					$this->updates['Doctrine DBAL'] = $data;
			}
			if (class_exists('Doctrine\ORM\Version')) {
				$files[] = ClassReflection::from('Doctrine\ORM\Version')->getFileName();
				$data = $this->getLatestByGithub('doctrine', 'doctrine2', \Doctrine\ORM\Version::VERSION);
				if (!empty($data))
					$this->updates['Doctrine ORM'] = $data;
			}
			$data = $this->getLatestByGithub('nella', 'versionpanel', self::VERSION);
			if (!empty($data))
				$this->updates['VersionPanel'] = $data;
			if (class_exists('Nella\CallbackPanel') && defined('Nella\CallbackPanel::VERSION')) {
				$files[] = ClassReflection::from('Nella\CallbackPanel')->getFileName();
				$data = $this->getLatestByGithub('nella', 'callbackpanel', \Nella\CallbackPanel::VERSION);
				if (!empty($data))
					$this->updates['CallbakcPanel'] = $data;
			}
			if (class_exists('NetteTranslator\Panel') && defined('NetteTranslator\Panel::VERSION')) {
				$files[] = ClassReflection::from('NetteTranslator\Panel')->getFileName();
				$data = $this->getLatestByGithub('vrtak-cz', 'nettetranslator', \NetteTranslator\Panel::VERSION);
				if (!empty($data))
					$this->updates['NetteTranslator'] = $data;
			}
			if (class_exists('Nette\Mail\SmtpMailer') && defined('Nette\Mail\SmtpMailer::VERSION')) {
				$files[] = ClassReflection::from('Nette\Mail\SmtpMailer')->getFileName();
				$data = $this->getLatestByGithub('nella', 'smtpmailer', \Nette\Mail\SmtpMailer::VERSION);
				if (!empty($data))
					$this->updates['SMTP Mailer'] = $data;
			}

			$files[] = __FILE__;
			$files[] = __DIR__ . "/version.tab.phtml";
			$files[] = __DIR__ . "/version.panel.phtml";
			$cache->save('updates', $this->updates, array('expire' => time() + 60 * 60 * 2, 'files' => $files));
		} elseif (isset($cache['updates']))
			$this->updates = $cache['updates'];
	}

	/**
	 * Get cURL response
	 *
	 * @param string $url
	 * @return string
	 */
	private function getCurlResponse($url)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_USERAGENT,
				"Mozilla/5.0 (compatible; NetteVersionPanel/".self::VERSION."; http://addons.nette.org/cs/versionpanel)");
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		$headers = array(
			"HTTP_ACCEPT: text/javascript,text/json,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8*/*;q=0.5",
			"HTTP_ACCEPT_CHARSET: windows-1250,utf-8;q=0.7,*;q=0.7",
			"HTTP_KEEP_ALIVE: 300",
			"HTTP_CONNECTION: keep-alive"

		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$res = curl_exec($ch);
		if (curl_errno($ch) && curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
			curl_close($ch);
			return NULL;
		}
		curl_close($ch);
		return substr($res, strpos($res, "\n\r\n") !== FALSE ? strpos($res, "\n\r\n") + 3 : 0);
	}

	/**
	 * Get response by array
	 *
	 * @param string $url
	 * @return array
	 */
	private function getArrayResponse($url)
	{
		return json_decode($this->getCurlResponse($url), TRUE);
	}

	/**
	 * Get latest version by GitHub API
	 *
	 * @param string $userId
	 * @param string $repo
	 * @param string $version
	 * @param string $revision
	 * @return array|NULL
	 */
	private function getLatestByGithub($userId, $repo, $version, $revision = NULL)
	{
		$dev = FALSE;
		if (strpos($version, 'dev') !== FALSE) {
			$dev = TRUE;
			$version = substr($version, 0, strpos($version, "-dev"));
		}
		$tags = $this->getArrayResponse("http://github.com/api/v2/json/repos/show/$userId/$repo/tags");
		if (empty($tags) || !array_key_exists('tags', $tags))
			return NULL;

		$keys = array_keys($tags['tags']);
		sort($keys);
		$latest = !(bool)count(array_filter($keys,
			function ($input) use ($version, $repo)
			{
				return version_compare(strpos($input, 'v') !== FALSE ? substr($input, 1) : $input, $version, '>');
			}
		));
		if (!$latest && !$dev) {
			$tag = $keys[count($keys)-1];
			$commit = $tags['tags'][$tag];

			$commitData = $this->getArrayResponse("http://github.com/api/v2/json/commits/show/$userId/$repo/".$commit);
			$date = $commitData['commit']['authored_date'];
			$timeZone = ini_get("date.timezone");
			if (!empty($timeZone))
				$date = date_create($date)->setTimezone(new \DateTimeZone($timeZone))->format("c");

			$data = array(
				'version' => strpos($tag, 'v') !== FALSE ? substr($tag, 1) : $tag,
				'revision' => substr($commit, 0, 7)." released on ".substr($date, 0, 10),
			);

			if ($repo == 'nette')
				$data['url'] = "http://files.nette.org/NetteFramework-".substr($tag, 1)."-PHP5.3.zip";
			elseif ($repo == 'dibi')
				$data['url'] = "http://files.dibiphp.com/dibi-".substr($tag, 1).".zip";
			elseif ($repo == 'texy')
				$data['url'] = "http://files.texy.info/latest.zip";
			elseif ($userId == 'doctrine' && $repo == 'common')
				$data['url'] = "http://github.com/doctrine/common/zipball/".$tag;
			elseif ($userId == 'doctrine' && $repo == 'dbal')
				$data['url'] = "http://github.com/doctrine/dbal/zipball/".$tag;
			elseif ($repo == 'doctrine2')
				$data['url'] = "http://github.com/doctrine/doctrine2/zipball/".$tag;
			elseif ($repo == 'versionpanel')
				$data['url'] = "http://github.com/nella/versionpanel/zipball/".$tag;
			elseif ($repo == 'callbackpanel')
				$data['url'] = "http://github.com/nella/callbackpanel/zipball/".$tag;
			elseif ($repo == 'nettetranslator')
				$data['url'] = "http://github.com/vrtak-cz/nettetranslator/zipball/".$tag;
			elseif ($repo == 'smtpmailer')
				$data['url'] = "http://github.com/nella/smtpmailer/zipball/".$tag;


			return $data;
		}
		
		if (!$dev)
			return NULL;

		$commits = $this->getArrayResponse("http://github.com/api/v2/json/commits/list/$userId/$repo/master");
		if (empty($commits) || !array_key_exists('commits', $commits) || !array_key_exists(0, $commits['commits']) || empty($revision))
			return NULL;

		if (substr($commits['commits'][0]['id'], 0, 7) != substr($revision, 0, 7)) {
			$date = $commits['commits'][0]['authored_date'];
			$timeZone = ini_get("date.timezone");
			if (!empty($timeZone))
				$date = date_create($date)->setTimezone(new \DateTimeZone($timeZone))->format("c");

			$data = array(
				'version' => $version."-dev",
				'revision' => substr($commits['commits'][0]['id'], 0, 7)." released on ".substr($date, 0, 10),
			);

			if ($repo == 'nette')
				$data['url'] = "http://files.nette.org/NetteFramework-{$version}dev-PHP5.3.zip";
			elseif ($repo == 'dibi')
				$data['url'] = "http://files.dibiphp.com/latest.zip";
			elseif ($repo == 'texy')
				$data['url'] = "http://files.texy.info/texy-{$version}-dev.zip";

			return $data;
		}

		return NULL;
	}

	/**
	 * Register this panel
	 */
	public static function register()
	{
		if (static::$registered)
			throw new \InvalidStateException("Version panel is already registered");
		
		\Nette\Debug::addPanel(new static);
		static::$registered = TRUE;
	}
}