<?php
  require_once 'parametres.php';

  class Database {
    private $host = DB_MYSQL_HOTE;
    private $user = DB_MYSQL_UTIL;
    private $pass = DB_MYSQL_PASS;
    private $dbname = DB_MYSQL_NOM;
    private $dbh;
    private $error;
    private $stmt;

    public function __construct() {
      $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname;

      $options = array(
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
      );

      try {
        $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
      }
      catch (PDOException $e) {
        $this->error = $e->getMessage();
      }
    }

    public function query($query) {
      $this->stmt = $this->dbh->prepare($query);
    }

    public function bind($param, $value, $type = null) {
      if (is_null($type)) {
        switch (true) {
          case is_int($value):
            $type = PDO::PARAM_INT;
          break;

          case is_bool($value):
            $type = PDO::PARAM_BOOL;
          break;

          case is_null($value):
            $type = PDO::PARAM_NULL;
          break;

          default:
            $type = PDO::PARAM_STR;
        }
      }
      $this->stmt->bindValue($param, $value, $type);
    }

    public function execute() {
      return $this->stmt->execute();
    }

    public function resultset() {
      $this->execute();
      return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function single() {
      $this->execute();
      return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function rowCount() {
      return $this->stmt->rowCount();
    }

    public function lastInsertId() {
      return $this->dbh->lastInsertId();
    }

    public function beginTransaction() {
      return $this->dbh->beginTransaction();
    }

    public function endTransaction() {
      return $this->dbh->commit();
    }

    public function cancelTransaction() {
      return $this->dbh->rollBack();
    }

    public function debugDumpParams() {
      return $this->dbh->debugDumpParams();
    }

    public function close() {
      $this->dbh = null;
    }
  }

  class Session {
    private $db;

    public function __construct() {
      $this->db = new Database;

      session_set_save_handler(
        array($this, "_open"),
        array($this, "_close"),
        array($this, "_read"),
        array($this, "_write"),
        array($this, "_destroy"),
        array($this, "_gc")
      );

      date_default_timezone_set("Europe/Paris");

      ini_set('session.cookie_secure', false);
      ini_set('session.cookie_httponly', true);
      ini_set('session.cookie_samesite', 'Lax');

      session_set_cookie_params(0, '/', $_SERVER['SERVER_NAME']);
      session_start();
      setcookie(session_name(), session_id(), ['path' => '/', 'domain' => $_SERVER['SERVER_NAME'], 'secure' => false, 'httponly' => true, 'samesite' => 'Lax']);
    }

    public function _open() {
      if ($this->db) {
        return true;
      }

      return false;
    }

    public function _close() {
      if ($this->db->close()) {
        return true;
      }

      return false;
    }

    public function _read($id) {
      $this->db->query('SELECT data from sessions WHERE id = :id AND browser = :browser');

      $this->db->bind(':id', $id);
      $this->db->bind(':browser', sha1($_SERVER['HTTP_USER_AGENT']));

      if ($this->db->execute()) {
        $row = $this->db->single();

        if (!is_null($row['data'])) {
          return $row['data'];
        }
        else {
          return '';
        }
      }
      else {
        return '';
      }
    }

    public function _write($id, $data) {
      $access = time();

      $this->db->query('SELECT data FROM sessions WHERE id = :id AND browser = :browser');

      $this->db->bind(':id', $id);
      $this->db->bind(':browser', sha1($_SERVER['HTTP_USER_AGENT']));

      if ($this->db->execute()) {
        $row = $this->db->single();

        if (!is_null($row['data'])) {
          $this->db->query('UPDATE sessions SET access = :access, data = :data WHERE id = :id');
        }
        else {
          $this->db->query('INSERT INTO sessions VALUES (:id, :access, :data, :browser)');
          $this->db->bind(':browser', sha1($_SERVER['HTTP_USER_AGENT']));
        }
      }
      else {
        $this->db->query('INSERT INTO sessions VALUES (:id, :access, :data, :browser)');
        $this->db->bind(':browser', sha1($_SERVER['HTTP_USER_AGENT']));
      }
      $this->db->bind(':id', $id);
      $this->db->bind(':access', $access);
      $this->db->bind(':data', $data);

      if ($this->db->execute()) {
        return true;
      }

      return false;
    }

    public function _destroy($id) {
      $this->db->query('DELETE FROM sessions WHERE id = :id');

      $this->db->bind(':id', $id);

      if ($this->db->execute()) {
        return true;
      }

      return false;
    }

    public function _gc($max) {
      $old = time() -$max;

      $this->db->query('DELETE FROM sessions WHERE access < :old');

      $this->db->bind(':old', $old);

      if ($this->db->execute()) {
        return true;
      }

      return false;
    }
  }

  function verif_session() {
    if (!isset($_SESSION['nom'])) {
      kill_session();
      return false;
    }

    return true;
  }

  function kill_session() {
    $_SESSION = array();
    if (ini_get('session.use_cookies')) {
      $params = session_get_cookie_params();
      setcookie(
        session_name(),
        '',
        [
          'expires' => time() - 42000,
          'path' => $params['path'],
          'domain' => $params['domain'],
          'secure' => $params['secure'],
          'httponly' => $params['httponly'],
          'samesite' => 'Lax'
        ]
      );
    }
    session_destroy();
  }
?>
