<?php

namespace App\Controllers;

use \Core\View;
use App\Models\UserModel;

if(!isset($_SESSION))
{
    session_start();
}
/**
 * User controller
 *
 * PHP version 7.0
 */
class User extends \Core\Controller
{

    public function inscriptionAction()
    {
        $errors = [];
        if(!empty($_POST))
        {
            $email = $_POST['email'];
            $password = $_POST['password'];

            // Contrôles de saisie
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Adresse email invalide.";
            }

            if(strlen($password) < 5)
            {
                $errors[] = "Mot de passe trop court.";
            }

            if(empty($errors))
            {
                // Enregistrer en BDD
                $user = new UserModel($email, $password);
                $user->inscription();

                header('Location: connexion');
            }
        }

        View::renderTemplate('User/inscription.html.twig', array(
            "errors" => $errors
        ));
    }

    public function connexionAction()
    {
        $errors = [];
        if(!empty($_POST))
        {
            $email = $_POST['email'];
            $password = $_POST['password'];

            if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Adresse email invalide.";
            }

            $result = UserModel::userExist($_POST['email']);

            if(!isset($result['email']))
            {
                $errors[] = "L'adresse mail et le mot de passe ne correspondent pas.";
            }else
            {
                if(password_verify($_POST['password'], $result['password']))
                {
                    $_SESSION['username'] = $email;
                    // Redirection vers page d'acceuil
                    header("Location: tests");
                }else
                {
                    $errors[] = "L'adresse mail et le mot de passe ne correspondent pas.";
                }
            }
        }
        View::renderTemplate('User/connexion.html.twig', array(
            "errors" => $errors
        ));
    }

    public function deconnexionAction()
    {
        unset($_SESSION['username']);
        header("Location: connexion");
    }
}
