<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'Mysql.php';

class Membership {

    function create_User() {


        $mysql = New Mysql_create_user();
        $un = $_POST['user'];
        $pwd = $_POST['pwd'];
        $game_name = $_POST['game'];
        if (strlen($un) > 2 && strlen($pwd) > 2) {
            if (isset($_POST['perm'])) {
                $mysql->create_user($un, $pwd, 'admin', $game_name);
            } else {
                if(strlen($game_name) > 2){
                    $mysql->create_user($un, $pwd, 'user', $game_name);
                }
            }
        } else {
            echo "Alle felter skal udfyldes";
        }
    }

    function validate_User($un, $pwd) {
        $mysql = New Mysql_login();
        $ensure_credentials = $mysql->verify_Username_and_Pass($un, $pwd);
        $_SESSION['cg'] = $ensure_credentials[1]['GameName'];
        if ($ensure_credentials[0] == true && $ensure_credentials[1]['Permission'] == "admin") {
            $_SESSION['status'] = 'authorized_admin';
            header("location: start_admin.php");
        } elseif ($ensure_credentials[0] == true && $ensure_credentials[1]['Permission'] == "user") {
            $_SESSION['status'] = 'authorized_user';
            header("location: start_user.php");
		}
		elseif ($ensure_credentials[0] == true && $ensure_credentials[1]['Permission'] == "app") {
            $_SESSION['status'] = 'authorized_downloader';
            header("location: download_app.php");
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

    function confirm_Both(){
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if ($_SESSION['status'] == 'authorized_admin' or $_SESSION['status'] == 'authorized_user')
            return;
        else
            header("location: login.php");
    }

    function confirm_Admin() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if ($_SESSION['status'] != 'authorized_admin')
            header("location: login.php");
    }

    function confirm_User() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if ($_SESSION['status'] != 'authorized_user')
    	    header('location: login.php');
    }

	function confirm_App() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if ($_SESSION['status'] != 'authorized_downloader')
    	    header('location: login.php');
    }

    function check_Active() {
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 86400)) {
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
