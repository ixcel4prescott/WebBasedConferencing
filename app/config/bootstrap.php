<?php
/* SVN FILE: $Id: bootstrap.php 4409 2007-02-02 13:20:59Z phpnut $ */
/**
 * Short description for file.
 *
 * Long description for file
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright 2005-2007, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright 2005-2007, Cake Software Foundation, Inc.
 * @link				http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
 * @package			cake
 * @subpackage		cake.app.config
 * @since			CakePHP(tm) v 0.10.8.2117
 * @version			$Revision: 4409 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2007-02-02 07:20:59 -0600 (Fri, 02 Feb 2007) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 *
 * This file is loaded automatically by the app/webroot/index.php file after the core bootstrap.php is loaded
 * This is an application wide file to load any function that is not used within a class define.
 * You can also use this to include or require any files in your application.
 *
 */
/**
 * The settings below can be used to set additional paths to models, views and controllers.
 * This is related to Ticket #470 (https://trac.cakephp.org/ticket/470)
 *
 * $modelPaths = array('full path to models', 'second full path to models', 'etc...');
 * $viewPaths = array('this path to views', 'second full path to views', 'etc...');
 * $controllerPaths = array('this path to controllers', 'second full path to controllers', 'etc...');
 *
 */
//EOF

define('BIZ_SIDE', true);

//------------------------------------------------------------------------------
//  Include stuff from common
//------------------------------------------------------------------------------

define('COMMON_DIR', ROOT . DS . 'common');

if(!function_exists('common')){
  function common($path) { 
    require_once(COMMON_DIR . DS . $path . '.php'); 
  }
}

function common_vendor($path) {
  common('vendors'.DS.$path);
}

common('config'.DS.'bootstrap');

//------------------------------------------------------------------------------
//  Group/Authorization Stuff
//------------------------------------------------------------------------------

// Groups/Permissions map
define('GROUP_NOBODY',        0);
define('GROUP_ACCOUNTS',      0);
define('GROUP_ACCOUNTGROUPS', 0);
define('GROUP_RESELLERS',     (1<<0));
define('GROUP_SALESPEOPLE',   (1<<1));
define('GROUP_ADMINS',        (1<<2));
//request admin is a reseller group user that can view requests
define('GROUP_RESELLER_ADMINS', GROUP_RESELLERS|(1<<9));

define('GROUP_RESELLER_ADMIN_ONLY', (1<<9));
define('GROUP_IC',            (1<<10)); // "virtual" group for IC employees

// This is silly, cant use a constant expression as a member definition in php so we dupe it with a define
define('GROUP_RESELLERS_AND_SALESPEOPLE', GROUP_RESELLERS|GROUP_SALESPEOPLE);
define('GROUP_RESELLERS_AND_ADMINS',      GROUP_RESELLERS|GROUP_ADMINS);
define('GROUP_ALL',                       GROUP_RESELLERS|GROUP_SALESPEOPLE|GROUP_ADMINS);

define('GROUP_IC_RESELLERS',                 GROUP_IC|GROUP_RESELLERS);
define('GROUP_IC_SALESPEOPLE',               GROUP_IC|GROUP_SALESPEOPLE);
define('GROUP_IC_RESELLERS_AND_SALESPEOPLE', GROUP_IC|GROUP_RESELLERS|GROUP_SALESPEOPLE);
define('GROUP_IC_RESELLERS_AND_ADMINS',      GROUP_IC|GROUP_RESELLERS|GROUP_ADMINS);
define('GROUP_IC_ALL',                       GROUP_IC|GROUP_RESELLERS|GROUP_SALESPEOPLE|GROUP_ADMINS);

//------------------------------------------------------------------------------
//  Backend Stuff
//------------------------------------------------------------------------------

if(!defined('MYCA_ROOT'))
  define('MYCA_ROOT',           dirname(__FILE__) . DS . '..' . DS . '..');

define('SCRIPT_ROOT',           MYCA_ROOT . DS . '..' . DS . 'scripts');

// Window in which we will run
define('BACKEND_RUN_WINDOW',     60 * 3); // 3m = 60s * 3
define('BACKEND_LOCK',           MYCA_ROOT . DS . 'vendors' . DS . 'myca_backend.lock');
define('BACKEND_HOST',           '<backend>');
define('BACKEND_ADDR',           '0.0.0.0');
define('BACKEND_MAX_TRIES',      5);
define('BACKEND_THROTTLE_SLEEP', 5);

//------------------------------------------------------------------------------
//  Constants used in credit card hook
//------------------------------------------------------------------------------
if (DEBUG > 0) {
  define('BILLING_CREDIT_CARD_WEBHOOK_URL', 'https://localhost:5000/callbacks/update_credit_card_webhook/');
} else {
  define('BILLING_CREDIT_CARD_WEBHOOK_URL', 'https://billing.myconferenceadmin.com/callbacks/update_credit_card_webhook/');
}
define('AUTH_TOKEN', '9bea49ee173946398f0f66944951d9c7');

//------------------------------------------------------------------------------
//  Various constants used in code
//------------------------------------------------------------------------------

define('CHANGELOG_PATH',         '../../MYCA_CHANGELOG.txt');

define('ROOM_CREATION_LIMIT',    50);
define('CUSTOM_SERVICERATE',     -1);
define('ACCTSTAT_ACTIVE',        0);

define('MAX_PINS',               1000);
define('MAX_ROOMS',              500);
define('ROOMS_CREATED_PER_SPIN', 10);

// Constants used in views
define('MAX_REQUESTS_INDEX',     500);
define('MAX_ACTIVE_ROOMS',       25);

define('GRAPH_ROOT',             'c:\www\data\graphs');

define('QUEUE_STAT_TIME',        90);

define('MAX_HISTORY_SIZE',       20);

define('METERED_BILLING_METHOD', 2);
define('MONTHLY_BILL_FREQUENCY', 1);

//------------------------------------------------------------------------------
//  Misc Includes
//------------------------------------------------------------------------------

// include some basic utility functions
common_vendor('mutils');

// Load firephp
function fbpr($i) {
  common_vendor('FirePHPCore/fb');
  fb(print_r($i, true));
}
