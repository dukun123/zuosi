<?php
/**
 * Created by PhpStorm.
 * User: dukun
 * Date: 09/27/2018
 * Time: 18:08
 */

namespace Home\Controller;

use Think\Controller;

class UserController extends Controller
{


    //login API
    public function login()
    {
        if (!IS_POST) {
            $this->ajaxReturn(json_style(400, "bad request, incorrect submit method", 10015));
        }
        $postData = file_get_contents("php://input");
        $resultData = json_decode($postData, true);
        $email = $resultData['email'];
        $password = $resultData['password'];
        if (!isset($email) || !isset($password)) {
            $this->ajaxReturn(json_style(400, "bad request, lack paramters", 10001));
        }

        $tb_user = M('user');
        $where['email'] = $email;
        $res = $tb_user->where($where)->find();
        if (isset($res)) {
            if (password_verify($password, $res['password'])) {
                $token = $this->getRandomString(20);
                $_SESSION['tokenID'] = $token;
                $_SESSION['userid'] = $res['id'];
                $this->ajaxReturn(json_style(200, "login success", 10002));
            } else {
                $this->ajaxReturn(json_style(401, "password error,try again", 10003));
            }
        } else {
            $this->ajaxReturn(json_style(401, "the email is not exist", 10004));

        }
    }


    // register API
    public function register()
    {
        if (!IS_POST) {
            $this->ajaxReturn(json_style(400, "bad request, incorrect submit method", 10015));
        }

        $postData = file_get_contents("php://input");
        $resultData = json_decode($postData, true);
        if (!isset($resultData['email']) || !isset($resultData['password'])) {
            $this->ajaxReturn(json_style(400, "bad request, lack paramters", 10001));
        }

        $data['email'] = $resultData['email'];

        $tb_user = M('user');
        //first retrive from dB
        $res = $tb_user->where($data)->find();
        if (isset($res)) {
            $this->ajaxReturn(json_style(401, "your emial has been used ,please change one", 10005));
        } else {
            $data['password'] = password_hash($resultData['password'], PASSWORD_BCRYPT);
            $res = $tb_user->add($data);
            if ($res) {

                $token = $this->getRandomString(20);
                $_SESSION['tokenID'] = $token;
                $_SESSION['userid'] = $res;
                $this->ajaxReturn(json_style(200, "Success!!", 10006));
            } else {
                $this->ajaxReturn(json_style(500, "database error", 10007));
            }
        }
    }

    public function getRandomString($len, $chars = null)
    {
        if (is_null($chars)) {
            $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        }
        mt_srand(10000000 * (double)microtime());
        for ($i = 0, $str = '', $lc = strlen($chars) - 1; $i < $len; $i++) {
            $str .= $chars[mt_rand(0, $lc)];
        }
        return $str;
    }

}