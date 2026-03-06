<?php
// helpers/db.php
require_once __DIR__ . '/../config/config.php';

function db(): PDO {
  static $pdo = null;
  if ($pdo instanceof PDO) return $pdo;

  $opts = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_PERSISTENT         => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    PDO::ATTR_EMULATE_PREPARES => false,
  ];

    try {
      $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, $opts);
      return $pdo;
    } catch (PDOException $e) {
      error_log('[DB] '.$e->getMessage());
      throw $e; // deja que el caller (página o API) responda 500
    }
}