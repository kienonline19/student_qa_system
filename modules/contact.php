<?php
/**
 * Contact Module - Handles contact form submissions
 */

require_once 'config/database.php';

/**
 * Save contact message to database
 * @param string $name Sender name
 * @param string $email Sender email
 * @param string $subject Message subject
 * @param string $message Message content
 * @return int|false New message ID or false on failure
 */
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

/**
 * Get all contact messages
 * @return array Array of contact messages
 */
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

/**
 * Get contact message by ID
 * @param int $messageId Message ID
 * @return array|false Message data or false if not found
 */
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

/**
 * Delete contact message
 * @param int $messageId Message ID
 * @return bool Success status
 */
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

/**
 * Send email notification (basic implementation)
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $message Email message
 * @param string $from Sender email
 * @return bool Success status
 */
function sendEmailNotification($to, $subject, $message, $from) {
    $headers = "From: " . $from . "\r\n";
    $headers .= "Reply-To: " . $from . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    // In a real application, you would use a proper email service
    // For now, we'll just log the email attempt
    error_log("Email would be sent to: $to, Subject: $subject");
    
    // Uncomment the line below if you want to actually send emails
    // return mail($to, $subject, $message, $headers);
    
    return true; // Return true for demonstration purposes
}
?>