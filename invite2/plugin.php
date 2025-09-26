<?php
// PLUGIN INFORMATION
$GLOBALS['plugins']['Invites2'] = array( // Plugin Name
	'name' => 'Invites2', // Plugin Name
	'author' => 'CauseFX', // Who wrote the plugin
	'category' => 'Management', // One to Two Word Description
	'link' => '', // Link to plugin info
	'license' => 'personal', // License Type use , for multiple
	'idPrefix' => 'Invites2', // html element id prefix
	'configPrefix' => 'Invites2', // config file prefix for array items without the hyphen
	'version' => '1.1.0', // SemVer of plugin
	'image' => 'api/plugins/Invites2/logo.png', // 1:1 non transparent image for plugin
	'settings' => true, // does plugin need a settings modal?
	'bind' => true, // use default bind to make settings page - true or false
	'api' => 'api/v2/plugins/Invites2/settings', // api route for settings page
	'homepage' => false // Is plugin for use on homepage? true or false
);

class Invites2 extends Organizr
{
	public function __construct()
	{
		parent::__construct();
		$this->_pluginUpgradeCheck();
	}

	public function _pluginUpgradeCheck()
	{
		if ($this->hasDB()) {
			$compare = new Composer\Semver\Comparator;
			$oldVer = $this->config['Invites2-dbVersion'];
			// Upgrade check start for version below
			$versionCheck = '1.1.0';
			if ($compare->lessThan($oldVer, $versionCheck)) {
				$oldVer = $versionCheck;
				$this->_pluginUpgradeToVersion($versionCheck);
			}
			// End Upgrade check start for version above
			// Update config.php version if different to the installed version
			if ($GLOBALS['plugins']['Invites2']['version'] !== $this->config['Invites2-dbVersion']) {
				$this->updateConfig(array('Invites2-dbVersion' => $oldVer));
				$this->setLoggerChannel('Invites2 Plugin');
				$this->logger->debug('Updated Invites2-dbVersion to ' . $oldVer);
			}
			return true;
		}
	}

	public function _pluginUpgradeToVersion($version = '1.1.0')
	{
		switch ($version) {
			case '1.1.0':
				$this->_addInvitedByColumnToDatabase();
				break;
		}
		$this->setResponse(200, 'Ran plugin update function for version: ' . $version);
		return true;
	}

	public function _addInvitedByColumnToDatabase()
	{
		$addColumn = $this->addColumnToDatabase('Invites2', 'invitedby', 'TEXT');
		$this->setLoggerChannel('Invites2 Plugin');
		if ($addColumn) {
			$this->logger->info('Updated Invites2 Database');
		} else {
			$this->logger->warning('Could not update Invites2 Database');
		}
	}

	public function _Invites2PluginGetCodes()
	{
		if ($this->qualifyRequest(1, false)) {
			$response = [
				array(
					'function' => 'fetchAll',
					'query' => 'SELECT * FROM Invites2'
				)
			];
		} else {
			$response = [
				array(
					'function' => 'fetchAll',
					'query' => array(
						'SELECT * FROM Invites2 WHERE invitedby = ?',
						$this->user['username']
					)
				)
			];
		}

		return $this->processQueries($response);
	}

	public function _Invites2PluginCreateCode($array)
	{
		$code = ($array['code']) ?? null;
		$username = ($array['username']) ?? null;
		$email = ($array['email']) ?? null;
		$Invites2 = $this->_Invites2PluginGetCodes();
		$inviteCount = count($Invites2);
		if (!$this->qualifyRequest(1, false)) {
			if ($this->config['Invites2-maximum-Invites2'] != 0 && $inviteCount >= $this->config['Invites2-maximum-Invites2']) {
				$this->setAPIResponse('error', 'Maximum number of Invites2 reached', 409);
				return false;
			}
		}
		if (!$code) {
			$this->setAPIResponse('error', 'Code not supplied', 409);
			return false;
		}
		if (!$username) {
			$this->setAPIResponse('error', 'Username not supplied', 409);
			return false;
		}
		if (!$email) {
			$this->setAPIResponse('error', 'Email not supplied', 409);
			return false;
		}
		$newCode = [
			'code' => $code,
			'email' => $email,
			'username' => $username,
			'valid' => 'Yes',
			'type' => $this->config['Invites2-type-include'],
			'invitedby' => $this->user['username'],
			'date' => gmdate('Y-m-d H:i:s')
		];
		$response = [
			array(
				'function' => 'query',
				'query' => array(
					'INSERT INTO [Invites2]',
					$newCode
				)
			)
		];
		$query = $this->processQueries($response);
		if ($query) {
			$this->setLoggerChannel('Invites2')->info('Added Invite [' . $code . ']');
			if ($this->config['PHPMAILER-enabled']) {
				$PhpMailer = new PhpMailer();
				$emailTemplate = array(
					'type' => 'invite',
					'body' => $this->config['PHPMAILER-emailTemplateInviteUser'],
					'subject' => $this->config['PHPMAILER-emailTemplateInviteUserSubject'],
					'user' => $username,
					'password' => null,
					'inviteCode' => $code,
				);
				$emailTemplate = $PhpMailer->_phpMailerPluginEmailTemplate($emailTemplate);
				$sendEmail = array(
					'to' => $email,
					'subject' => $emailTemplate['subject'],
					'body' => $PhpMailer->_phpMailerPluginBuildEmail($emailTemplate),
				);
				$PhpMailer->_phpMailerPluginSendEmail($sendEmail);
			}
			$this->setAPIResponse('success', 'Invite Code: ' . $code . ' has been created', 200);
			return true;
		} else {
			return false;
		}
	}

	public function _Invites2PluginVerifyCode($code)
	{
		$response = [
			array(
				'function' => 'fetchAll',
				'query' => array(
					'SELECT * FROM Invites2 WHERE valid = "Yes" AND code = ? COLLATE NOCASE',
					$code
				)
			)
		];
		if ($this->processQueries($response)) {
			$this->setAPIResponse('success', 'Code has been verified', 200);
			return true;
		} else {
			$this->setAPIResponse('error', 'Code is invalid', 401);
			return false;
		}
	}

	public function _Invites2PluginDeleteCode($code)
	{
		if ($this->qualifyRequest(1, false)) {
			$response = [
				array(
					'function' => 'fetch',
					'query' => array(
						'SELECT * FROM Invites2 WHERE code = ? COLLATE NOCASE',
						$code
					)
				)
			];
		} else {
			if ($this->config['Invites2-allow-delete']) {
				$response = [
					array(
						'function' => 'fetch',
						'query' => array(
							'SELECT * FROM Invites2 WHERE invitedby = ? AND code = ? COLLATE NOCASE',
							$this->user['username'],
							$code
						)
					)
				];
			} else {
				$this->setAPIResponse('error', 'You are not permitted to delete Invites2.', 409);
				return false;
			}
		}
		$info = $this->processQueries($response);
		if (!$info) {
			$this->setAPIResponse('error', 'Code not found', 404);
			return false;
		}
		$response = [
			array(
				'function' => 'query',
				'query' => array(
					'DELETE FROM Invites2 WHERE code = ? COLLATE NOCASE',
					$code
				)
			)
		];
		$this->setAPIResponse('success', 'Code has been deleted', 200);
		return $this->processQueries($response);
	}

	public function _Invites2PluginUseCode($code, $array)
	{
		$code = ($code) ?? null;
		$usedBy = ($array['usedby']) ?? null;
		$now = date("Y-m-d H:i:s");
		$currentIP = $this->userIP();
		if ($this->_Invites2PluginVerifyCode($code)) {
			$updateCode = [
				'valid' => 'No',
				'usedby' => $usedBy,
				'dateused' => $now,
				'ip' => $currentIP
			];
			$response = [
				array(
					'function' => 'query',
					'query' => array(
						'UPDATE Invites2 SET',
						$updateCode,
						'WHERE code=? COLLATE NOCASE',
						$code
					)
				)
			];
			$query = $this->processQueries($response);
			$this->setLoggerChannel('Invites2')->info('Invite Used [' . $code . ']');
			return $this->_Invites2PluginAction($usedBy, 'share', $this->config['Invites2-type-include']);
		} else {
			return false;
		}
	}

	public function _Invites2PluginLibraryList($type = null)
	{
		switch ($type) {
			case 'plex':
				if (!empty($this->config['plexToken']) && !empty($this->config['plexID'])) {
					$url = 'https://plex.tv/api/servers/' . $this->config['plexID'];
					try {
						$headers = array(
							"Accept" => "application/json",
							"X-Plex-Token" => $this->config['plexToken']
						);
						$response = Requests::get($url, $headers, array());
						libxml_use_internal_errors(true);
						if ($response->success) {
							$libraryList = array();
							$plex = simplexml_load_string($response->body);
							foreach ($plex->Server->Section as $child) {
								$libraryList['libraries'][(string)$child['title']] = (string)$child['id'];
							}
							if ($this->config['Invites2-plexLibraries'] !== '') {
								$noLongerId = 0;
								$libraries = explode(',', $this->config['Invites2-plexLibraries']);
								foreach ($libraries as $child) {
									if (!$this->search_for_value($child, $libraryList)) {
										$libraryList['libraries']['No Longer Exists - ' . $noLongerId] = $child;
										$noLongerId++;
									}
								}
							}
							$libraryList = array_change_key_case($libraryList, CASE_LOWER);
							return $libraryList;
						}
					} catch (Requests_Exception $e) {
						$this->setLoggerChannel('Plex')->error($e);
						return false;
					};
				}
				break;
			default:
				# code...
				break;
		}
		return false;
	}

	public function _Invites2PluginGetSettings()
	{
		if ($this->config['plexID'] !== '' && $this->config['plexToken'] !== '' && $this->config['Invites2-type-include'] == 'plex') {
			$loop = $this->_Invites2PluginLibraryList($this->config['Invites2-type-include'])['libraries'];
			foreach ($loop as $key => $value) {
				$libraryList[] = array(
					'name' => $key,
					'value' => $value
				);
			}
		} else {
			$libraryList = array(
				array(
					'name' => 'Refresh page to update List',
					'value' => '',
					'disabled' => true,
				),
			);
		}
		return array(
			'Backend' => array(
				array(
					'type' => 'select',
					'name' => 'Invites2-type-include',
					'label' => 'Media Server',
					'value' => $this->config['Invites2-type-include'],
					'options' => array(
						array(
							'name' => 'N/A',
							'value' => 'n/a'
						),
						array(
							'name' => 'Plex',
							'value' => 'plex'
						),
						array(
							'name' => 'Emby',
							'value' => 'emby'
						)
					)
				),
				array(
					'type' => 'select',
					'name' => 'Invites2-Auth-include',
					'label' => 'Minimum Authentication',
					'value' => $this->config['Invites2-Auth-include'],
					'options' => $this->groupSelect()
				),
				array(
					'type' => 'switch',
					'name' => 'Invites2-allow-delete-include',
					'label' => 'Allow users to delete Invites2',
					'help' => 'This must be disabled to enforce invitation limits.',
					'value' => $this->config['Invites2-allow-delete-include']
				),
				array(
					'type' => 'number',
					'name' => 'Invites2-maximum-Invites2',
					'label' => 'Maximum number of Invites2 permitted for users.',
					'help' => 'Set to 0 to disable the limit.',
					'value' => $this->config['Invites2-maximum-Invites2'],
					'placeholder' => '0'
				),
			),
			'Plex Settings' => array(
				array(
					'type' => 'password-alt',
					'name' => 'plexToken',
					'label' => 'Plex Token',
					'value' => $this->config['plexToken'],
					'placeholder' => 'Use Get Token Button'
				),
				array(
					'type' => 'button',
					'label' => 'Get Plex Token',
					'icon' => 'fa fa-ticket',
					'text' => 'Retrieve',
					'attr' => 'onclick="PlexOAuth(oAuthSuccess,oAuthError, oAuthMaxRetry, null, null, \'#Invites2-settings-items [name=plexToken]\')"'
				),
				array(
					'type' => 'password-alt',
					'name' => 'plexID',
					'label' => 'Plex Machine',
					'value' => $this->config['plexID'],
					'placeholder' => 'Use Get Plex Machine Button'
				),
				array(
					'type' => 'button',
					'label' => 'Get Plex Machine',
					'icon' => 'fa fa-id-badge',
					'text' => 'Retrieve',
					'attr' => 'onclick="showPlexMachineForm(\'#Invites2-settings-items [name=plexID]\')"'
				),
				array(
					'type' => 'select2',
					'class' => 'select2-multiple',
					'id' => 'invite-select-' . $this->random_ascii_string(6),
					'name' => 'Invites2-plexLibraries',
					'label' => 'Libraries',
					'value' => $this->config['Invites2-plexLibraries'],
					'options' => $libraryList
				),
				array(
					'type' => 'text',
					'name' => 'Invites2-plex-tv-labels',
					'label' => 'TV Labels (comma separated)',
					'value' => $this->config['Invites2-plex-tv-labels'],
					'placeholder' => 'All'
				),
				array(
					'type' => 'text',
					'name' => 'Invites2-plex-movies-labels',
					'label' => 'Movies Labels (comma separated)',
					'value' => $this->config['Invites2-plex-movies-labels'],
					'placeholder' => 'All'
				),
				array(
					'type' => 'text',
					'name' => 'Invites2-plex-music-labels',
					'label' => 'Music Labels (comma separated)',
					'value' => $this->config['Invites2-plex-music-labels'],
					'placeholder' => 'All'
				),
			),
			'Emby Settings' => array(
				array(
					'type' => 'password-alt',
					'name' => 'embyToken',
					'label' => 'Emby API key',
					'value' => $this->config['embyToken'],
					'placeholder' => 'enter key from emby'
				),
				array(
					'type' => 'text',
					'name' => 'embyURL',
					'label' => 'Emby server adress',
					'value' => $this->config['embyURL'],
					'placeholder' => 'localhost:8086'
				),
				array(
					'type' => 'text',
					'name' => 'Invites2-EmbyTemplate',
					'label' => 'Emby User to be used as template for new users',
					'value' => $this->config['Invites2-EmbyTemplate'],
					'placeholder' => 'AdamSmith'
				)
			),
			'FYI' => array(
				array(
					'type' => 'html',
					'label' => 'Note',
					'html' => '<span lang="en">After enabling for the first time, please reload the page - Menu is located under User menu on top right</span>'
				)
			)
		);
	}

	public function _Invites2PluginAction($username, $action = null, $type = null)
	{
		if ($action == null) {
			$this->setAPIResponse('error', 'No Action supplied', 409);
			return false;
		}
		switch ($type) {
			case 'plex':
				if (!empty($this->config['plexToken']) && !empty($this->config['plexID'])) {
					$url = "https://plex.tv/api/servers/" . $this->config['plexID'] . "/shared_servers/";
					if ($this->config['Invites2-plexLibraries'] !== "") {
						$libraries = explode(',', $this->config['Invites2-plexLibraries']);
					} else {
						$libraries = '';
					}
					if ($this->config['Invites2-plex-tv-labels'] !== "") {
						$tv_labels = "label=" . $this->config['Invites2-plex-tv-labels'];
					} else {
						$tv_labels = "";
					}
					if ($this->config['Invites2-plex-movies-labels'] !== "") {
						$movies_labels = "label=" . $this->config['Invites2-plex-movies-labels'];
					} else {
						$movies_labels = "";
					}
					if ($this->config['Invites2-plex-music-labels'] !== "") {
						$music_labels = "label=" . $this->config['Invites2-plex-music-labels'];
					} else {
						$music_labels = "";
					}
					$headers = array(
						"Accept" => "application/json",
						"Content-Type" => "application/json",
						"X-Plex-Token" => $this->config['plexToken']
					);
					$data = array(
						"server_id" => $this->config['plexID'],
						"shared_server" => array(
							"library_section_ids" => $libraries,
							"invited_email" => $username
						),
						"sharing_settings" => array(
							"filterTelevision" => $tv_labels,
							"filterMovies" => $movies_labels,
							"filterMusic" => $music_labels
						)
					);
					try {
						switch ($action) {
							case 'share':
								$response = Requests::post($url, $headers, json_encode($data), array());
								break;
							case 'unshare':
								$id = (is_numeric($username) ? $username : $this->_Invites2PluginConvertPlexName($username, "id"));
								$url = $url . $id;
								$response = Requests::delete($url, $headers, array());
								break;
							default:
								$this->setAPIResponse('error', 'No Action supplied', 409);
								return false;
						}
						if ($response->success) {
							$this->setLoggerChannel('Invites2')->info('Plex User now has access to system');
							$this->setAPIResponse('success', 'Plex User now has access to system', 200);
							return true;
						} else {
							switch ($response->status_code) {
								case 400:
									$this->setLoggerChannel('Plex')->warning('Plex User already has access');
									$this->setAPIResponse('error', 'Plex User already has access', 409);
									return false;
								case 401:
									$this->setLoggerChannel('Plex')->warning('Incorrect Token');
									$this->setAPIResponse('error', 'Incorrect Token', 409);
									return false;
								case 404:
									$this->setLoggerChannel('Plex')->warning('Libraries not setup correctly');
									$this->setAPIResponse('error', 'Libraries not setup correct', 409);
									return false;
								default:
									$this->setLoggerChannel('Plex')->warning('An error occurred [' . $response->status_code . ']');
									$this->setAPIResponse('error', 'An Error Occurred', 409);
									return false;
							}
						}
					} catch (Requests_Exception $e) {
						$this->setLoggerChannel('Plex')->error($e);
						$this->setAPIResponse('error', $e->getMessage(), 409);
						return false;
					}
				} else {
					$this->setLoggerChannel('Plex')->warning('Plex Token/ID not set');
					$this->setAPIResponse('error', 'Plex Token/ID not set', 409);
					return false;
				}
				break;
			case 'emby':
				try {
					#add emby user to system
					$this->setAPIResponse('success', 'User now has access to system', 200);
					return true;
				} catch (Requests_Exception $e) {
					$this->setLoggerChannel('Emby')->error($e);
					$this->setAPIResponse('error', $e->getMessage(), 409);
					return false;
				}
			default:
				return false;
		}
		return false;
	}

	public function _Invites2PluginConvertPlexName($user, $type)
	{
		$array = $this->userList('plex');
		switch ($type) {
			case "username":
			case "u":
				$plexUser = array_search($user, $array['users']);
				break;
			case "id":
				if (array_key_exists(strtolower($user), $array['users'])) {
					$plexUser = $array['users'][strtolower($user)];
				}
				break;
			default:
				$plexUser = false;
		}
		return (!empty($plexUser) ? $plexUser : null);
	}

}
