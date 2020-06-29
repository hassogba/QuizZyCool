<?php
namespace App\Controllers;

use App\Config;
use App\Models\TestModel;
use \Core\View;

use \ZipArchive;

if(!isset($_SESSION))
{
    session_start();
}

/**
 * Test controller
 *
 * PHP version 7.0
 */
class Test extends \Core\Controller
{
    // Déclaration des constantes
    const VALID_TYPES = ["image", "video", "son"];
    const VALID_TYPES_IDS = ["image"=>1, "video"=>2, "son"=>3];
    const VALID_IMAGE_FORMAT = ["png", "jpg", "jpeg", "gif"];
    const VALID_VIDEO_FORMAT = ["mp4", "avi", "mkv"];
    const VALID_AUDIO_FORMAT = ["mp3", "ogg", "flac"];


    /**
     * Fonction qui s'exécute AVANT toutes les autres.
     * On vérifie que l'utilisateur est connecté avant de lui donner accès à la page qu'il demande.
     * Si ce n'est pas le cas, on le redirige vers la page de connexion.
     */
    protected function before()
    {
        if(!isset($_SESSION['username']))
        {
            header("Location: connexion");
        }
    }
    public function nouveauAction()
    {
        $errors = [];

        if(!empty($_POST))
        {
            // Si ce sont les trous qui viennent d'être définis, renvoyer vers la méthode qui
            // traite ce cas pour enregistrer en bdd ...
            if(isset($_POST['finalSubmit']))
            {
                return $this->choixTrous($_POST, $_FILES);
            }

            // ... Sinon c'est la première étape et on fait les contrôles de saisie.
            $errors = $this->controlesDeSaisie();

            // redéfinir le type ressource en mettant à la place, l'id correspondant (pour enreg. futur en bdd)
            $_POST['type'] = self::VALID_TYPES_IDS[$_POST['type']];

            if(empty($errors))
            {
                // Pas d'erreurs, passer à l'étape du choix des trous
                $_SESSION['postvalues'] = $_POST;
                return $this->choixTrous($_FILES['ressource']);
            }
        }
        View::renderTemplate('Test/new.html.twig', array(
            "errors" => $errors
        ));
    }

    public function choixTrous($file)
    {

        $newfilename = isset($newfilename) ? $newfilename : "";
        if(!isset($_POST['finalSubmit'])) {

            // Il vient de valider la première étape.
            // Découper le texte en mots et afficher la page pour choisir les trous

//            $text = utf8_encode($_SESSION['postvalues']['text']);
            $text = $_SESSION['postvalues']['text'];

            // Elimination des <sub>.+<\/sub>
            $text = preg_replace("/<sub>.+<\/sub>/", "", $text);

            // Elimination des autres balises HTML et découpage des mots du texte
            $explodedText = explode(" ", strip_tags($_SESSION['postvalues']['text']));

            // Version avec les balises HTML conservées
            $explodedTextBalise = explode(" ", $_SESSION['postvalues']['text']);

            // Enregistrement du fichier (sinon on le perdra après la soumission des trous choisis)
            // Renommer le fichier et enregistrer
            $temp = explode(".", $file["name"]);
            $newfilename = round(microtime(true)) . '.' . end($temp);
            move_uploaded_file($file["tmp_name"], "ressources/" . $newfilename);

            $_SESSION['newfilename'] = $newfilename; // pour enregistrer en bdd après soumission
        }else
        {
            // Trous choisis. Enregistrement du test en bdd
            $title = htmlspecialchars($_SESSION['postvalues']['title']);
            $subtitle = htmlspecialchars($_SESSION['postvalues']['subtitle']);

            // Supprimer tous les éléments JS que l'utilisateur malicieux aurait pu insérer dans le texte.
            // Vu qu'on l'affichera tel quel avec les éléments HTML, il faut un minimum de contrôle
            $text = preg_replace("/<script>.+<\/script>/", "", $_POST['finalText']);


            $type = htmlspecialchars($_SESSION['postvalues']['type']);
            $ressource = $_SESSION['newfilename'];

            /** @var TestModel $test */
            $test = new TestModel($title, $subtitle, $text, $type, $ressource);
            $result = $test->add();

            if($result)
            {
                header('Location: tests');
                die('');
            }
            // TODO message d'erreur
        }

        View::renderTemplate('Test/choixTrous.html.twig', array(
            "explodedText" => $explodedText,
            "explodedTextBalise" => $explodedTextBalise
        ));
    }

    public function controlesDeSaisie()
    {
        $errors = [];

        $title = htmlspecialchars($_POST['title']);
        $subtitle = htmlspecialchars($_POST['subtitle']);
        $text = ($_POST['text']); // , ENT_NOQUOTES, "UTF-8"
        $type = htmlspecialchars($_POST['type']);

        // Titre
        if(empty($title))
        {
            $errors[] = "Veuillez renseigner un titre.";
        }

        if(strlen($title) > 0 && strlen($title) < 10) // entre 1 et 10, texte trop court
        {
            $errors[] = "Titre trop court.";
        }

        // Sous titre

        if(empty($subtitle))
        {
            $errors[] = "Veuillez renseigner un sous titre.";
        }

        if(strlen($subtitle) > 0 && strlen($subtitle) < 10)
        {
            $errors[] = "Sous titre trop court.";
        }

        // Texte
        // Min 100 caractères et max 1500
        if(strlen($text) > 1500)
        {
            $errors[] = "Texte trop long.";
        }

        if(strlen($text) < 100)
        {
            $errors[] = "Texte trop court.";
        }

        // Fichier
        if(!in_array($type, self::VALID_TYPES))
        {
            $errors[] = "Veuillez choisir un type valide.";
        }else
        {
            // Validation du fichier soumis
            $errors = $this->checkFile($_FILES['ressource'], $type, $errors);
        }

        return $errors;


    }

    public function checkFile($file, $filetype, $errors)
    {
        // Récupérer le type de fichier soumis
        $type = explode("/", $file['type'])[1];

        $size = $file['size'];

        switch ($filetype) {
            case "image":
                // Max 5Mo
                if($size > 5242880)
                {
                    $errors[] = "Fichier trop volumineux.";
                }

                // Contrôle du type de fichier
                if(!in_array($type, self::VALID_IMAGE_FORMAT))
                {
                    $errors[] = "Format image invalide (\"png\", \"jpg\", \"jpeg\" ou \"gif\")";
                }
                break;
            case "video":
                // Max 10M0
                if($size > 10485760)
                {
                    $errors[] = "Fichier trop volumineux.";
                }

                // Contrôle du type de fichier
                if(!in_array($type, self::VALID_VIDEO_FORMAT))
                {
                    $errors[] = "Format vidéo invalide (\"mp4\", \"avi\" ou \"mkv\").";
                }

                break;

            case "son":
                // Max 5Mo et type valide
                if($size > 5242880)
                {
                    $errors[] = "Fichier trop volumineux.";
                }

                // Contrôle du type de fichier
                if(!in_array($type, self::VALID_AUDIO_FORMAT))
                {
                    $errors[] = "Format audio invalide (\"mp3\", \"ogg\" ou \"flac\").";
                }
                break;

                // Pas de default case. On s'est assuré qu'il s'agisse de l'un de ces trois types valides

        }

        return $errors;
    }

    public function listeAction()
    {
        View::renderTemplate('Test/index.html.twig', array(
            'tests' => TestModel::getAll()));
    }

    public function passerTestAction()
    {

        $uri = explode("-", $_SERVER['REQUEST_URI']);
        $idTest = $uri[count($uri)-1];

        $nbCorrect = 0;
        $isCorrection = false; // pour affichage du texte spécifique à la correction du test

        $test = TestModel::getOne($idTest);


        // Découper le texte du test selon les mots
        $explodedText = explode(" ", mb_convert_encoding($test['texte'], "UTF-8"));


        if(empty($_POST)) {

            // Remplacer les trous par des champs de saisie
            $texteFinal = "";

            if(isset($_SESSION['indexTrous']))
            {
                // Si déjà défini, supprimer pour ne pas créer de doublons dans le tableau des index
                unset($_SESSION['indexTrous']);
            }

            for ($i = 0; $i < sizeof($explodedText); $i++) {
                if (preg_match("/<trou>.+<\/trou>/", $explodedText[$i])) {
                    // Enregistrer les index des trous pour la validation



                    $_SESSION['indexTrous'][] = $i;
                    $explodedText[$i] = "<input type='text' name='" . $i . "' id='" . $i . "' class='texteUser form-inline margin-10' style='width:150px;' required>";
                }
                // Reconstituer le texte avec les espaces entre les mots et les champs de saisie
                $texteFinal .= $explodedText[$i] . " ";
            }

        }else {
            $isCorrection = true;
            $nbCorrect = 0;
            for ($i = 0; $i < sizeof($_SESSION['indexTrous']); $i++) {
                // Récupérer le texte original (la bonne réponse pour ce trou)
                $solution = $explodedText[$_SESSION['indexTrous'][$i]];

                // Récupérer strictement le texte solution et rien d'autre (balises, encodages caractères HTML...)
                preg_match_all('#<trou>(.*?)</trou>#', html_entity_decode($solution), $matches);
                $solution = $matches[1][0];

                // Texte en minuscule pour comparaison
                $solution = strtolower($solution);

                // Récupérer la réponse soumise
                $response = $_POST[$_SESSION['indexTrous'][$i]];
                $response = strtolower($response);

                // Comparer
                if (strcmp($solution, $response) == 0) {
                    $nbCorrect++;

                    // Construction du texte à afficher comme réponse au test
                    // Remplacer le texte par la saisie utilisateur avec un arrière plan vert
                    $explodedText[$_SESSION['indexTrous'][$i]] = "<p class='bg-success margin-right-10 correction'>" . strip_tags($explodedText[$_SESSION['indexTrous'][$i]]) . "</p>";
                } else {
                    // texte fond rouge
                    $explodedText[$_SESSION['indexTrous'][$i]] = "<p class='bg-danger margin-right-10 correction'>" . strip_tags($explodedText[$_SESSION['indexTrous'][$i]]) . "</p>";
                }
            }


            // Reconstitution du texte à afficher
            $texteFinal = "";
            for ($i = 0; $i < sizeof($explodedText); $i++) {
                $texteFinal .= $explodedText[$i] . " ";
            }
        }

        // Extension de la ressource
        $extension = explode(".", $test['ressource']);
        $extension = end($extension);

        View::renderTemplate('Test/passerTest.html.twig', array(
            'test' => $test,
            'isCorrection' => $isCorrection,
            'nbCorrect' => $nbCorrect,
            'nbTotal' => isset($_SESSION['indexTrous']) ? sizeof($_SESSION['indexTrous']) : 0,
            'texte' => $texteFinal,
            'extension' => $extension
        ));
    }

    public function export()
    {
        ////
        // Récupération du test
        ////
        $uri = explode("-", $_SERVER['REQUEST_URI']);
        $idTest = $uri[count($uri)-1];

        // Récupérer le test de la bdd
        $test = TestModel::getOne($idTest);

        //////
        // Premières lignes du fichier index : création du test et ouverture de la session
        ////

        // Création du fichier
        $indexFile = 'export/index.php';
        $handle = fopen($indexFile, 'w');

        // Ouverture de session
        $data = '<?php 
        if(!isset($_SESSION))
        {
            session_start();
        }
        ';

        // Création du test
        $data .= '
        $test["titre"] = "'.$test['titre'].'";'.
        '$test["soustitre"] = "'.$test['soustitre'].'";
        '.'$test["texte"] = "'.$test['texte'].'";
        '.'$test["typeressource"] = "'.$test['typeressource'].'";
        '.'$test["ressource"] = "'.$test['ressource'].'";'
        ;

        // Ajout du code pour la gestion du test (aff. vue, contrôles, correction)
        $controlTest = '../App/Controllers/controlTestExport.php';
        $handleControl = fopen($controlTest, 'r');
        $controlContent = fread($handleControl,filesize($controlTest));

        $data .= $controlContent;

        fwrite($handle, $data);

        ////
        // Copie des fichiers css, js et ressources
        ////
        $this->recurse_copy("css", "export/css");
        $this->recurse_copy("js", "export/js");
        $this->recurse_copy("fonts", "export/fonts");
        $this->recurse_copy("images", "export/images");

        // Création du dossier de ressource
        if(!is_dir("export/ressources"))
            mkdir("export/ressources");

        // Copier la ressource liée au test
        copy("ressources/".$test['ressource'], "export/ressources/".$test['ressource']);


        // Génération du fichier zip
        $this->Zip("export", "exportTest.zip");

        // Succès
        header("Location: exportTest.php");

        View::renderTemplate('Test/exportOk.html.twig');

    }


    function recurse_copy($src,$dst) {
        $dir = opendir($src);
        if(!is_dir($dst))
        {
            mkdir($dst);
        }

        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    recurse_copy($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }


    function Zip($source, $destination)
    {
        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }

        $zip = new \ZipArchive();
        if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
            return false;
        }

        $source = str_replace('\\', '/', realpath($source));

        if (is_dir($source) === true)
        {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file)
            {
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
                    continue;

                $file = realpath($file);

                if (is_dir($file) === true)
                {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                }
                else if (is_file($file) === true)
                {
                    $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                }
            }
        }
        else if (is_file($source) === true)
        {
            $zip->addFromString(basename($source), file_get_contents($source));
        }

        return $zip->close();
    }
}