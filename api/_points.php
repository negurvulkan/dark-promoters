<?php
declare(strict_types=1);

/**
 * Add points to a user and log the change.
 *
 * @param PDO $db Database connection
 * @param int $userId User ID
 * @param int $delta Number of points to add (positive)
 * @param string $reason Reason for the change
 */
function add_points(PDO $db, int $userId, int $delta, string $reason): void {
    $ownTx = !$db->inTransaction();
    if ($ownTx) {
        $db->beginTransaction();
    }
    $stmt = $db->prepare('UPDATE users SET points = points + :delta WHERE id = :uid');
    $stmt->execute([':delta' => $delta, ':uid' => $userId]);

    $stmt = $db->prepare('INSERT INTO point_log (user_id, delta, reason, created_at) VALUES (:uid, :delta, :reason, NOW())');
    $stmt->execute([':uid' => $userId, ':delta' => $delta, ':reason' => $reason]);
    if ($ownTx) {
        $db->commit();
    }
}

/**
 * Spend points for a user if sufficient balance exists.
 *
 * @param PDO $db Database connection
 * @param int $userId User ID
 * @param int $delta Number of points to spend (positive)
 * @param string $reason Reason for the deduction
 * @return bool True on success, false if insufficient points
 */
function spend_points(PDO $db, int $userId, int $delta, string $reason): bool {
    $ownTx = !$db->inTransaction();
    if ($ownTx) {
        $db->beginTransaction();
    }
    $stmt = $db->prepare('SELECT points FROM users WHERE id = :uid FOR UPDATE');
    $stmt->execute([':uid' => $userId]);
    $points = (int)$stmt->fetchColumn();
    if ($points < $delta) {
        if ($ownTx) {
            $db->rollBack();
        }
        return false;
    }
    $stmt = $db->prepare('UPDATE users SET points = points - :delta WHERE id = :uid');
    $stmt->execute([':delta' => $delta, ':uid' => $userId]);

    $stmt = $db->prepare('INSERT INTO point_log (user_id, delta, reason, created_at) VALUES (:uid, :negdelta, :reason, NOW())');
    $stmt->execute([':uid' => $userId, ':negdelta' => -$delta, ':reason' => $reason]);
    if ($ownTx) {
        $db->commit();
    }
    return true;
}
