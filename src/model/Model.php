<?php

require_once 'model/Database.php';

class Model {
    public function addUser($forename, $surname, $email, $username, $password) {
        if ($this->checkUserExist($username)) {
            return false;
        }
        $options = ["salt" => "9fda749430593bd5a5c01b"];
        $hasAndSalt = password_hash($password, PASSWORD_BCRYPT, $options);

        $db = Database::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("INSERT INTO accounts_users (FORENAME, SURNAME, EMAIL, USERNAME, PASSWORD, is_active, TALKING_TO)
                    VALUES (?, ?, ?, ?, ?, 0, 0)");
        $stmt->bindParam(1, $forename);
        $stmt->bindParam(2, $surname);
        $stmt->bindParam(3, $email);
        $stmt->bindParam(4, $username);
        $stmt->bindParam(5, $hasAndSalt);
        $stmt->execute();

        return true;
    }

    public function setActive($username) {
        $this->changeActive($username, 1);
    }

    public function setNotActive($username) {
        $this->changeActive($username, 0);
    }

    private function changeActive($username, $val) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("UPDATE accounts_users SET is_active = ? WHERE USERNAME = ?");
        $stmt->bindParam(1, $val);
        $stmt->bindParam(2, $username);
        $stmt->execute();
    }

    public function checkLoginCredentials($username, $password) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $options = ["salt" => "9fda749430593bd5a5c01b"];
        $hash = password_hash($password, PASSWORD_BCRYPT, $options);
        $stmt = $conn->prepare('SELECT USERNAME,PASSWORD FROM accounts_users WHERE USERNAME = ? AND PASSWORD = ?');
        $stmt->bindParam(1, $username);
        $stmt->bindParam(2, $hash);
        $stmt->execute();
        return ($stmt->rowCount() > 0);
    }

    private function checkUserExist($username) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM accounts_users WHERE USERNAME = ?");
        $stmt->bindParam(1, $username);
        $stmt->execute();
        return ($stmt->rowCount() > 0);
    }

    public function getAllOnlineUsers($username) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT ID, FORENAME, SURNAME FROM accounts_users WHERE USERNAME != ? AND is_active = 1");
        $stmt->bindParam(1, $username);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllOfflineUsers($username) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT ID, FORENAME, SURNAME FROM accounts_users WHERE USERNAME != ? AND is_active = 0");
        $stmt->bindParam(1, $username);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function checkEmailExists($email) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM accounts_users WHERE EMAIL = ?");
        $stmt->bindParam(1, $email);
        $stmt->execute();
        return ($stmt->rowCount() > 0);
    }

    public function changePassword($username, $password) {
        $options = ["salt" => "9fda749430593bd5a5c01b"];
        $hasAndSalt = password_hash($password, PASSWORD_BCRYPT, $options);

        $db = Database::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("UPDATE accounts_users set PASSWORD=? where USERNAME = ?");
        $stmt->bindParam(1, $hasAndSalt);
        $stmt->bindParam(2, $username);
        $stmt->execute();
        return ($stmt->rowCount() > 0);
    }

    private function getUserByEmail($email) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT USERNAME FROM accounts_users WHERE EMAIL = ?");
        $stmt->bindParam(1, $email);
        $stmt->execute();
        return $stmt->fetchAll()[0]["USERNAME"];
    }

    public function sendForgetEmail($email) {
        // new pass
        $newPass = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 5)), 0, 5);
        $user = $this->getUserByEmail($email);
        $this->changePassword($user, $newPass);

        // email
        $subject = 'Reset Password Request';
        $message = 'Your new password is: ' . $newPass;
        $headers = "from: Group H CS312 <#webleedmaroon@strathclyde.com>\n";

        mail($email, $subject, $message, $headers);
    }

    public function sendMessage($userId, $dest, $message) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("INSERT INTO accounts_message_table (SENDER_ID, RECEIVER_ID, MESSAGE_BODY)
                    VALUES (?, ?, ?)");
        $stmt->bindParam(1, $userId);
        $stmt->bindParam(2, $dest);
        $stmt->bindParam(3, $message);
        $stmt->execute();
    }

    public function fetchMessage($userId, $dest) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT SENDER_ID, RECEIVER_ID, MESSAGE_BODY, SEND_TIMESTAMP FROM accounts_message_table WHERE SENDER_ID = ? AND RECEIVER_ID = ? 
                                OR SENDER_ID = ? AND RECEIVER_ID = ?");
        $stmt->bindParam(1, $userId);
        $stmt->bindParam(2, $dest);
        $stmt->bindParam(3, $dest);
        $stmt->bindParam(4, $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getForename($username) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT FORENAME FROM accounts_users WHERE USERNAME = ?");
        $stmt->bindParam(1, $username);
        $stmt->execute();
        return $stmt->fetchAll()[0]["FORENAME"];
    }

    public function getSurname($username) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT SURNAME FROM accounts_users WHERE USERNAME = ?");
        $stmt->bindParam(1, $username);
        $stmt->execute();
        return $stmt->fetchAll()[0]["SURNAME"];
    }

    public function getCurrentUserId($username) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT ID FROM accounts_users WHERE USERNAME = ?");
        $stmt->bindParam(1, $username);
        $stmt->execute();
        return $stmt->fetchAll()[0]["ID"];
    }

    public function setTalkingTo($username, $talkingToId) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("UPDATE accounts_users SET TALKING_TO = ? WHERE USERNAME = ?");
        $stmt->bindParam(1, $talkingToId);
        $stmt->bindParam(2, $username);
        $stmt->execute();
        return ($stmt->rowCount() > 0);
    }

    public function getTalkingTo($username) {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT TALKING_TO FROM accounts_users WHERE USERNAME = ?");
        $stmt->bindParam(1, $username);
        $stmt->execute();
        return $stmt->fetchAll()[0]["TALKING_TO"];
    }
}
?>
