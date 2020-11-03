<?php

include_once("model/Model.php");

//Needs to be included so that we can use $_SESSION
session_start();

class Controller {

    private $model;

    public function __construct() {
        $this->model = new Model();
    }

    public function invoke() {
        $command = isset($_REQUEST["cmd"]) ? $_REQUEST["cmd"] : "mainpage";

        if ($command == "mainpage") {
            if (isset($_SESSION["username"]) && ($_SESSION["loggedin"] == 1)) {
                $username = $_SESSION["username"];
                $this->includeMainPage($username);
            } else {
                include("view/login.php");
            }
        } elseif ($command == "login") {
            $username = (isset($_POST["username"])) ? $this->parse_input($_POST["username"]) : "";
            $password = (isset($_POST["password"])) ? $this->parse_input($_POST["password"]) : "";

            $res = $this->model->checkLoginCredentials($username, $password);

            if ($res == True) {
                $_SESSION["loggedin"] = 1;
                $_SESSION["username"] = $username;
                // set active
                $this->model->setActive($username);
                //get the users page.
                $this->includeMainPage($username);
            } else {
                echo "<div class=\"alert alert-danger alert-dismissable fade in\"><a href=\"\" class=\"close\" data-dismiss=\"alert\" aria-label=\"close\">&times;</a>WARNING!The Combination of Username and Password does not exist</div>";
                include("view/login.php");
            }
        } elseif ($command == "changePass") {
            $username = $_SESSION["username"];
            $forename = $this->model->getForename($username);
            $surname = $this->model->getSurname($username);
            include("view/changePass.php");
        } elseif ($command == "changePassword") {
            $username = $_SESSION["username"];
            $oldPassword = (isset($_POST["oldPassword"])) ? $this->parse_input($_POST["oldPassword"]) : "";
            $newPassword = (isset($_POST["newPassword"])) ? $this->parse_input($_POST["newPassword"]) : "";
            $rNewPassword = (isset($_POST["rNewPassword"])) ? $this->parse_input($_POST["rNewPassword"]) : "";

            // check oldPassword is the same
            $res = $this->model->checkLoginCredentials($username, $oldPassword);
            if ($res == True && ($newPassword == $rNewPassword)) {
                $this->model->changePassword($username, $newPassword);
                $this->displaySuccessDialog("Successful change of password");
                $this->includeMainPage($username);
            } else if ($res == False) {
                $this->displayDangerDialog("Current password is wrong");
                $forename = $this->model->getForename($username);
                $surname = $this->model->getSurname($username);
                include("view/changePass.php");
            } else {
                $this->displayDangerDialog("New password mismatch");
                $forename = $this->model->getForename($username);
                $surname = $this->model->getSurname($username);
                include("view/changePass.php");
                
            }
        } elseif ($command == "register") {
            $forename = (isset($_POST["forename"])) ? $this->parse_input($_POST["forename"]) : "";
            $surname = (isset($_POST["surname"])) ? $this->parse_input($_POST["surname"]) : "";
            $username = (isset($_POST["username"])) ? $this->parse_input($_POST["username"]) : "";
            $password = (isset($_POST["password"])) ? $this->parse_input($_POST["password"]) : "";
            $email = (isset($_POST["email"])) ? $this->parse_input($_POST["email"]) : "";

            if ($email) {
                $validEmail = $this->emailchecker($_POST["email"]);
            }

            $res = false;
            if ($validEmail) {
                $res = $this->model->addUser($forename, $surname, $email, $username, $password);
            } else {
                $this->displayDangerDialog("Invalid Email");
                include("view/register.php");
                return;
            }

            if ($res == true) {
                $this->displaySuccessDialog("Registration Successful");
                include("view/login.php");
                return;
            } else {
                $this->displayDangerDialog("Account with the username exists.Please refill the form.");
                include("view/login.php");
            }
        } elseif ($command == "Forgot my password") {
            include("view/forgetPassword.php");
        } elseif ($command == "forgetPassword") {
            $email = (isset($_POST["email"])) ? $this->parse_input($_POST["email"]) : "";
            $res = $this->model->checkEmailExists($email);
            if ($res == true) {
                $this->model->sendForgetEmail($email);
                include("view/login.php");
            } else {
                $this->displayDangerDialog("Provided email does not exist");
                include("view/forgetPassword.php");
            }
        } elseif ($command == "logout") {
            // set not active
            $username = $_SESSION["username"];
            $this->model->setNotActive($username);
            $_SESSION["username"] = "";
            $_SESSION["loggedin"] = 0;

            include("view/login.php");
        } elseif ($command == "sendMessage") {
            $message = $this->parse_input($_POST["message"]);
            $username = $_SESSION["username"];

            $dest = $this->model->getTalkingTo($username);
            $userId = $this->model->getCurrentUserId($username);
            $this->model->sendMessage($userId, $dest, $message);

            $this->includeMainPage($username);
        } elseif ($command == "loadMessage") {
            $username = $_SESSION["username"];
            $currentUserId = $this->model->getCurrentUserId($username);
            $receiverId = $_REQUEST["id"];

            $messageHistory = $this->model->fetchMessage($currentUserId, $receiverId);
            $chatHistoryHTML = "";
            $this->model->setTalkingTo($username, $receiverId);

            if (count($messageHistory) > 0) {
                foreach ($messageHistory as $currMessage) {
                    $chatHistoryHTML = $chatHistoryHTML . $this->createMessageBubble($currMessage, $currentUserId);
                }
            } else {
                $chatHistoryHTML = "<p>What you doin' chap? Go on! Chat!!!!</p>";
            }

            echo $chatHistoryHTML;
        } elseif ($command == "getListPeople") {

            $username = $_SESSION["username"];
            $onlineUsers = $this->model->getAllOnlineUsers($username);
            $offlineUsers = $this->model->getAllOfflineUsers($username);
            $userListHTML = "";
            foreach ($onlineUsers as $person) {
             
                $receiverId = isset($_REQUEST["id"]) ? $_REQUEST["id"] : -1;
                $userListHTML = $userListHTML . '<li id="' . $person['ID'] . '" ';
                
                if($receiverId == $person["ID"]){
                    $userListHTML  = $userListHTML . 'class="active" ';
                }
                
                $userListHTML = $userListHTML .'onclick="getChatHistory(this);">
                                        <a href="#">
                                            <i class="glyphicon glyphicon-user" style="color:green"></i>
                                            ' . $person['FORENAME'] . ' ' . $person['SURNAME'] . '</a>
                                    </li>';
            }

            foreach ($offlineUsers as $person) {
                $receiverId = isset($_REQUEST["id"]) ? $_REQUEST["id"] : -1;
                $userListHTML = $userListHTML . '<li id="' . $person['ID'] . '" ';
                
                if($receiverId == $person["ID"]){
                    $userListHTML  = $userListHTML . 'class="active" ';
                }
                
                $userListHTML = $userListHTML .'onclick="getChatHistory(this);">
                                        <a href="#">
                                            <i class="glyphicon glyphicon-user" style="color:red"></i>
                                            ' . $person['FORENAME'] . ' ' . $person['SURNAME'] . '</a>
                                    </li>';
            }

            echo $userListHTML;
        }
        else {
            $this->includeMainPage("");
        }
    }

    /* Used to give the message the appropriate message bubble depending on
      if the message was sent or received */

    private function createMessageBubble($message, $currentUserId) {
        $chatBubble = "";
        $timestamp = $message["SEND_TIMESTAMP"];
        if ($message["SENDER_ID"] === $currentUserId) {
            $chatBubble = '<div class="msgc" style="margin-bottom: 30px;"> <div class="msg msgfrom">' . $message["MESSAGE_BODY"] . '</div> <div class="msgarrRight msgarrfrom"></div>' .
                    '<div class="msgsent-stamp msgsentfrom-stamp">Sent At: ' . $timestamp . '</div> </div>';
        } else {
            $chatBubble = '<div class="msgc"> <div class="msg">' . $message["MESSAGE_BODY"] . '</div> <div class="msgarrLeft"></div> ' .
                    '<div class="msgsent-stamp">Sent At: ' . $timestamp . '</div> </div>';
        }

        return $chatBubble;
    }

    private function parse_input($par) {//needed for input parsing
        $par = trim($par);
        $par = stripslashes($par);
        $par = htmlspecialchars($par);
        //$par = mysql_real_escape_string($par);
        $par = htmlspecialchars($par, ENT_IGNORE, 'utf-8');
        //$par = strip_tags($par);
        return $par;
    }

    private function emailchecker($emailCheck) {
        if (filter_var($emailCheck, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            return false;
        }
    }

    private function includeMainPage($username) {
        //names of the other users
        //chat history
        $forename = $this->model->getForename($username);
        $surname = $this->model->getSurname($username);

        include("view/mainpage.php");
    }

    private function displayDangerDialog($message) {
        echo "<div class=\"alert alert-danger alert-dismissable fade in\"><a href=\"\" class=\"close\" data-dismiss=\"alert\" aria-label=\"close\">&times;</a>WARNING!" . $message . "</div>";
    }

    private function displaySuccessDialog($message) {
        echo "<div class=\"alert alert-success alert-dismissable fade in\"><a href=\"\" class=\"close\" data-dismiss=\"alert\" aria-label=\"close\">&times;</a>SUCCESS!" . $message . "</div>";
    }

}

?>