<?php
// public/update_tache_status.php

header('Content-Type: application/json; charset=utf-8');

try {
    $payload = json_decode(file_get_contents('php://input'), true);
    if (!isset($payload['id'], $payload['statut'])) {
        throw new \Exception('Paramètres manquants');
    }

    $id     = (int) $payload['id'];
    $status = $payload['statut'];

    $allowed = ['En cours','Terminée','Annulée'];
    if (!in_array($status, $allowed, true)) {
        throw new \Exception('Statut invalide');
    }

    // adjust these to match your DATABASE_URL
    $pdo = new \PDO(
        'mysql:host=127.0.0.1;dbname=event_planner;charset=utf8mb4',
        'root',
        '',
        [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]
    );

    $stmt = $pdo->prepare('UPDATE tache SET statut = :statut WHERE id = :id');
    $stmt->execute([':statut' => $status, ':id' => $id]);

    echo json_encode(['success' => true]);
} catch (\Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}
