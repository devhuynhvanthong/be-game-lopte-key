<?php
const TABLE_ACCOUNT = 'account';
const TABLE_SERVICE_SUPPORT = 'services';
const TABLE_ENCRYPTION = 'encryption';
const TABLE_SESSIONS = 'sessions';

/**
 * VALUE
 */

const VALUE_ACCOUNT_SERVICE_NAME = "account_service";

/**
 * PATH
 */

const PATH_LOGIN = "/api/check_login";
const PATH_REGISTER = "/api/insert_account";
const PATH_GET_PERSONAL_INFO = '/api/get_account_data';
const PATH_CHECK_EXPRIRED_ACCESS_TOKEN = '/api/check_exprired_access_token';
const PATH_GET_LOGIN_HISTORY = '/api/get_login_history';
const PATH_GET_RECENT_ACTIVITY = '/api/get_recent_activity';
const PATH_GET_PAYMENT_METHOD = '/api/get_payment_method';
const PATH_ADD_PAYMENT_METHOD = '/api/add_payment_method';
const PATH_EDIT_PAYMENT_METHOD = '/api/edit_payment_method';
const PATH_DELETE_PAYMENT_METHOD = '/api/delete_payment_method';
const PATH_ADD_PAYMENT = '/api/add_payment';
?>
