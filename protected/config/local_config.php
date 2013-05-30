<?php

/**
 * This is a local config file. Created from sample file. 
 * Contains all the configurations which will be local to an installation.
 * @author Santosh Singh <santosh@incaendo.com>
 * This file is part of <Contest and Grants>.
 * This file can not be copied and/or distributed without the express permission 
 * of <ahref Foundation.
 */

/**
 * Caution: create a copy of this file as local_config.php and change required 
 * configurations in it
 */

/**
 * set error reporting on turn it off on production. Or just remove following 
 * two lines on production
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);


/**
 * define installation details
 */
define('BASE_URL','http://www.aggregator.com/');
define('IMAGE_URL',BASE_URL.'images/');
define('CONTEST_IMAGE_URL',BASE_URL.'uploads/contestImage/');

/**
 * constant for database configuration
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'aggregator');
define('DB_USER', 'root');
define('DB_PASS','a');

/**
 * Define aggregator API URL 
 */
define('AGGREGATOR_API_URL', 'AGGREGATOR_API_URL');

/**
 * define identity manager details
 */
define('IDENTITY_MANAGER_API_URL', 'http://10.0.1.107:8001/v1/');
define('IDM_USER_ENTITY', 'users');
define('IDM_API_KEY', 'yaiT6eequi7faig7aeSh0phi9id3iu3B');

/**
 * Define application log file name
 */
define('APP_LOG_FILE_NAME', 'aggreagtor-' . date('d-M-Y').'.log');

/**
 * Define site session cookie name
 */
define('SITE_SESSION_COOKIE_NAME', 'contest');

/**
 * Define contest Admin users email
 */
define('CONTEST_ADMIN_USERS', '["santosh@incaendo.com", "sankalp@incaendo.com"]');

/**
 * Define site language
 */
define('SITE_LANGUAGE', 'it_it');
define('SITE_TITLE', 'Contest and Grand');