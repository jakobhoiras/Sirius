<?php

require 'Mysql.php';

class Membership {

    function create_User() {


        $mysql = New Mysql_create_user();
        $un = $_POST['user'];
        $pwd = $_POST['pwd'];
        if (strlen($un) > 2 || strlen($pwd) > 2) {
            if (isset($_POST['perm'])) {
                $mysql->create_user($un, $pwd, 'admin');
            } else {
                $mysql->create_user($un, $pwd, 'user');
            }
        } else {
            echo "Alle felter skal udfyldes";
        }
    }

    function validate_User($un, $pwd) {
        $mysql = New Mysql_login();
        $ensure_credentials = $mysql->verify_Username_and_Pass($un, $pwd);

        if ($ensure_credentials[0] == true && $ensure_credentials[1] == "admin") {
            $_SESSION['status'] = 'authorized_admin';
            header("location: start.php");
        } elseif ($ensure_credentials[0] == true && $ensure_credentials[1] == "user") {
            $_SESSION['status'] = 'authorized_user';
            header("location: spil_GUI.php");
        } else
            return "Please enter a correct username and password";
    }

    function log_User_Out() {
        if (isset($_SESSION['status'])) {
            unset($_SESSION['status']);
            if (isset($_COOKIE[session_name()])) {
                setcookie(session_name(), '', time() - 10000);
                session_unset();
                session_destroy();
            }
        }
    }

    function confirm_Admin() {
        session_start();
        if ($_SESSION['status'] != 'authorized_admin')
            header("location: login.php");
    }

    function confirm_User() {
        session_start();
        if ($_SESSION['status'] != 'authorized_user')
            header("location: login.php");
    }

    function check_Active() {
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
            // last request was more than 30 minutes ago
            setcookie(session_name(), '', time() - 10000);
            session_unset();
            session_destroy();
        }
        $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
    }

}

#class Database {
#
#	function save_Kunde($navn) {
#		$mysql = New Mysql();
#		$inserted = $mysql->insert_Kunde($navn1, $navn2, $navn3);
#		if($inserted){
#			return $inserted;
#		} else echo "Fejl, kunden blev ikke gemt";
#	}
#
#	function søg_Kunde($navn) {
#		$mysql = New Mysql();
#		$inserted = $mysql->get_Kunde($navn);
#		if($inserted[0][0]==true){
#			return $inserted;
#		} else echo "Fejl, kunden blev ikke fundet";
#	}
#}
