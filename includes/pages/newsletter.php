<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit; }
$email = trim($_POST['email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { echo json_encode(['success'=>false,'message'=>'Invalid email address.']); exit; }
$exists = dbFetchOne("SELECT id FROM newsletter_subscribers WHERE email=?", [$email]);
if ($exists) { echo json_encode(['success'=>false,'message'=>'You are already subscribed!']); exit; }
dbExecute("INSERT INTO newsletter_subscribers (email) VALUES (?)", [$email]);
echo json_encode(['success'=>true,'message'=>'Thank you for subscribing!']);
