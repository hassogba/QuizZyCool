<?php

namespace App\Models;

use PDO;

/**
 * Test model
 *
 * PHP version 7.0
 */
class TestModel extends \Core\Model
{
    private $titre;
    private $soustitre;
    private $texte;
    private $typeRessource;
    private $ressource;

    public function __construct($titre, $soustitre, $texte, $typeRessource, $ressource)
    {
        $this->titre = $titre;
        $this->soustitre = $soustitre;
        $this->texte = $texte;
        $this->typeRessource = $typeRessource;
        $this->ressource = $ressource;
    }

    /**
     * @return mixed
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * @param mixed $titre
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;
    }

    /**
     * @return mixed
     */
    public function getSoustitre()
    {
        return $this->soustitre;
    }

    /**
     * @param mixed $soustitre
     */
    public function setSoustitre($soustitre)
    {
        $this->soustitre = $soustitre;
    }

    /**
     * @return mixed
     */
    public function getTexte()
    {
        return $this->texte;
    }

    /**
     * @param mixed $texte
     */
    public function setTexte($texte)
    {
        $this->texte = $texte;
    }

    /**
     * @return mixed
     */
    public function getTypeRessource()
    {
        return $this->typeRessource;
    }

    /**
     * @param mixed $typeRessource
     */
    public function setTypeRessource($typeRessource)
    {
        $this->typeRessource = $typeRessource;
    }

    /**
     * @return mixed
     */
    public function getRessource()
    {
        return $this->ressource;
    }

    /**
     * @param mixed $ressource
     */
    public function setRessource($ressource)
    {
        $this->ressource = $ressource;
    }

    /**
     * Ajout d'un test à la bdd
     *
     * @return bool
     */
    public function add()
    {
        $db = static::getDB();
        $stmt = $db->prepare("INSERT INTO test(titre, soustitre, texte, typeressource, ressource)
                              VALUES (:titre, :soustitre, :texte, :typeressource, :ressource)");
        return $stmt->execute(array(
            ':titre' => $this->titre,
            ':soustitre' => $this->soustitre,
            ':texte' => $this->texte,
            ':typeressource' => $this->typeRessource,
            ':ressource' => $this->ressource
        ));
    }

    public static function getAll()
    {
        $db = static::getDB();
        $stmt = $db->query('SELECT * FROM test');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getOne($id)
    {
        $db = static::getDB();
        $stmt = $db->prepare("SELECT * FROM test WHERE id = :id");
        $stmt->bindValue(":id", $id);
        $stmt->execute();
        return $stmt->fetch();
    }



}
