<?php
// server/submitForm.php

declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

use App\MailHandler;

try {
    // Load DB config
    require_once __DIR__ . '/config.php';
    require_once __DIR__ . '/variable.php';
    require_once __DIR__ . '/mailHandler.php';

    // Only allow POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
        exit;
    }

    // Get form inputs safely
    $name = isset($_POST['name']) ? trim((string)$_POST['name']) : '';
    $email = isset($_POST['email']) ? trim((string)$_POST['email']) : '';
    $favorite_star = isset($_POST['favorite_star']) ? trim((string)$_POST['favorite_star']) : '';
    $message = isset($_POST['message']) ? trim((string)$_POST['message']) : null;
    $participate = isset($_POST['participate']) ? trim((string)$_POST['participate']) : '';

    // Handle plotlines[] (optional)
    $plotlines = [];
    if (isset($_POST['plotline']) && is_array($_POST['plotline'])) {
        $plotlines = array_map('trim', $_POST['plotline']);
    } else {
        foreach ($_POST as $k => $v) {
            if (strpos($k, 'plotline') !== false && is_array($v)) {
                $plotlines = array_merge($plotlines, $v);
            }
        }
    }

    // --- VALIDATION SECTION ---
    $errors = [];

    // Name validation
    if ($name === '') {
        $errors['name'] = 'Name is required.';
    } elseif (mb_strlen($name) > 150) {
        $errors['name'] = 'Name is too long.';
    }

    // Email validation
    if ($email === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email address.';
    } elseif (mb_strlen($email) > 255) {
        $errors['email'] = 'Email is too long.';
    }

    // Skip validation for favorite_star, participate, and plotline
    // Just sanitize plotlines (optional safety)
    $allowed = ['amnesia', 'prince', 'bakery', 'big_city_home'];
    if (is_array($plotlines)) {
        $plotlines = array_values(array_filter($plotlines, function($p) use ($allowed) {
            return in_array($p, $allowed, true);
        }));
    } else {
        $plotlines = [];
    }

    // Message length validation
    if ($message !== null && mb_strlen($message) > 2000) {
        $errors['message'] = 'Message is too long.';
    }

    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Validation failed.',
            'errors' => $errors
        ]);
        exit;
    }

    // Determine IP address
    $ip = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    if ($ip === '') { $ip = '0.0.0.0'; }

    $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 1000) : '';

    // Prepare data for DB
    $plotlines_json = json_encode(array_values($plotlines), JSON_UNESCAPED_UNICODE);

    // Insert record using PDO
    $pdo = new PDO($dbDsn, $dbUser, $dbPass, $dbOptions);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "INSERT INTO cozy_crew_submissions
        (name, email, favorite_star, participate, plotlines, message, ip_address, user_agent, created_at)
        VALUES (:name, :email, :favorite_star, :participate, :plotlines, :message, :ip_address, :user_agent, NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':favorite_star', $favorite_star, $favorite_star === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':participate', $participate, $participate === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':plotlines', $plotlines_json, $plotlines_json === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':message', $message, $message === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->bindValue(':ip_address', $ip, PDO::PARAM_STR);
    $stmt->bindValue(':user_agent', $userAgent, PDO::PARAM_STR);

    $stmt->execute();

    $insertId = (int)$pdo->lastInsertId();

    // Prepare plotline text (optional)
    $plotline = "";
    if (count($plotlines) > 0) {
        $plotlineTexts = [];
        foreach ($plotlines as $pl) {
            if (isset($plotlineList[$pl])) {
                $plotlineTexts[] = $plotlineList[$pl];
            }
        }
        $plotline = implode(', ', $plotlineTexts);
    }

    // Prepare data for mail (if needed)
    $data = [
        'name' => $name,
        'email' => $email,
        'plotline' => $plotline,
        'favorite_star' => $favorite_star,
        'participate' => $participateOptions[$participate] ?? '',
        'message' => $message,
    ];

    $mailHandler = new MailHandler($emailHost, $emailPort, $emailUsername, $emailPassword, $emailFrom, $emailTo);
    $responseAdmin = $mailHandler->sendFanSubmission($data);
    $responseuser = $mailHandler->sendConfirmationToUser($email, $name);

    echo json_encode([
        'success' => true,
        'message' => 'Thank you! Your response has been saved.',
        'id' => $insertId,
        'email_status' => ['admin' => $responseAdmin, 'user' => $responseuser]
    ]);
    exit;

} catch (PDOException $ex) {
    error_log((new DateTime())->format(DateTime::ATOM) . " - PDOException: " . $ex->getMessage() . PHP_EOL, 3, __DIR__ . '/error.log');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error. Please try again later.']);
    exit;

} catch (Throwable $ex) {
    error_log((new DateTime())->format(DateTime::ATOM) . " - Exception: " . $ex->getMessage() . PHP_EOL, 3, __DIR__ . '/error.log');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error. Please try again later.']);
    exit;
}
