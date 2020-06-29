<?php

namespace App\Models;

use PDO;

/**
 *  user model
 *
 * PHP version 7.0
 */
class UserModel extends \Core\Model
{
    private $email;
    private $password;

    public function __construct($email, $password)
    {
        $this->email = $email;
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }


    /**
     * Get all the users as an associative array
     *
     * @return array
     */
    public static function getAll()
    {
        $db = static::getDB();
        $stmt = $db->query('SELECT id, name FROM users');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function inscription()
    {
        $db = static::getDB();

        $encodedPassword = password_hash($this->password, PASSWORD_DEFAULT);

        $stmt = $db->prepare("INSERT INTO user(email, password) VALUES (:email, :password)");
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $encodedPassword);

        $stmt->execute();

    }

    public static function userExist($email)
    {
        $db = static::getDB();
        $stmt = $db->prepare('SELECT email, password FROM user WHERE email=:email');

        $stmt->bindParam(":email", $email);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;



    }

}
