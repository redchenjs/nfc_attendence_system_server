<?php
/**
 * File Name: index.php
 * PHP Version 7
 *
 * @category None
 * @package  None
 * @author   Jack Chen <redchenjs@live.com>
 * @license  https://zyiot.top/nas public
 * @version  GIT: <v2.6.1>
 * @link     https://zyiot.top/nas
 */

const DB_HOST = 'localhost:3306';
const DB_USER = 'nasadmin';
const DB_PASS = 'naspasswd';
const DB_NAME = 'nas_db';

const FTP_HOST = 'localhost';
const FTP_USER = 'anonymous';
const FTP_PASS = '';

const TEST_USER = 'test';
const WX_APP_ID = 'wx8d7f06fb7ba10c2d';

require 'utils/app.php';
require 'utils/dev.php';
require 'utils/web.php';

const HTTP_REQ_CODE_APP_GET_INFO     = 110; // 微信端获取用户信息
const HTTP_REQ_CODE_APP_GET_TOKEN    = 111; // 微信端获取验证口令
const HTTP_REQ_CODE_APP_BIND_USER    = 112; // 微信端请求绑定用户
const HTTP_REQ_CODE_APP_UNBIND_USER  = 113; // 微信端请求解绑用户
const HTTP_REQ_CODE_APP_UPDATE_PSWD  = 114; // 微信端请求修改密码
const HTTP_REQ_CODE_DEV_VERIFY_TOKEN = 210; // 设备端请求口令验证
const HTTP_REQ_CODE_DEV_UPDATE_FW    = 211; // 设备端请求固件更新

$data = file_get_contents('php://input');   // 获取POST数据
$data = json_decode($data, true);           // 解析JSON
$code = $data['request'];                   // 客户端请求码

switch ($code) {
case HTTP_REQ_CODE_APP_GET_INFO:
    $wx_code = $data['wx_code'];
    if (($wx_openid = getOpenID($wx_code)) !== null) {
        if (($user_id = getUserID($wx_openid)) !== null) {
            $last_info = getLastInfo($user_id);
            $arr = array(
                'result' => true,
                'user_id' => $user_id,
                'last_time' => $last_info['last_time'],
                'last_location' => $last_info['last_location']
            );
        } else {
            $arr = array(
                'result' => false
            );
        }
    } else {
        $arr = array(
            'result' => null
        );
    }
    header('content-type:application/json');
    echo json_encode($arr);
    break;
case HTTP_REQ_CODE_APP_GET_TOKEN:
    $wx_code = $data['wx_code'];
    if (($wx_openid = getOpenID($wx_code)) !== null) {
        $user_token = getUserToken($wx_openid);
        $arr = array(
            'result' => $user_token !== null ? true : false,
            'user_token' => $user_token
        );
    } else {
        $arr = array(
            'result' => null
        );
    }
    header('content-type:application/json');
    echo json_encode($arr);
    break;
case HTTP_REQ_CODE_APP_BIND_USER:
    $wx_code = $data['wx_code'];
    $user_id = $data['user_id'];
    $user_passwd = $data['user_passwd'];
    if (($wx_openid = getOpenID($wx_code)) !== null) {
        $errmsg = bindUser($wx_openid, $user_id, $user_passwd);
        $arr = array(
            'result' => $errmsg === null ? true : false,
            'errmsg' => $errmsg
        );
    } else {
        $arr = array(
            'result' => null
        );
    }
    header('content-type:application/json');
    echo json_encode($arr);
    break;
case HTTP_REQ_CODE_APP_UNBIND_USER:
    $wx_code = $data['wx_code'];
    $user_id = $data['user_id'];
    if (($wx_openid = getOpenID($wx_code)) !== null) {
        $errmsg = unbindUser($wx_openid, $user_id);
        $arr = array(
            'result' => $errmsg === null ? true : false,
            'errmsg' => $errmsg
        );
    } else {
        $arr = array(
            'result' => null
        );
    }
    header('content-type:application/json');
    echo json_encode($arr);
    break;
case HTTP_REQ_CODE_APP_UPDATE_PSWD:
    $wx_code = $data['wx_code'];
    $user_id = $data['user_id'];
    $old_passwd = $data['old_passwd'];
    $new_passwd = $data['new_passwd'];
    if (($wx_openid = getOpenID($wx_code)) !== null) {
        $errmsg = updatePassword($wx_openid, $user_id, $old_passwd, $new_passwd);
        $arr = array(
            'result' => $errmsg === null ? true : false,
            'errmsg' => $errmsg
        );
    } else {
        $arr = array(
            'result' => null
        );
    }
    header('content-type:application/json');
    echo json_encode($arr);
    break;
case HTTP_REQ_CODE_DEV_VERIFY_TOKEN:
    $device_mac = $data['device_mac'];
    $user_token = $data['user_token'];
    $arr = array(
        'result' => verifyUserToken($device_mac, $user_token)
    );
    header('content-type:application/json');
    echo json_encode($arr);
    break;
case HTTP_REQ_CODE_DEV_UPDATE_FW:
    $device_mac = $data['device_mac'];
    $fw_version = $data['fw_version'];
    getFirmwareUpdate($device_mac, $fw_version);
    break;
default:
    listLog();
    break;
}
?>
