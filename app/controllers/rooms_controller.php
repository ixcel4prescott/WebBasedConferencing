<?

class RoomsController extends AppController {
//01-13-2014 GW Modified for Smartcloud Room
	var $uses = Array('RoomView', 'Room', 'Account', 'AccountView', 'ResellerGroup', 'SalespersonGroup', 'DefaultBridge', 'Reseller', 'Salesperson', 'Bridge', 'Request', 'DialinNumber', 'Note', 'WebinterpointAccount', 'SmartcloudAccount', 'RequestView', 'Contact', 'Bridge', 'Status', 'Contact', 'Usage', 'WelcomeEmailLog', 'ConferenceReport', 'BridgeSettings', 'ServiceType', 'BillingMethod', 'AccountBillingMethod', 'RoomBillingMethod', 'Product', 'WebinterpointRoom', 'SmartCloudRoom', 'WebexRoom', 'LiveMeetingRoom');
	var $components = Array('Pagination', 'Email', 'RequestHandler');
	var $helpers = Array('Html', 'Pagination', 'Time');

	var $permissions = GROUP_ALL;

	//-----------------------------------------------------------------------------
	//
	//  Helpers for actions
	//
	//-----------------------------------------------------------------------------

	// Set the defaults for the bridges
	private function set_bridge_defaults($bridge, $accountBillingID) {
		//These will be based off of the generic settings not the bridge settings. Need to check in testing though.
		//Turning these all off for now. These will be eventually worked into a profile system where the defaults will be based off of a profile.
		$this -> data['Room']['startmode'] = 0;
		// Interactive
		$this -> data['Room']['securitytype'] = 0;
		// No PIN
		$this -> data['Room']['scheduletype'] = 0;
		// Unattended
		$this -> data['Room']['namerecording'] = 0;
		// Off, This is the generic value not the Spectel setting of 1 for off.
		$this -> data['Room']['entryannouncement'] = 1;
		// Tone
		$this -> data['Room']['exitannouncement'] = 1;
		// Tone
		$this -> data['Room']['conference_viewer'] = 0;
		// CHECKED
		$this -> data['Room']['endonchairhangup'] = 0;
		// No
		$this -> data['Room']['dialout'] = 0;
		// Off
		$this -> data['Room']['endingsignal'] = 0;
		// Tone
		$this -> data['Room']['dtmfsignal'] = 0;
		// System Message
		$this -> data['Room']['recordingsignal'] = 0;
		// Play System Message
		$this -> data['Room']['digitentry1'] = 0;
		// Off
		$this -> data['Room']['confirmdigitentry1'] = 0;
		// No
		$this -> data['Room']['digitentry2'] = 0;
		// Off
		$this -> data['Room']['confirmdigitentry2'] = 0;
		// No
		$this -> data['Room']['muteallduringplayback'] = 0;
		// No
		$this -> data['Room']['record_playback'] = 0;
		// No
		$this -> data['Room']['bill_code_prompt'] = 0;
		// No
		$this -> data['Room']['billing_method_id'] = $accountBillingID;
	}

	// Get the accounts default bridge
	private function get_default_bridge($acctgrpid) {
		$default_bridge = $this -> DefaultBridge -> read(null, $acctgrpid);

		if (!empty($this -> data['Room']['bridgeid'])) {
			$bridge = $this -> data['Room']['bridgeid'];
		} else {
			$bridge = $default_bridge;
			if ($bridge) {
				$bridge = $bridge['DefaultBridge']['bridge_id'];
			} else {
				// guessing based on the first created room
				$first_room = $this -> Room -> find(Array('Room.acctgrpid' => $acctgrpid, 'roomstat' => 0), null, 'roomstatdate ASC');
				$bridge = $first_room ? $first_room['Room']['bridgeid'] : SPECTEL_BRIDGEID;
			}
		}

		if (!$default_bridge)
			$this -> set('bridge_error', 'no_default');
		elseif ($default_bridge['DefaultBridge']['bridge_id'] != $bridge)
			$this -> set('bridge_error', 'not_default');
		else
			$this -> set('bridge_error', null);

		return $bridge;
	}

	private function setup_room_select($BridgeSettings, $symbol, $selectList) {
		$temp = array();
		foreach ($BridgeSettings as $row) {
			if ($row['symbol'] == $symbol) {
				$temp[$row['value']] = $row['setting'];
			}
		}
		$this -> set($selectList, $temp);
	}

	private function build_dialin_list($resellerid, $account = null, $bridge) {
		$dialin_numbers = a();
		$dialin_map = a();
		$default_dialin = null;

		foreach ($this->DialinNumber->get($resellerid, $bridge) as $i) {
			$dialin_numbers[$i[0]['id']] = $i[0]['description'];
			$dialin_map[$i[0]['id']] = a($i[0]['tollfreeno'], $i[0]['tollno']);

			if ($i[0]['default'])
				$default_dialin = $i[0]['id'];
		}

		$this -> set('dialin_number_error', false);

		if ($account) {
			// honor specified and account default dialin numbers past just the resellers default
			if (!empty($this -> data['Room']['dialinNoid']) && isset($dialin_numbers[$this -> data['Room']['dialinNoid']])) {
				$default_dialin = $this -> data['Room']['dialinNoid'];
			} elseif ($account['Account']['dialinNoid'] && isset($dialin_numbers[$account['Account']['dialinNoid']])) {
				$default_dialin = $account['Account']['dialinNoid'];
			} elseif ($account['Account']['dialinNoid'] || empty($dialin_numbers)) {
				$this -> systemLog('dialin number alignment error', $account['Account']['acctgrpid']);
				$this -> set('dialin_number_error', true);
			}
		}

		return Array($dialin_numbers, $dialin_map, $default_dialin);
	}

	private function setup_room_form($bridge, $BridgeSettings) {
		$this -> set('room_statuses', $this -> Status -> generateList(null, 'description ASC', null, '{n}.Status.acctstat', '{n}.Status.description'));
		$this -> set('languages', $this -> Room -> languages);

		$this -> setup_room_select($BridgeSettings, 'scheduletype', 'schedule_types');
		$this -> setup_room_select($BridgeSettings, 'securitytype', 'security_types');
		$this -> setup_room_select($BridgeSettings, 'startmode', 'start_modes');
		$this -> setup_room_select($BridgeSettings, 'namerecording', 'namerecording');
		$this -> setup_room_select($BridgeSettings, 'endonchairhangup', 'endonchairhangup');
		$this -> setup_room_select($BridgeSettings, 'dialout', 'dialout');
		$this -> setup_room_select($BridgeSettings, 'record_playback', 'record_playback');
		$this -> setup_room_select($BridgeSettings, 'entryannouncement', 'entryannouncement');
		$this -> setup_room_select($BridgeSettings, 'exitannouncement', 'exitannouncement');
		$this -> setup_room_select($BridgeSettings, 'endingsignal', 'endingsignal');
		$this -> setup_room_select($BridgeSettings, 'dtmfsignal', 'dtmfsignal');
		$this -> setup_room_select($BridgeSettings, 'recordingsignal', 'recordingsignal');
		$this -> setup_room_select($BridgeSettings, 'digitentry1', 'digitentry1');
		$this -> setup_room_select($BridgeSettings, 'confirmdigitentry1', 'confirmdigitentry1');
		$this -> setup_room_select($BridgeSettings, 'digitentry2', 'digitentry2');
		$this -> setup_room_select($BridgeSettings, 'confirmdigitentry2', 'confirmdigitentry2');
		$this -> setup_room_select($BridgeSettings, 'muteallduringplayback', 'muteallduringplayback');
		$this -> setup_room_select($BridgeSettings, 'conference_viewer', 'conference_viewer');
		$this -> setup_room_select($BridgeSettings, 'bill_code_prompt', 'bill_code_prompt');
	}

	private function convert_room($room, $bridgeSettingMap) {
		$tempRoom = array();
		foreach ($room as $symbol => $val) {

			if (array_key_exists($symbol, $bridgeSettingMap)) {

				$tempRoom[$symbol] = $bridgeSettingMap[$symbol][$val];
			} else {
				$tempRoom[$symbol] = $val;
			}
		}
		return $tempRoom;
	}

	//-----------------------------------------------------------------------------
	//
	//  Public Actions
	//
	//-----------------------------------------------------------------------------

	function associate($accountid = null) {
		$this -> pageTitle = 'Contact';

		if ($accountid) {
			$user = $this -> Session -> read('User');

			if ($room = $this -> Room -> get($accountid, $user)) {
				if (!empty($this -> data)) {

					if ($contact = $this -> Contact -> read(null, $this -> data['Contact']['id'])) {
						$full_name = $this -> Contact -> fullName($contact);

						if ($this -> Contact -> associateRoom($contact, $room) == 0) {
							$this -> Session -> setFlash(sprintf('Assigned room %s to contact %s', $room['Room']['accountid'], $full_name));
						} else {
							$this -> Session -> setFlash(sprintf('Could not associate room %s with contact %s', $room['Room']['accountid'], $full_name));
						}

						$this -> redirect('/rooms/view/' . $room['Room']['accountid']);

					} else {
						$this -> Session -> setFlash('Contact not found');
						$this -> redirect('/contacts');
					}
				}

			} else {
				$this -> Session -> setFlash('Room not found');
				$this -> redirect('/rooms');
			}
		} else {
			$this -> Session -> setFlash('No Room specified');
			$this -> redirect('/rooms');
		}
	}

	function create($contact_id = null) {
		$user = $this -> Session -> read('User');

		$this -> Contact -> recursive = 0;
		$this -> Account -> recursive = 0;

		$contact = $this -> Contact -> get($contact_id, $user);
		$account = $this -> Account -> get($contact['Contact']['acctgrpid'], $user);

		if ($contact && $account) {
			$this -> set('account', $account);
			$this -> set('contact', $contact);

			$full_name = $this -> Contact -> fullName($contact);
			$this -> set('full_name', $full_name);

			$this -> Salesperson -> recursive = 0;
			$salesperson = $this -> Salesperson -> read(null, $contact['Account']['salespid']);
			$this -> set('salesperson', $salesperson);

			$reseller = $this -> Reseller -> read(null, $salesperson['Salesperson']['resellerid']);
			$this -> set('reseller', $reseller);

			// can the user set maxconnections
			$can_set_maxconnections = true;
			if ($user['User']['ic_employee'] == 0 || $user['User']['level_type'] == 'salesperson')
				$can_set_maxconnections = false;
			$this -> set('can_set_maxconnections', $can_set_maxconnections);

			// select appropriate bridge id for use
			$bridges = $this -> Bridge -> generateList(Array('active' => 1, 'type' => AUDIO_BRIDGE), 'id ASC', null, '{n}.Bridge.id', '{n}.Bridge.name');
			$this -> set('bridges', $bridges);

			$bridge = $this -> get_default_bridge($account['Account']['acctgrpid']);
			$this -> set('bridge', $bridge);

			$BridgeSettings = $this -> BridgeSettings -> getBridgeSettings($bridge);
			$accountGroupBillingMethodID = $this -> AccountBillingMethod -> find(array('acctgrpid' => $account['Account']['acctgrpid']), array('billing_method_id'));
			$billingMethods = $this -> BillingMethod -> generateList(null, null, null, '{n}.BillingMethod.id', '{n}.BillingMethod.description');

			$this -> set('activeBridgeSettings', pluck($BridgeSettings, 'symbol'));
			$this -> set('bridgeSettingsDetails', $BridgeSettings);
			$this -> set('billingMethods', $billingMethods);

			$products = $this -> Product -> generateList(null, null, null, '{n}.Product.code', '{n}.Product.name');
			$this -> set('products', $products);

			$this -> setup_room_form($bridge, $BridgeSettings);

			$BridgeSettingDescriptions = $this -> BridgeSettings -> getBridgeSettingDescriptions($bridge);
			$this -> set('settingsDescription', $BridgeSettingDescriptions);

			list($dialin_numbers, $dialin_map, $default_dialin) = $this -> build_dialin_list($reseller['Reseller']['resellerid'], $account, isset($bridges[$bridge]) ? $bridges[$bridge] : '');
			$this -> set('dialin_numbers', $dialin_numbers);
			$this -> set('dialin_map', $dialin_map);
			$this -> set('default_dialin', $default_dialin);

			// Submit via bridge drop down change, set defaults
			if (!empty($this -> data['Page']['switch_bridge']) && $this -> data['Page']['switch_bridge']) {
				$this -> set_bridge_defaults($bridge, $accountGroupBillingMethodID['AccountBillingMethod']['billing_method_id']);
				unset($this -> data['Page']['switch_bridge']);
			}
			//print_r($this -> data);
			// Submit via submit button click
			if (!empty($this -> data) && isset($_POST['manual'])) {
				$this -> data['Room']['resellerid'] = $reseller['Reseller']['resellerid'];

				$this -> Room -> set($this -> data);

				if ($this -> Room -> validates($this -> data)) {

					if (!empty($this -> data['Room']['startdate_date']) && !empty($this -> data['Room']['startdate_hour']) && !empty($this -> data['Room']['startdate_min']) && !empty($this -> data['Room']['startdate_meridian']))
						$this -> data['Room']['startdate'] = date('Ymd H:i:s', strtotime($this -> data['Room']['startdate_date'] . ' ' . $this -> data['Room']['startdate_hour'] . ':' . $this -> data['Room']['startdate_min'] . ' ' . $this -> data['Room']['startdate_meridian']));
					// Final frobbing of data before we kick off
					$this -> data['Room']['canada'] = (int)((float)$this -> data['Room']['canada'] * 10000.0);
					//$rv = $this->Request->saveRequest(REQTYPE_ROOM_CREATE, $user['User']['id'], $account['Account']['acctgrpid'],
					//      null, $this->data['Room'], REQSTATUS_APPROVED);
					if (!$this -> Room -> createRoom($user, $this -> data['Room'])) {
						$this -> Session -> setFlash('Your request has been submitted');
						$this -> redirect('/accounts/view/' . $account['Account']['acctgrpid']);
					} else {
						$this -> Session -> setFlash('Room creation failed');
					}
				}
			} elseif (empty($this -> data)) {
				$this -> data = Array('Room' => Array('bridgeid' => $bridge, 'productid' => $account['Account']['default_product_id'], 'contact_id' => $contact['Contact']['id'], 'acctgrpid' => $account['Account']['acctgrpid'], 'email' => $contact['Contact']['email'], 'contact' => $full_name, 'company' => $account['Account']['bcompany'], 'rateid' => $account['Account']['default_rateid'], 'canada' => (float)$account['Account']['default_canada'] / 10000.0, 'dialinNoid' => $account['Account']['dialinNoid'], 'emailrpt' => 1, 'lang' => 0, 'maximumconnections' => 100));
				$this -> set_bridge_defaults($bridge, $accountGroupBillingMethodID['AccountBillingMethod']['billing_method_id']);
				$this -> data['Room']['autogenerate_cec'] = empty($this -> data['Room']['cec']) ? 1 : 0;
				$this -> data['Room']['autogenerate_pec'] = empty($this -> data['Room']['pec']) ? 1 : 0;
			}
		} else {
			$this -> Session -> setFlash('Account not found');
			$this -> redirect('/');
		}
	}

	function edit($accountid = null) {
		$user = $this -> Session -> read('User');

		if ($room = $this -> Room -> get($accountid, $user)) {
			$this -> set('room', $room);
             //01-13-2014 Added SmartCloud Room
			//Web or Audio or SmartCloud
			$web_room = $room['Room']['bridgeid'] == WEBINTERPOINT_BRIDGEID || $room['Room']['bridgeid'] == SMARTCLOUD_BRIDGEID || $room['Room']['bridgeid'] == WEBEX_BRIDGEID || $room['Room']['bridgeid'] == LIVE_MEETING_BRIDGEID || $room['Room']['bridgeid'] == WEBEXPRESS_BRIDGEID;
			$this -> Salesperson -> recursive = 0;
			$salesperson = $this -> Salesperson -> read(null, $room['Account']['salespid']);

			$reseller = $this -> Reseller -> read(null, $salesperson['Salesperson']['resellerid']);
			$this -> set('reseller', $reseller);

			// can the user set maxconnections
			$can_set_maxconnections = true;
			if ($user['User']['ic_employee'] == 0 || $user['User']['level_type'] == 'salesperson')
				$can_set_maxconnections = false;
			$this -> set('can_set_maxconnections', $can_set_maxconnections);

			$billingMethods = $this -> BillingMethod -> generateList(null, null, null, '{n}.BillingMethod.id', '{n}.BillingMethod.description');
			$this -> set('billingMethods', $billingMethods);

			if (!$web_room) {
				$BridgeSettings = $this -> BridgeSettings -> getBridgeSettings($room['Room']['bridgeid']);

				$this -> set('activeBridgeSettings', pluck($BridgeSettings, 'symbol'));
				$this -> set('bridgeSettingsDetails', $BridgeSettings);

				$this -> setup_room_form($room['Room']['bridgeid'], $BridgeSettings);

				$bridgeSettingMap = array();
				foreach ($BridgeSettings as $key => $val) {

					if (array_key_exists($val['symbol'], $bridgeSettingMap)) {
						$result = array();
						foreach ($bridgeSettingMap[$val['symbol']] as $key => &$value) {
							$result[$key] = $value;
						}
						$result[$val['bridgevalue']] = $val['value'];
						$bridgeSettingMap[$val['symbol']] = $result;
					} else {
						$bridgeSettingMap[$val['symbol']] = array($val['bridgevalue'] => $val['value']);
					}
				}

				$room['Room'] = $this -> convert_room($room['Room'], $bridgeSettingMap);
				$BridgeSettingDescriptions = $this -> BridgeSettings -> getBridgeSettingDescriptions($room['Room']['bridgeid']);
				$this -> set('settingsDescription', $BridgeSettingDescriptions);
			} else {
				$this -> set('languages', $this -> Room -> languages);
			}

			if (!empty($this -> data) && isset($_POST['manual'])) {
				$this -> Room -> set($this -> data);

				if ($this -> Room -> validates($this -> data)) {
					$effective_date = null;
					if ($room['Room']['bridgeid'] == OCI_BRIDGEID && !$this -> Room -> isSafeUpdate($room, $this -> data['Room']))
						$effective_date = date('Y-m-d') . ' 23:00:00';

					if ($user['User']['level_type'] == 'salesperson' && !in_array($room['Account']['salespid'], $user['Salespersons']))
						$status = REQSTATUS_PENDING;
					else
						$status = REQSTATUS_APPROVED;

					if (!$this -> Room -> updateRoom($user, $this -> data['Room'])) {
						//Update Room Billing Method.
						if (!empty($this -> data['Room']['billing_method_id'])) {
							$billing_method_id = $this -> data['Room']['billing_method_id'];
							$billing_frequency_id = (!empty($this -> data['Room']['billing_frequency_id']) ? $this -> data['Room']['billing_frequency_id'] : null);
							$flat_rate_charge = (!empty($this -> data['Room']['flat_rate_charge']) ? $this -> data['Room']['flat_rate_charge'] : null);
							$this -> Room -> setBillingMethod($room, $billing_method_id, $billing_frequency_id, $flat_rate_charge);
						}

						$this -> Session -> setFlash('Your request has been submitted');
						$this -> redirect('/rooms/view/' . $room['Room']['accountid']);
					} else {
						$this -> Session -> setFlash('Request submission failed');
					}
				}
			} elseif (empty($this -> data)) {
				$this -> data = $room;
				// NB - backfill room email from contact
				if (!empty($room['Contact'][0]['email']))
					$this -> data['Room']['email'] = $room['Contact'][0]['email'];
				
				/*
				 * 2013-08-29
				 * noliphant@onsm.com
				 * Nathan Oliphant
				 * Retrieve the flat-rate information for this room.
				 * Changes here and in app/views/rooms/edit.thtml for correct billing load on edit.
				 */
				$billing_method_id = $this -> RoomBillingMethod -> find(Array('accountid' => $accountid));
				if (empty($billing_method_id)) {
					$this -> set('billing_method', "Price Per Minute");
					$billing_info['billing_method'] = "Price Per Minute";
					$billing_info['billing_method_id'] = '';
					$billing_info['billing_method_code'] = '';
					$billing_info['billing_frequency_id'] = '';
					$billing_info['flat_rate_charge'] = '';
					$billing_info['accountid'] = $accountid;
					$billing_info['set'] = 'false';
					$this -> set('billing_info', $billing_info);
				} else {
					$billing_method = $this -> BillingMethod -> find(Array('id' => $billing_method_id['RoomBillingMethod']['billing_method_id']), array('description', 'code'));
					$this -> set('billing_method', $billing_method);
					$billing_info_raw = $this -> RoomBillingMethod -> find(Array('id' => $billing_method_id), array('accountid', 'billing_method_id', 'flat_rate_charge', 'billing_frequency_id'));
					$billing_info['billing_method'] = $billing_method['BillingMethod']['description'];
					$billing_info['billing_method_id'] = $billing_info_raw['RoomBillingMethod']['billing_method_id'];
					$billing_info['billing_method_code'] = $billing_method['BillingMethod']['code'];
					$billing_info['billing_frequency_id'] = $billing_info_raw['RoomBillingMethod']['billing_frequency_id'];
					$billing_info['flat_rate_charge'] = $billing_info_raw['RoomBillingMethod']['flat_rate_charge'];
					$billing_info['accountid'] = $accountid;
					$billing_info['set'] = 'true';
					$this -> set('billing_info', $billing_info);
				}
				
				if ($web_room)
					$this -> render('edit_web');
			}

			$bridges = $this -> Bridge -> generateList(Array('active' => 1), 'id ASC', null, '{n}.Bridge.id', '{n}.Bridge.name');
			// NB: this is placed after $this->data get set
			list($dialin_numbers, $dialin_map, $default_dialin) = $this -> build_dialin_list($salesperson['Salesperson']['resellerid'], $room, $bridges[$room['Room']['bridgeid']]);

			if (isset($dialin_numbers[$room['Room']['dialinNoid']]))
				$default_dialin = $room['Room']['dialinNoid'];

			$this -> set('dialin_numbers', $dialin_numbers);
			$this -> set('dialin_map', $dialin_map);
			$this -> set('default_dialin', $default_dialin);

			if ($web_room)
				$this -> render('edit_web');

		} else {
			$this -> Session -> setFlash('Account not found');
			$this -> redirect('/');
		}
	}

	function index($acctgrpid = null) {
		$user = $this -> Session -> read('User');

		if ($is_ajax = $this -> RequestHandler -> isAjax() || !empty($_GET['export'])) {
			Configure::write('debug', 0);
			$this -> layout = 'ajax';
		}
		$this -> set('is_ajax', $is_ajax);

		$criteria = Array();

		$account = null;
		$this -> Account -> recursive = 0;

		if ($acctgrpid && ($account = $this -> Account -> read(null, $acctgrpid))) {
			$criteria['RoomView.acctgrpid'] = $acctgrpid;

			if (!empty($_GET['embed']) && !empty($account['DefaultBridge']['bridge_id']))
				$criteria['RoomView.bridgeid'] = Array($account['DefaultBridge']['bridge_id'], WEBEX_BRIDGEID, LIVE_MEETING_BRIDGEID, WEBINTERPOINT_BRIDGEID, SMARTCLOUD_BRIDGEID);
		}
		$this -> set('account', $account);

		$bridges = $this -> Bridge -> generateList(Array('active' => 1), 'id ASC', null, '{n}.Bridge.id', '{n}.Bridge.description');
		$bridges[""] = "";
		ksort($bridges);
		$this -> set('bridges', $bridges);
		if (!empty($_GET['embed']) || empty($_GET['all']))
			$criteria['RoomView.roomstat'] = Array('Active', 'Suspended');

		if (!is_null($user['Resellers']))
			$criteria['RoomView.resellerid'] = $user['Resellers'];

		if (!empty($_GET['query'])) {
			$query = $_GET['query'];
			$fquery = implode('%', preg_split('/\s+/', $query, -1, PREG_SPLIT_NO_EMPTY));

			$criteria['OR'] = Array('RoomView.accountid' => "LIKE {$fquery}%", 'RoomView.company' => "LIKE {$fquery}%", 'RoomView.email' => "LIKE {$fquery}%", 'RoomView.cec' => "LIKE {$fquery}%", 'RoomView.pec' => "LIKE {$fquery}%", 'RoomView.contact' => "LIKE {$fquery}%");

			if ($account)
				$criteria['OR']['RoomView.acctgrpid'] = "LIKE %{$fquery}%";
		} else {
			$query = '';
		}

		if (!empty($_GET['bridge'])) {
			$criteria['RoomView.bridgeid'] = $_GET['bridge'];
		}

		$this -> set('query', $query);

		if ($user['User']['level_type'] == SALESPERSON_LEVEL) {
			//Only export the accounts that a salesperson owns. We don't want them seeing the
			//seeing the contact info of the accounts that they don't own.
			$criteria['RoomView.salespid'] = $user['Salespersons'];
		}

		$this -> set('full_report', $user['User']['level_type'] == 'reseller' || in_array($account['AccountView']['salespid'], $user['Salespersons']));

		list($order, $limit, $page) = $this -> Pagination -> init($criteria, null, aa('sortBy', 'accountid'));

		if (!empty($_GET['export']))
			list($limit, $page) = Array(null, null);

		$this -> RoomView -> recursive = 0;
		$data = $this -> RoomView -> findAll($criteria, NULL, $order, $limit, $page);

		if ($acctgrpid && !empty($_GET['export'])) {
			$filename = sprintf('%s - Rooms %s', $acctgrpid, date('Y-m-d'));
			$keys = Array('accountid', 'contact', 'billingcode', 'cec', 'pec');
			$headers = Array('accountid' => 'Confirmation Number', 'contact' => 'Room Topic', 'billingcode' => 'Billing Code', 'cec' => 'Chair Pass Code', 'pec' => 'Participant Pass Code');

			export_csv($filename, $keys, $headers, pluck($data, 'RoomView'));

			die ;
		} else {
			$this -> set('data', $data);
		}
	}

	function pull($acctgrpid = null) {
		$user = $this -> Session -> read('User');

		if ($account = $this -> Account -> get($acctgrpid, $user)) {
			$this -> set('account', $account);

			if (!empty($this -> data)) {
				if ($this -> Room -> isValidAccountid($this -> data['Room']['accountid'])) {

					if (!$this -> Room -> roomExists($this -> data['Room']['accountid'])) {

						if ($room = $this -> Room -> pull($acctgrpid, $this -> data['Room']['accountid'])) {
							$this -> Session -> write('room_settings', $room);
							$this -> redirect('/rooms/create/' . $acctgrpid);
						} else {
							$this -> Session -> setFlash('No room found on bridge with confirmation number: ' . $this -> data['Room']['accountid']);
						}
					} else {
						$this -> Session -> setFlash('This confirmation number already exists in the database: ' . $this -> data['Room']['accountid']);
					}

				} else {
					$this -> Room -> invalidate('accountid');
				}
			}

		} else {
			$this -> Session -> setFlash('Account not found');
			$this -> redirect('/');
		}
	}

	/*
	 * Not sure if this is being used. If this is still here and no one is
	 * complaining after 6 months - DC 2012/09/07
	 function search()
	 {
	 Configure::write('debug', 0);

	 if(!empty($this->data)) {
	 $user = $this->Session->read('User');

	 $account = $this->AccountView->read(null, mssql_escape($this->data['Account']['src']));

	 // NB: the keys need to differ so they dont overwrite, not sure how to properly do this
	 $criteria = Array('acctgrpid' => '<> ' . mssql_escape($this->data['Account']['src']),
	 'acctstatus'=> 'Active',
	 'OR'        => Array( 'AccountView.acctgrpid' => 'LIKE %' . mssql_escape($this->data['Room']['acctgrpid']) . '%',
	 'AccountView.company'   => 'LIKE %' . mssql_escape($this->data['Room']['acctgrpid']) . '%') );

	 if(!is_null($user['Resellers']))
	 $criteria['AccountView.resellerid'] = $user['Resellers'];

	 $max = 25;
	 $this->AccountView->recursive = 0;
	 $accounts = $this->AccountView->findAll($criteria, Array('acctgrpid', 'company', 'contact'), 'acctgrpid ASC', $max);
	 $this->set('accounts', $accounts);
	 $this->set('too_many', count($accounts) == $max ? true : false);

	 $this->layout = 'ajax';
	 }
	 }*/

	function select($acctgrpid = null, $mode = null) {
		if ($acctgrpid && $mode) {
			$user = $this -> Session -> read('User');

			$this -> Account -> recursive = 0;
			if ($account = $this -> Account -> get($acctgrpid, $user)) {
				$this -> set('account', $account);
				$this -> set('mode', $mode);

				$is_post = $this -> RequestHandler -> isPost();

				if ($is_ajax = $this -> RequestHandler -> isAjax()) {
					Configure::write('debug', 0);
					$this -> layout = 'ajax';
				}

				$this -> set('is_ajax', $is_ajax);
				$key = sprintf('Selections.%s.%s', $mode, $acctgrpid);
				if ($this -> Session -> check($key))
					$selections = $this -> Session -> read($key);
				else
					$selections = Array();

				if (!empty($_GET['room'])) {
					if (array_search($_GET['room'], $selections) === false) {
						$selections[] = $_GET['room'];
						$this -> Session -> write($key, $selections);
					}
				}

				if ($is_post && !$is_ajax && $selections) {
					$modes = Array('email' => '/email/index/' . $acctgrpid, 'statuses' => '/rooms/statuses/' . $acctgrpid, 'move' => '/rooms/move/' . $acctgrpid, 'rates' => '/rooms/rates/' . $acctgrpid, 'bulkupdates' => '/rooms/bulkupdates/' . $acctgrpid);

					$this -> redirect($modes[$mode]);
				} elseif ($is_post && $is_ajax) {
					$nonQueryCriteria = Array('RoomView.acctgrpid' => $acctgrpid, 'RoomView.roomstat' => 'Active', 'RoomView.bridgeid = (select bridge_id from default_bridge where default_bridge.acctgrpid = [Roomview].acctgrpid)', '(RoomView.isopassist = 0 OR RoomView.isopassist is null)', '(RoomView.isevent = 0 OR RoomView.isevent is null)');
					if ($_POST['accountid'] == 'all') {
						foreach ($this->RoomView->findAll($nonQueryCriteria) as $i)
							if (array_search($i['RoomView']['accountid'], $selections) === False)
								$selections[] = $i['RoomView']['accountid'];
					} elseif ($_POST['accountid'] == 'none') {
						$selections = Array();
					} elseif (isset($_POST['value']) && !empty($_POST['value'])) {
						if (array_search($_POST['accountid'], $selections) === False)
							$selections[] = $_POST['accountid'];
					} elseif (isset($_POST['value'])) {
						$idx = array_search($_POST['accountid'], $selections);
						if ($idx !== false)
							unset($selections[$idx]);
					}

					// NB: if the 0'th key is not set, cake will generate incorrect sql, so reindex the array to be sure
					$selections = array_values($selections);

					$this -> set('selections', $selections);
					$this -> set('selected', count($selections));

					$this -> Session -> write($key, $selections);
					$this -> render('select_ajax');

				} else {
					if ($is_post && !$selections)
						$this -> Session -> setFlash('Please select one or more rooms');

					// FIXME see previous, also consoladate this
					$criteria = Array('RoomView.acctgrpid' => $acctgrpid, 'RoomView.roomstat' => 'Active', 'RoomView.bridgeid = (select bridge_id from default_bridge where default_bridge.acctgrpid = [Roomview].acctgrpid)', '(RoomView.isopassist = 0 OR RoomView.isopassist is null)', '(RoomView.isevent = 0 OR RoomView.isevent is null)');

					if (!empty($_GET['query'])) {
						$query = $_GET['query'];
						$fquery = implode('%', preg_split('/\s+/', $query, -1, PREG_SPLIT_NO_EMPTY));

						$criteria['OR'] = Array('RoomView.accountid' => "LIKE {$fquery}%", 'RoomView.company' => "LIKE {$fquery}%", 'RoomView.email' => "LIKE {$fquery}%", 'RoomView.cec' => "LIKE {$fquery}%", 'RoomView.pec' => "LIKE {$fquery}%", 'RoomView.contact' => "LIKE {$fquery}%");
					} else {
						$query = '';
					}

					$this -> set('query', $query);

					list($order, $limit, $page) = $this -> Pagination -> init($criteria, null, aa('sortBy', 'accountid'));
					$this -> set('data', $this -> RoomView -> findAll($criteria, NULL, $order, $limit, $page));
				}
				$this -> set('selections', $selections);
				$this -> set('selected', count($selections));

			} else {
				$this -> Session -> flash('Account not found');
				$this -> redirect('/accounts');
			}
		} else {
			$this -> Session -> flash('Invalid account or mode passed');
			$this -> redirect('/accounts');
		}
	}

	function status($accountid = null) {
		$user = $this -> Session -> read('User');

		if ($room = $this -> Room -> get($accountid, $user)) {
			$this -> set('room', $room);
			$this -> set('room_statuses', $this -> Status -> generateList(null, 'description ASC', null, '{n}.Status.acctstat', '{n}.Status.description'));

			if ($this -> data) {
				$valid_effective_date = preg_match(VALID_DATE, $this -> data['Room']['effective_date']);

				if ($this -> data['Room']['status'] != $room['Room']['roomstat'] && !empty($this -> data['Room']['reason']) && $valid_effective_date) {

					$effective_date = $this -> data['Room']['effective_date'] . ' 00:00:00';

					$rv = $this -> Room -> updateStatus($user, $room, $this -> data['Room']['status'], $this -> data['Room']['reason'], $effective_date);

					if (!$rv) {
						$this -> Session -> setFlash('Your request has been submitted');
						$this -> redirect('/rooms/index/' . $room['Room']['acctgrpid']);
					} else {
						$this -> Session -> setFlash('Could not update room status');
					}

				} else {
					if (empty($this -> data['Room']['reason']))
						$this -> Room -> invalidate('reason');

					if ($this -> data['Room']['status'] == $room['Room']['roomstat'])
						$this -> Room -> invalidate('status');

					if (!$valid_effective_date)
						$this -> Room -> invalidate('effective_date');
				}
			} else {
				$this -> data = $room;
			}

		} else {
			$this -> Session -> setFlash('Room not found');
			$this -> redirect('/');
		}
	}

	function statuses($acctgrpid = null) {
		$user = $this -> Session -> read('User');

		if ($account = $this -> Account -> get($acctgrpid, $user)) {
			$key = 'Selections.statuses.' . $acctgrpid;

			if ($selections = $this -> Session -> read($key)) {

				$account['Room'] = Array();
				$this -> Room -> recursive = 0;
				foreach ($this->Room->findAll(Array('accountid' => $selections)) as $i)
					$account['Room'][] = $i['Room'];

				$this -> set('account', $account);
				$this -> set('room_statuses', $this -> Status -> generateList(null, 'description ASC', null, '{n}.Status.acctstat', '{n}.Status.description'));
				if (!empty($this -> data)) {
					$valid_effective_date = preg_match(VALID_DATE, $this -> data['Room']['effective_date']);

					if ($valid_effective_date && isset($this -> data['Room']['status']) && !empty($this -> data['Room']['reason'])) {

						$effective_date = $this -> data['Room']['effective_date'] . ' 00:00:00';

						set_time_limit(0);

						// Ugly hack to get around fetching each room
						foreach ($this->data['Room']['accountid'] as $a) {
							if (!empty($a)) {
								$this -> Room -> updateStatus($user, Array('Room' => Array('accountid' => $a)), $this -> data['Room']['status'], $this -> data['Room']['reason'], $effective_date);
							}
						}

						// invalidate room selections
						$this -> Session -> delete($key);
						$this -> Session -> setFlash('Your requests have been submitted');
						$this -> redirect('/accounts/view/' . $acctgrpid);
					} else {
						if (!$valid_effective_date)
							$this -> Room -> invalidate('effective_date');

						if (empty($this -> data['Room']['reason']))
							$this -> Room -> invalidate('reason');
					}
				} else {
					$this -> data = Array('Room' => Array('accountid' => $selections));
				}
			} else {
				$this -> Session -> setFlash('Please select one or more rooms');
				$this -> redirect('/rooms/select/' . $acctgrpid . '/statuses');
			}

		} else {
			$this -> Session -> setFlash('Account not found');
			$this -> redirect('/');
		}
	}

	function sync($accountid = null) {
		$this -> layout = 'ajax';
		Configure::write('debug', 0);

		$user = $this -> Session -> read('User');
		$this -> set('user', $user);

		$rv = false;

		if ($room = $this -> Room -> get($accountid, $user)) {
			if ($this -> data) {
				$this -> Request -> saveRequest(REQTYPE_ROOM_SYNC, $user['User']['id'], $room['Room']['acctgrpid'], $room['Room']['accountid'], null, REQSTATUS_APPROVED);
				$rv = true;
			}
		}

		$this -> set('rv', $rv);
	}

	function migrate($accountid = null) {
		$this -> layout = 'ajax';
		Configure::write('debug', 0);

		$user = $this -> Session -> read('User');
		$this -> set('user', $user);

		$rv = false;

		if ($room = $this -> Room -> get($accountid, $user)) {
			if ($this -> data) {
				$this -> Request -> saveRequest(REQTYPE_CODE_MIGRATION, $user['User']['id'], $room['Room']['acctgrpid'], $room['Room']['accountid'], null, REQSTATUS_APPROVED);
				$rv = true;
			}
		}

		$this -> set('rv', $rv);
	}

	function view($accountid = null) {
		$user = $this -> Session -> read('User');
		$this -> set('user', $user);

		if ($room = $this -> Room -> get($accountid, $user)) {
			$this -> set('room', $room);

			$this -> addHistory(sprintf('Room %s - %s', $room['Room']['accountid'], $room['Room']['contact']));

			$this -> Contact -> unbindModel(Array('hasAndBelongsToMany' => Array('Room'), 'belongsTo' => Array('Account')));
			$account_contacts = $this -> Contact -> findAll(Array('Contact.acctgrpid' => $room['Room']['acctgrpid']), null, 'last_name ASC, first_name ASC');

			$contacts = Array();
			foreach ($account_contacts as $c)
				$contacts[$c['Contact']['id']] = $this -> Contact -> fullName($c);
			$this -> set('contacts', $contacts);

			$this -> set('schedule_types', isset($this -> Room -> schedule_types[$room['Room']['bridgeid']]) ? $this -> Room -> schedule_types[$room['Room']['bridgeid']] : $this -> Room -> schedule_types[OCI_BRIDGEID]);

			// Include the null value
			$this -> Room -> security_types[SPECTEL_BRIDGEID][0] = 'No PIN';
			$this -> set('security_types', isset($this -> Room -> security_types[$room['Room']['bridgeid']]) ? $this -> Room -> security_types[$room['Room']['bridgeid']] : $this -> Room -> security_types[OCI_BRIDGEID]);

			$this -> set('start_modes', isset($this -> Room -> start_modes[$room['Room']['bridgeid']]) ? $this -> Room -> start_modes[$room['Room']['bridgeid']] : $this -> Room -> start_modes[OCI_BRIDGEID]);

			$this -> set('announcements', isset($this -> Room -> announcements[$room['Room']['bridgeid']]) ? $this -> Room -> announcements[$room['Room']['bridgeid']] : $this -> Room -> announcements[OCI_BRIDGEID]);

			$this -> set('recording_signals', isset($this -> Room -> recording_signals[$room['Room']['bridgeid']]) ? $this -> Room -> recording_signals[$room['Room']['bridgeid']] : $this -> Room -> recording_signals[OCI_BRIDGEID]);

			$this -> Room -> spectel_namerecording_options[0] = 'Off';
			$this -> set('spectel_namerecording_options', $this -> Room -> spectel_namerecording_options);

			$this -> set('room_statuses', $this -> Status -> generateList(null, 'description ASC', null, '{n}.Status.acctstat', '{n}.Status.description'));

			$this -> set('ending_signals', $this -> Room -> ending_signals);
			$this -> set('dtmf_signals', $this -> Room -> dtmf_signals);
			$this -> set('digit_entries', $this -> Room -> digit_entries);
			$this -> set('languages', $this -> Room -> languages);

			$this -> set('notes', $this -> Note -> get('Room', $accountid));

			$this -> set('bridge_settings', Array());

			$this -> set('pending_sync', $this -> Request -> find(Array('accountid' => $accountid, 'type' => REQTYPE_ROOM_SYNC, 'status' => a(REQSTATUS_APPROVED))));

			$this -> set('migrations', $this -> Room -> findMigrations($room));
			$this -> set('existing_migration_req', $this -> Request -> find(Array('accountid' => $accountid, 'type' => REQTYPE_CODE_MIGRATION, 'status' => a(REQSTATUS_APPROVED, REQSTATUS_FAILED)), null, 'Request.created DESC'));

			$this -> set('webinterpoint_account', $this -> WebinterpointAccount -> findAccount($room));
			$this -> set('existing_webinterpoint_req', $this -> Request -> find(Array('accountid' => $accountid, 'type' => REQTYPE_WEBINTERPOINT_CREATION, 'status' => a(REQSTATUS_APPROVED, REQSTATUS_FAILED)), null, 'Request.created DESC'));
         //01-13-2013 Added SmartCloud Room			
			$this -> set('smartcloud_account', $this -> SmartCloudAccount -> findAccount($room));
			$this -> set('existing_smartcloud_req', $this -> Request -> find(Array('accountid' => $accountid, 'type' => REQTYPE_SMARTCLOUD_CREATION, 'status' => a(REQSTATUS_APPROVED, REQSTATUS_FAILED)), null, 'Request.created DESC'));

			$this -> set('requests', $this -> RequestView -> findAll(Array('accountid' => $accountid), null, 'created DESC', 5));

			$billing_method_id = $this -> RoomBillingMethod -> find(Array('accountid' => $accountid));
			if (empty($billing_method_id)) {
				$this -> set('billing_method', "Price Per Minute");
			} else {
				$billing_method = $this -> BillingMethod -> find(Array('id' => $billing_method_id['RoomBillingMethod']['billing_method_id']), array('description'));
				$this -> set('billing_method', $billing_method['BillingMethod']['description']);
			}

			$criteria = Array();
			if (!is_null($user['Resellers'])) {
				$criteria['ConferenceReport.resellerid'] = $user['Resellers'];
			}
			$criteria['ConferenceReport.accountid'] = $accountid;
			//DC Need one for salesperson?

			$this -> set('serviceTypes', $this -> ServiceType -> generateList(null, null, null, '{n}.ServiceType.code', '{n}.ServiceType.description'));
			$this -> set('usage', $this -> ConferenceReport -> findAll($criteria, null, 'conference_start DESC', 5));

			$this -> set('welcome_email_log', $this -> WelcomeEmailLog -> find(Array('accountid' => $accountid), 'sent', 'sent DESC'));
			$web_bridges = array(WEBINTERPOINT_BRIDGEID, WEBEX_BRIDGEID, SMARTCLOUD_BRIDGEID, LIVE_MEETING_BRIDGEID);
			if (in_array($room['Room']['bridgeid'], $web_bridges)) {
				if ($room['Room']['bridgeid'] == WEBEX_BRIDGEID)
					$this -> set('bridge_key', 'WebexRoom');
				else if ($room['Room']['bridgeid'] == LIVE_MEETING_BRIDGEID)
					$this -> set('bridge_key', 'LiveMeetingRoom');
				else if ($room['Room']['bridgeid'] == SMARTCLOUD_BRIDGEID)
					$this -> set('bridge_key', 'SmartCloudRoom');	
				$this -> set('audio_room', $room['WebinterpointRoom']['audio_accountid']);
				$this -> render('view_web');
			} else if ($room['Bridge']['type'] == AUDIO_BRIDGE) {
				$associated_webinterpoint = $this -> WebinterpointRoom -> find(Array('audio_accountid' => $accountid, 'web_room.roomstat' => 0), 'web_accountid');
				$this -> set('webi_room', $associated_webinterpoint['WebinterpointRoom']['web_accountid']);

				$associated_webex = $this -> WebexRoom -> find(Array('audio_accountid' => $accountid, 'web_room.roomstat' => 0), 'web_accountid');
				$this -> set('webex_room', $associated_webex['WebexRoom']['web_accountid']);

				$associated_live_meeting = $this -> LiveMeetingRoom -> find(Array('audio_accountid' => $accountid, 'web_room.roomstat' => 0), 'web_accountid');
				$this -> set('live_meeting_room', $associated_live_meeting['LiveMeetingRoom']['web_accountid']);
			}

		} else {
			$this -> Session -> setFlash('Room not found');
			$this -> redirect('/');
		}
	}

	function bulkupdates($acctgrpid = null) {
		$user = $this -> Session -> read('User');

		// can the user set maxconnections
		$can_set_maxconnections = true;
		if ($user['User']['ic_employee'] == 0 || $user['User']['level_type'] == 'salesperson')
			$can_set_maxconnections = false;
		$this -> set('can_set_maxconnections', $can_set_maxconnections);

		if ($account = $this -> Account -> get($acctgrpid, $user)) {

			$this -> Salesperson -> recursive = 0;
			$salesperson = $this -> Salesperson -> read(null, $account['Salesperson']['salespid']);

			$key = 'Selections.bulkupdates.' . $acctgrpid;

			$BridgeSettings = $this -> BridgeSettings -> getBridgeSettings($account['DefaultBridge']['bridge_id']);
			$this -> set('activeBridgeSettings', pluck($BridgeSettings, 'symbol'));
			$this -> set('bridgeSettingsDetails', $BridgeSettings);
			$this -> setup_room_form($account['DefaultBridge']['bridge_id'], $BridgeSettings);
			$BridgeSettingDescriptions = $this -> BridgeSettings -> getBridgeSettingDescriptions($account['DefaultBridge']['bridge_id']);
			$this -> set('settingsDescription', $BridgeSettingDescriptions);
			$bridgeName = $this -> Bridge -> find("id = " . $account['DefaultBridge']['bridge_id'], array('name'));

			if ($selections = $this -> Session -> read($key)) {
				$account['Room'] = Array();
				$this -> Room -> recursive = 0;
				foreach ($this->Room->findAll(Array('accountid' => $selections)) as $i)
					$account['Room'][] = $i['Room'];
				$this -> set('account', $account);
				$staging = array();
				if (!empty($this -> data)) {
					foreach ($this->data['Room'] as $paramKey => $paramValue) {
						if ($paramKey <> 'accountid' && $paramValue !== '')
							$staging[$paramKey] = $paramValue;
					}

					set_time_limit(0);
					$error = 0;
					foreach ($this->data['Room']['accountid'] as $id) {
						unset($staging['contact']);
						unset($staging['accountid']);
						if (!empty($id)) {
							$staging['accountid'] = $id;
							foreach ($account['Room'] as $roomSrch) {
								if ($roomSrch['accountid'] == $id) {
									$staging['contact'] = $roomSrch['contact'];
									break;
								}
							}

							if ($this -> Room -> updateRoom($user, $staging) <> 0) {
								$this -> Session -> setFlash('Errors occurred during your request');
								$error = 1;
							}
						}
					}
					if (!$error) {
						$this -> Session -> setFlash('Request Processed');
					}
					$this -> redirect('/accounts/view/' . $acctgrpid);
				} else {
					$this -> data = Array('Room' => Array('accountid' => $selections));

				}
			} else {
				$this -> Session -> setFlash('Please select one or more rooms');
				$this -> redirect('/rooms/select/' . $acctgrpid . '/statuses');
			}

			$bridges = $this -> Bridge -> generateList(Array('active' => 1), 'id ASC', null, '{n}.Bridge.id', '{n}.Bridge.name');
			// NB: this is placed after $this->data get set
			list($dialin_numbers, $dialin_map, $default_dialin) = $this -> build_dialin_list($salesperson['Salesperson']['resellerid'], null, $bridgeName['Bridge']['name']);

			$this -> set('dialin_numbers', $dialin_numbers);
			$this -> set('dialin_map', $dialin_map);
			$this -> set('default_dialin', $default_dialin);

		} else {
			$this -> Session -> setFlash('Account not found');
			$this -> redirect('/');
		}
	}

}
