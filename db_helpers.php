<?php
/**
 * Database Helper Functions
 * 
 * This file provides compatibility functions to make the transition
 * from mysqli to PDO smoother. These functions mimic mysqli methods
 * but use PDO under the hood.
 */

/**
 * Execute a query and return the result
 * 
 * @param string $sql SQL query to execute
 * @param array $params Parameters to bind (optional)
 * @return PDOStatement|false The result object or false on failure
 */
function query($sql, $params = []) {
    global $conn;
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Query error: " . $e->getMessage());
        return false;
    }
}

/**
 * Fetch a single row as an associative array
 * 
 * @param PDOStatement $result The result from a query() call
 * @return array|null The row as an associative array or null if no more rows
 */
function fetch_assoc($result) {
    if (!$result) return null;
    $row = $result->fetch(PDO::FETCH_ASSOC);
    return $row ? $row : null;
}

/**
 * Fetch all rows as an associative array
 * 
 * @param PDOStatement $result The result from a query() call
 * @return array An array of all rows
 */
function fetch_all($result) {
    if (!$result) return [];
    return $result->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get the number of rows in a result
 * 
 * @param PDOStatement $result The result from a query() call
 * @return int The number of rows
 */
function num_rows($result) {
    if (!$result) return 0;
    return $result->rowCount();
}

/**
 * Get the last inserted ID
 * 
 * @return string The last inserted ID
 */
function last_insert_id() {
    global $conn;
    return $conn->lastInsertId();
}

/**
 * Escape a string for use in a query
 * 
 * @param string $string The string to escape
 * @return string The escaped string
 */
function escape_string($string) {
    return substr(htmlspecialchars(trim($string)), 0, 255);
}

/**
 * Begin a transaction
 */
function begin_transaction() {
    global $conn;
    $conn->beginTransaction();
}

/**
 * Commit a transaction
 */
function commit() {
    global $conn;
    $conn->commit();
}

/**
 * Rollback a transaction
 */
function rollback() {
    global $conn;
    $conn->rollBack();
}

/**
 * Get the error message from the last operation
 * 
 * @return string The error message
 */
function error() {
    global $conn;
    $error = $conn->errorInfo();
    return $error[2] ?? 'Unknown error';
}

/**
 * Prepare a query with placeholders and execute it
 * 
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters to bind
 * @return PDOStatement|false The result object or false on failure
 */
function prepared_query($sql, $params = []) {
    global $conn;
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Prepared query error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get a single value from the first row and column of a result
 * 
 * @param PDOStatement $result The result from a query() call
 * @return mixed The value or null if no rows
 */
function fetch_value($result) {
    if (!$result) return null;
    return $result->fetchColumn();
} 