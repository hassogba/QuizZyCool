
$nbCorrect = 0;
$isCorrection = false; // pour affichage du texte spécifique à la correction du test

// Découper le texte du test selon les mots
$explodedText = explode(" ", $test['texte']);


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

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Test à trous - Passer test</title>
    <meta name="description" content="">
    <meta name="author" content="templatemo">
    <!--
    Visual Admin Template
    https://templatemo.com/tm-455-visual-admin
    -->
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,400italic,700' rel='stylesheet' type='text/css'>
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/templatemo-style.css" rel="stylesheet">

    <style>
        .texteUser{ width: 50px; }

        .correction{
            display:inline;
            padding:8px;
        }

        #texte
        {
            line-height: 3.5;
        }

        #texte p
        {
            display:inline;
        }
    </style>
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>
<body>
<!-- Left column -->
<div class="templatemo-flex-row">
    <div class="templatemo-sidebar">
        <header class="templatemo-site-header">
            <h1>QuizZyCool</h1>
        </header>
        <div class="profile-photo-container">
            <img src="images/profile-photo.jpg" alt="Profile Photo" class="img-responsive">
            <div class="profile-photo-overlay"></div>
        </div>
        <div class="mobile-menu-icon">
            <i class="fa fa-bars"></i>
        </div>
        <nav class="templatemo-left-nav">
            <ul>
                <li><a href="#" class="active"><i class="fa fa-home fa-fw"></i>Liste des tests</a></li>
                <li><a href="#"><i class="fa fa-bar-chart fa-fw"></i>Nouveau test</a></li>
            </ul>
        </nav>
    </div>
    <!-- Main content -->
    <div class="templatemo-content col-1 light-gray-bg">
        <div class="templatemo-top-nav-container">
            <div class="row">
                <nav class="templatemo-top-nav col-lg-12 col-md-12">
                    <ul class="text-uppercase">
                        <li><a href="#" class="active">Liste des tests</a></li>
                        <li><a href="#">Nouveau test</a></li>
                    </ul>
                </nav>
            </div>
        </div>

        <div class="templatemo-content-container">
            <?php $nbTotal = isset($_SESSION['indexTrous']) ? sizeof($_SESSION['indexTrous']) : 0; ?>
            <div class="templatemo-flex-row flex-content-row">
                <div class="templatemo-content-widget white-bg col-2">
                     <h2 class="templatemo-inline-block"><?php if($isCorrection){ echo " Correction : "; } echo $test['titre'] . " (" . $test['soustitre'] . ")" ?></h2>
                    <br><h3 class="templatemo-inline-block margin-10"><?php if($isCorrection) { echo $nbCorrect . " réponse(s) correcte(s) sur " . $nbTotal . "."; } ?></h3>
                    <hr>
                    <form method="post">
                        <div class="row">
                            <?php if($test['typeressource'] == 1) { // image ?>
                                <img class='img-fluid center-block' width="300" src="ressources/<?php echo $test['ressource']; ?>"/>
                            <?php  } elseif($test['typeressource'] == 2) { // vidéo ?>
                                <div class="embed-responsive embed-responsive-16by9">
                                    <iframe class="embed-responsive-item" src="ressources/<?php echo $test['ressource']; ?>" allowfullscreen></iframe>
                                </div>
                            <?php  } elseif($test['typeressource'] == 3) { ?> // audio
                                <audio controls>
                                    <source src="ressources/<?php echo $test['ressource']; ?>" type="audio/<?php echo $extension; ?>">
                                    Votre navigateur ne supporte pas la balise audio.
                                </audio>
                            <?php } ?>
                        </div>
                        <div id="texte"></div>
						<?php if(!$isCorrection){ ?> <input type="submit" value="Valider !" class="btn btn-success pull-right"> <?php } ?>
                    </form>



                </div>
            </div>

            <footer class="text-right">
                <p>Copyright &copy; 2020 Thibaut ASSOGBA
                    | QuizZyCool</p>
            </footer>
        </div>


    </div>
</div>

<!-- JS -->
<script src="js/jquery-1.11.2.min.js"></script>      <!-- jQuery -->
<script src="js/jquery-migrate-1.2.1.min.js"></script> <!--  jQuery Migrate Plugin -->
<script src="https://www.google.com/jsapi"></script> <!-- Google Chart -->

<script type="text/javascript" src="js/templatemo-script.js"></script>      <!-- Templatemo Script -->
<script lang="js">
    $('#texte').html("<?php echo $texteFinal; ?>")
</script>
</body>
</html>


