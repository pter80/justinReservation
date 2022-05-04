<?php
require_once "parametres.php";

function cnx_base() {
	try {
		$conn = new PDO (
			"mysql:host=" . DB_MYSQL_HOTE .
			';dbname=' . DB_MYSQL_NOM .
			';charset=utf8',
			DB_MYSQL_UTIL,
			DB_MYSQL_PASS,
			array(
				PDO::ATTR_EMULATE_PREPARES => true,
				PDO::ATTR_PERSISTENT => true,
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
			)
		);
		return $conn;
	} catch(PDOException $e) {
		return false;
	}
}

function type_var($valeur, $type = null) {
	if(is_null($type)) {
		switch (true) {
			case is_int($valeur):
				$type = PDO::PARAM_INT;
			break;
			case is_bool($valeur):
				$type = PDO::PARAM_BOOL;
			break;
			case is_null($valeur):
				$type = PDO::PARAM_NULL;
			break;
			default:
				$type = PDO::PARAM_STR;
		}
	}
}

function exec_req($sql, $donnees, &$conn) {
	$resultat = true;
	$req = $conn->prepare($sql);
	if (!$donnees) {
		$resultat = $req->execute();
	}
	else {
		$resultat = $req->execute($donnees);
	}

	if ($resultat) {
		return $req;
	}
	else {
		return false;
	}
}

function req_maj($sql, $donnees) {
	$conn = cnx_base();
	if ($conn == false) {
		return false;
	}
	else {
		try {
			return exec_req($sql, $donnees, $conn);
		} catch (PDOException $e) {
			echo "Requête : \n" .$sql . "\n";
			var_dump($sql);
			var_dump($donnees);
			echo $e->getMessage();
			return false;
		}
	}
}

function req_maj_transaction(...$requetes) {
	$conn = cnx_base();

	if ($conn == false) {
		return false;
	}
	else {
		try {
			$ok = true;
			$conn->beginTransaction();
			foreach ($requetes as $requete) {
				if (exec_req($requete[0], $requete[1], $conn) === false) {
					$ok = false;
				}
			}
			$conn->commit();
			return $ok;
		} catch (PDOException $e) {
			$conn->rollback();
			echo "Requête : \n";
			var_dump($requetes);
			echo $e->getMessage();
			return false;
		}
	}
}

function req_select($sql, $param, $nbLignes = 0) {
	$conn = cnx_base();
	if ($conn == false) {
		return false;
	}
	else {
		try {
			$req = exec_req($sql, $param, $conn);
			if ($req != false) {
				$donnees = $req->fetchAll(PDO::FETCH_OBJ);
				if (count($donnees) != $nbLignes) {
					return $donnees;
				}
				else {
					return false;
				}
			}
			else {
				return false;
			}
		} catch (PDOException $e) {
			echo "Requêtes : \n";
			var_dump($sql);
			var_dump($param);
			echo $e->getMessage();
			return false;
		}
	}
}

function validerDate($date, $format = 'd/m/Y H:i') {
	$d = DateTime::createFromFormat($format, $date);
	return $d && $d->format($format) == $date;
}
