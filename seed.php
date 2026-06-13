<?php
// seed.php

// 1. Force error displaying to catch issues instantly
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 2. Import your verified OOP Models
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Chat.php';

echo "🤖 Initializing database seeding sequence...\n";

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("❌ Error: Could not connect to MariaDB database instance.\n");
}

$chat = new Chat($db);

// 3. Define an array of realistic social mock chats
$mockMessages = [
    "Hey there! Is this chat running entirely on a phone?",
    "Yes! It is built with custom OOP PHP and MariaDB inside Termux.",
    "Wow, that is fast. No React or heavy Node processes needed?",
    "Nope, just lightweight vanilla JavaScript long-polling every 2 seconds.",
    "Awesome. Let's load up a bunch of messages to test out the UI layout container rules.",
    "Testing out a longer message block string here to confirm that text wraps cleanly inside our application viewport panel context without bleeding out over margins.",
    "Everything looks clean! Ready to add authentic user session logic next.",
    "Database seeding completed successfully."
];

$successCount = 0;

foreach ($mockMessages as $index => $message) {
    // Alternate random sender IDs (User 1 and User 2) to simulate conversation flow
    $senderId = ($index % 2 === 0) ? 1 : 2;
    
    if ($chat->create($senderId, $message)) {
        $successCount++;
        echo "✅ Inserted message $successCount/" . count($mockMessages) . "\n";
    } else {
        echo "❌ Failed to insert message at array offset $index\n";
    }
}

echo "\n🚀 Done! Successfully injected $successCount fake social interactions into table 'messages'.\n";
