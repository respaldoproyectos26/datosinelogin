<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

/**
 * Cuenta los exámenes asignados al usuario y pendientes de presentar.
 */
if (!function_exists('exams_pending_count')) {
    function exams_pending_count(): int
    {
        if (!is_logged_in()) {
            return 0;
        }

        $pdo = db();
        $userId = auth()->id();
        
        // Obtener roles del usuario
        $roles = $pdo->prepare("SELECT role_id FROM role_user WHERE user_id = ?");
        $roles->execute([$userId]);
        $roleIds = array_column($roles->fetchAll(PDO::FETCH_ASSOC), 'role_id');

        // Query base
        $sql = "
            SELECT COUNT(DISTINCT e.id)
            FROM exams e
            JOIN exam_assignments a ON a.exam_id = e.id
            WHERE e.status = 'published'
            AND (
                    a.user_id = ?
                    OR (a.role_id IS NOT NULL " . 
                        ($roleIds ? " AND a.role_id IN (" . implode(',', array_fill(0, count($roleIds), '?')) . ")" : "") . 
                    ")
                )
            AND e.id NOT IN (
                    SELECT exam_id FROM attempts 
                    WHERE user_id = ? AND submitted_at IS NOT NULL
            )
        ";

        // Parámetros
        $params = [$userId];
        if ($roleIds) {
            $params = array_merge($params, $roleIds);
        }
        $params[] = $userId;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }
}