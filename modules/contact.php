<?php

require_once 'config/database.php';

function saveContactMessage($name, $email, $subject, $message) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            INSERT INTO contact_messages (name, email, subject, message) 
            VALUES (?, ?, ?, ?)
        ");
        $result = $stmt->execute([$name, $email, $subject, $message]);
        
        if ($result) {
            return $pdo->lastInsertId();
        }
        return false;
    } catch (Exception $e) {
        error_log("Error saving contact message: " . $e->getMessage());
        return false;
    }
}

function getAllContactMessages() {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
        return $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error getting contact messages: " . $e->getMessage());
        return [];
    }
}

function getContactMessageById($messageId) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE message_id = ?");
        $stmt->execute([$messageId]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error getting contact message: " . $e->getMessage());
        return false;
    }
}

function deleteContactMessage($messageId) {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE message_id = ?");
        return $stmt->execute([$messageId]);
    } catch (Exception $e) {
        error_log("Error deleting contact message: " . $e->getMessage());
        return false;
    }
}

function sendEmailNotification($to, $subject, $message, $from) {
    $headers = "From: " . $from . "\r\n";
    $headers .= "Reply-To: " . $from . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    error_log("Email would be sent to: $to, Subject: $subject");
    
    // Uncomment the line below if you want to actually send emails
    // return mail($to, $subject, $message, $headers);
    
    return true;
}
?>