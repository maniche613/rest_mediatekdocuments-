<?php

include_once("ConnexionPDO.php");

/**
 * Classe de construction des requêtes SQL à envoyer à la BDD
 */
class AccessBDD {

    public $login = "root";
    public $mdp = "";
    public $bd = "mediatek86";
    public $serveur = "localhost";
    public $port = "3306";
    public $conn = null;

    /**
     * constructeur : demande de connexion à la BDD
     */
    public function __construct() {
        try {
            $this->conn = new ConnexionPDO($this->login, $this->mdp, $this->bd, $this->serveur, $this->port);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * récupération de toutes les lignes d'une table
     * @param string $table nom de la table
     * @return lignes de la requete
     */
    public function selectAll($table) {
        if ($this->conn != null) {
            switch ($table) {
                case "livre" :
                    return $this->selectAllLivres();
                case "dvd" :
                    return $this->selectAllDvd();
                case "revue" :
                    return $this->selectAllRevues();
                case "abonnements" :
                    return $this->selectAllAbonnements();
                case "utilisateur" :
                    return $this->selectAllUtilisateurs();
                default:
                    // cas d'un select portant sur une table simple, avec tri sur le libellé
                    return $this->selectAllTableSimple($table);
            }
        } else {
            return null;
        }
    }

    /**
     * récupération d'une ligne d'une table
     * @param string $table nom de la table
     * @param string $id id de la ligne à récupérer
     * @return ligne de la requete correspondant à l'id
     */
    public function selectOne($table, $id) {
        if ($this->conn != null) {
            switch ($table) {
                case "exemplaire" :
                    return $this->selectAllExemplairesRevue($id);
                case "commandedocument" :
                    return $this->selectAllCommandesLivre($id);
                case "abonnement" :
                    return $this->selectAllAbonnement($id);
                default:
                    // cas d'un select portant sur une table simple			
                    $param = array(
                        "id" => $id
                    );
                    return $this->conn->query("select * from $table where id=:id;", $param);
            }
        } else {
            return null;
        }
    }

    /**
     * récupération de toutes les lignes de d'une table simple (sans jointure) avec tri sur le libellé
     * @param type $table
     * @return lignes de la requete
     */
    public function selectAllTableSimple($table) {
        $req = "select * from $table order by libelle;";
        return $this->conn->query($req);
    }

    /**
     * récupération de toutes les lignes de la table Livre et les tables associées
     * @return lignes de la requete
     */
    public function selectAllLivres() {
        $req = "Select l.id, l.ISBN, l.auteur, d.titre, d.image, l.collection, ";
        $req .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $req .= "from livre l join document d on l.id=d.id ";
        $req .= "join genre g on g.id=d.idGenre ";
        $req .= "join public p on p.id=d.idPublic ";
        $req .= "join rayon r on r.id=d.idRayon ";
        $req .= "order by titre ";
        return $this->conn->query($req);
    }

    /**
     * récupération de toutes les lignes de la table DVD et les tables associées
     * @return lignes de la requete
     */
    public function selectAllDvd() {
        $req = "Select l.id, l.duree, l.realisateur, d.titre, d.image, l.synopsis, ";
        $req .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $req .= "from dvd l join document d on l.id=d.id ";
        $req .= "join genre g on g.id=d.idGenre ";
        $req .= "join public p on p.id=d.idPublic ";
        $req .= "join rayon r on r.id=d.idRayon ";
        $req .= "order by titre ";
        return $this->conn->query($req);
    }

    /**
     * récupération de toutes les lignes de la table Revue et les tables associées
     * @return lignes de la requete
     */
    public function selectAllRevues() {
        $req = "Select l.id, l.periodicite, d.titre, d.image, l.delaiMiseADispo, ";
        $req .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $req .= "from revue l join document d on l.id=d.id ";
        $req .= "join genre g on g.id=d.idGenre ";
        $req .= "join public p on p.id=d.idPublic ";
        $req .= "join rayon r on r.id=d.idRayon ";
        $req .= "order by titre ";
        return $this->conn->query($req);
    }
    /**
     * récupération de toutes les lignes de la table Abonnement et les tables associées
     * @return lignes de la requete
     */
    public function selectAllAbonnements(){
        $req = "Select a.id, c.dateCommande, c.montant, a.dateFinAbonnement, a.idRevue, d.titre ";
        $req .= "from abonnement a join commande c on c.id=a.id ";
        $req .= "join document d on d.id=a.idRevue ";
        $req .= "order by c.dateCommande DESC";
        return $this->conn->query($req);
    }
    /**
     * récupération de toutes les lignes de la table Utilisateur et les tables associées
     * @return lignes de la requete
     */
    public function selectAllUtilisateurs(){
        $req = "Select u.id, u.login, u.password, u.idService, s.type ";
        $req .= "from utilisateur u join service s on u.idService=s.id";
        return $this->conn->query($req);
    }
    
    /**
     * récupération de tous les abonnement d'une revue
     * @param string $id id de la revue
     * @return lignes de la requete
     */
    public function selectAllAbonnement($id){
       $param = array(
            "id" => $id
        );
        $req = "Select a.id, c.dateCommande, c.montant, a.dateFinAbonnement, a.idRevue, d.titre ";
        $req .= "from abonnement a join commande c on a.id=c.id ";
        $req .= "join document d on d.id=a.idRevue ";
        $req .= "where a.idRevue = :id ";
        $req .= "order by c.dateCommande DESC";
        return $this->conn->query($req, $param);
    }

    /**
     * récupération de tous les exemplaires d'une revue
     * @param string $id id de la revue
     * @return lignes de la requete
     */
    public function selectAllExemplairesRevue($id) {
        $param = array(
            "id" => $id
        );
        $req = "Select e.id, e.numero, e.dateAchat, e.photo, e.idEtat ";
        $req .= "from exemplaire e join document d on e.id=d.id ";
        $req .= "where e.id = :id ";
        $req .= "order by e.dateAchat DESC";
        return $this->conn->query($req, $param);
    }

    /**
     * récupération de toutes les commandes d'un livre
     * @param string $id id du livre
     * @return lignes de la requete
     */
    public function selectAllCommandesLivre($id) {
        $param = array(
            "id" => $id
        );
        $req = "Select c.id, d.dateCommande, d.montant, c.nbExemplaire, c.idLivreDvd, c.idSuivi, s.libelle as suivi ";
        $req .= "from commandedocument c join commande d on c.id=d.id ";
        $req .= "join suivi s on s.id=c.idSuivi ";
        $req .= "where c.idLivreDvd = :id ";
        $req .= "order by d.dateCommande DESC";
        return $this->conn->query($req, $param);
    }

    /**
     * suppresion d'une ou plusieurs lignes dans une table
     * @param string $table nom de la table
     * @param array $champs nom et valeur de chaque champs
     * @return true si la suppression a fonctionné
     */
    public function delete($table, $champs) {
        if ($this->conn != null) {
            switch ($table) {
                case "commandedocument" :
                    return $this->deleteCommandeDocument($table, $champs);
                case "abonnement" :
                    return $this->deleteAbonnement($table, $champs);
                default :// construction de la requête
                    $requete = "delete from $table where ";
                    foreach ($champs as $key => $value) {
                        $requete .= "$key=:$key and ";
                    }
                    // (enlève le dernier and)
                    $requete = substr($requete, 0, strlen($requete) - 5);
                    return $this->conn->execute($requete, $champs);
            }
        } else {
            return null;
        }
    }

    /**
     * suppresion d'une ou plusieurs lignes dans CommandeDocument puis dans Commande
     * @param string $table nom de la table commandedocument
     * @param array $champs nom et valeur de chaque champs
     * @return true si la suppression a fonctionné
     */
    public function deleteCommandeDocument($table, $champs) {
        $params = array(
            "id" => $champs["Id"],
            "nbExemplaire" => $champs["NbExemplaire"],
            "idLivreDvd" => $champs["IdLivreDvd"],
            "idSuivi" => $champs["IdSuivi"]
        );
        $requete = "delete from $table where ";
        foreach ($params as $key => $value) {
            $requete .= "$key=:$key and ";
        }
        // (enlève le dernier and)
        $requete = substr($requete, 0, strlen($requete) - 5);
        $result1 = $this->conn->execute($requete, $params);
        $params = array(
            "id" => $champs["Id"],
            "dateCommande" => $champs["DateCommande"],
            "montant" => $champs["Montant"]
        );
        $requete = "delete from commande where ";
        foreach ($params as $key => $value) {
            $requete .= "$key=:$key and ";
        }
        // (enlève le dernier and)
        $requete = substr($requete, 0, strlen($requete) - 5);
        $result2 = $this->conn->execute($requete, $params);
        //retourne les deux
        return $result1 && $result2;
    }
    
    
    /**
     * suppresion d'une ou plusieurs lignes dans Abonnement puis dans Commande
     * @param string $table nom de la table commandedocument
     * @param array $champs nom et valeur de chaque champs
     * @return true si la suppression a fonctionné
     */
    public function deleteAbonnement($table, $champs) {
        $params = array(
            "id" => $champs["Id"],
            "dateFinAbonnement" => $champs["DateFinAbonnement"],
            "idRevue" => $champs["IdRevue"]
        );
        $requete = "delete from $table where ";
        foreach ($params as $key => $value) {
            $requete .= "$key=:$key and ";
        }
        // (enlève le dernier and)
        $requete = substr($requete, 0, strlen($requete) - 5);
        $result1 = $this->conn->execute($requete, $params);
        $params = array(
            "id" => $champs["Id"],
            "dateCommande" => $champs["DateCommande"],
            "montant" => $champs["Montant"]
        );
        $requete = "delete from commande where ";
        foreach ($params as $key => $value) {
            $requete .= "$key=:$key and ";
        }
        // (enlève le dernier and)
        $requete = substr($requete, 0, strlen($requete) - 5);
        $result2 = $this->conn->execute($requete, $params);
        //retourne les deux
        return $result1 && $result2;
    }

    /**
     * ajout d'une ligne dans une table
     * @param string $table nom de la table
     * @param array $champs nom et valeur de chaque champs de la ligne
     * @return true si l'ajout a fonctionné
     */
    public function insertOne($table, $champs) {
        if ($this->conn != null && $champs != null) {
            switch ($table) {
                case "commandedocument" :
                    return $this->insertOneCommandeDocument($table, $champs);
                case "abonnement" :
                    return $this->insertOneAbonnement($table, $champs);
                default :
                    // construction de la requête
                    $requete = "insert into $table (";
                    foreach ($champs as $key => $value) {
                        $requete .= "$key,";
                    }
                    // (enlève la dernière virgule)
                    $requete = substr($requete, 0, strlen($requete) - 1);
                    $requete .= ") values (";
                    foreach ($champs as $key => $value) {
                        $requete .= ":$key,";
                    }
                    // (enlève la dernière virgule)
                    $requete = substr($requete, 0, strlen($requete) - 1);
                    $requete .= ");";
                    return $this->conn->execute($requete, $champs);
            }
        } else {
            return null;
        }
    }

    /**
     * ajout d'une ligne dans Commande puis CommandeDocument
     * @param string $table nom de la table CommandeDocument
     * @param array $champs nom et valeur de chaque champs de la ligne
     * @return true si l'ajout a fonctionné
     */
    public function insertOneCommandeDocument($table, $champs) {
        // insertion commande
        $param = array(
            "id" => $champs["Id"],
            "dateCommande" => $champs["DateCommande"],
            "montant" => $champs["Montant"]
        );
        // construction de la requête
        $requete = "insert into commande (";
        foreach ($param as $key => $value) {
            $requete .= "$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete) - 1);
        $requete .= ") values (";
        foreach ($param as $key => $value) {
            $requete .= ":$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete) - 1);
        $requete .= ");";
        $result1 = $this->conn->execute($requete, $param);

        // insertion commandedocument
        $param2 = array(
            "id" => $champs["Id"],
            "nbExemplaire" => $champs["NbExemplaire"],
            "idLivreDvd" => $champs["IdLivreDvd"],
            "idSuivi" => $champs["IdSuivi"]
        );
        // construction de la requête
        $requete2 = "insert into $table (";
        foreach ($param2 as $key => $value) {
            $requete2 .= "$key,";
        }
        // (enlève la dernière virgule)
        $requete2 = substr($requete2, 0, strlen($requete2) - 1);
        $requete2 .= ") values (";
        foreach ($param2 as $key => $value) {
            $requete2 .= ":$key,";
        }
        // (enlève la dernière virgule)
        $requete2 = substr($requete2, 0, strlen($requete2) - 1);
        $requete2 .= ");";
        $result2 = $this->conn->execute($requete2, $param2);
        //retourne les deux
        return $result1 && $result2;
    }
    
    /**
     * ajout d'une ligne dans Commande puis Abonnement
     * @param string $table nom de la table Abonnement
     * @param array $champs nom et valeur de chaque champs de la ligne
     * @return true si l'ajout a fonctionné
     */
    public function insertOneAbonnement($table, $champs) {
        // insertion commande
        $param = array(
            "id" => $champs["Id"],
            "dateCommande" => $champs["DateCommande"],
            "montant" => $champs["Montant"]
        );
        // construction de la requête
        $requete = "insert into commande (";
        foreach ($param as $key => $value) {
            $requete .= "$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete) - 1);
        $requete .= ") values (";
        foreach ($param as $key => $value) {
            $requete .= ":$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete) - 1);
        $requete .= ");";
        $result1 = $this->conn->execute($requete, $param);

        // insertion commandedocument
        $param2 = array(
            "id" => $champs["Id"],
            "dateFinAbonnement" => $champs["DateFinAbonnement"],
            "idRevue" => $champs["IdRevue"]
        );
        // construction de la requête
        $requete2 = "insert into $table (";
        foreach ($param2 as $key => $value) {
            $requete2 .= "$key,";
        }
        // (enlève la dernière virgule)
        $requete2 = substr($requete2, 0, strlen($requete2) - 1);
        $requete2 .= ") values (";
        foreach ($param2 as $key => $value) {
            $requete2 .= ":$key,";
        }
        // (enlève la dernière virgule)
        $requete2 = substr($requete2, 0, strlen($requete2) - 1);
        $requete2 .= ");";
        $result2 = $this->conn->execute($requete2, $param2);
        //retourne les deux
        return $result1 && $result2;
    }

    /**
     * modification d'une ligne dans une table
     * @param string $table nom de la table
     * @param string $id id de la ligne à modifier
     * @param array $champs nom et valeur de chaque champs de la ligne
     * @return true si la modification a fonctionné
     */
    public function updateOne($table, $id, $champs) {
        if ($this->conn != null && $champs != null) {
            switch ($table) {
                case "commandedocument" :
                    return $this->updateOneCommandeDocument($table, $id, $champs);
                default :
                    // construction de la requête
                    $requete = "update $table set ";
                    foreach ($champs as $key => $value) {
                        $requete .= "$key=:$key,";
                    }
                    // (enlève la dernière virgule)
                    $requete = substr($requete, 0, strlen($requete) - 1);
                    $champs["id"] = $id;
                    $requete .= " where id=:id;";
                    return $this->conn->execute($requete, $champs);
            }
        } else {
            return null;
        }
    }

    /**
     * modification d'une ligne dans CommandeDocument
     * @param string $table nom de la table CommandeDocument
     * @param string $id id de la ligne à modifier
     * @param array $champs nom et valeur de chaque champs de la ligne
     * @return true si la modification a fonctionné
     */
    public function updateOneCommandeDocument($table, $id, $champs) {
        $params = array(
            "id" => $champs["Id"],
            "nbExemplaire" => $champs["NbExemplaire"],
            "idLivreDvd" => $champs["IdLivreDvd"],
            "idSuivi" => $champs["IdSuivi"]
        );
        $requete = "update $table set ";
        foreach ($params as $key => $value) {
            $requete .= "$key=:$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete) - 1);
        $requete .= " where $table.id=:id;";
        return $this->conn->execute($requete, $params);
    }

}
