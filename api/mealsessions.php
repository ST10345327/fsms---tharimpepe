<?php
function handleMealSessions($method, $id) {
    $user = requireAuth();
    $db = getDBConnection();
    $model = new MealSession($db);

    switch ($method) {
        case 'GET':
            if ($id) {
                $stmt = $db->prepare("SELECT * FROM MealSession WHERE MealSessionID = ?");
                $stmt->execute([(int)$id]);
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$data) {
                    apiJsonResponse(false, 'Meal session not found', null, 404);
                }
                apiJsonResponse(true, 'Meal session retrieved', $data);
            } else {
                $stmt = $db->query("SELECT * FROM MealSession ORDER BY SessionDate DESC");
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                apiJsonResponse(true, 'Meal sessions retrieved', $data);
            }
            break;

        case 'POST':
            $input = getJsonInput();
            validateRequired($input, ['session_date', 'session_type']);

            $stmt = $db->prepare("INSERT INTO MealSession (SessionDate, SessionType, Location) VALUES (?, ?, ?)");
            $stmt->execute([
                $input['session_date'],
                $input['session_type'],
                $input['location'] ?? null
            ]);
            $sid = $db->lastInsertId();
            $stmt = $db->prepare("SELECT * FROM MealSession WHERE MealSessionID = ?");
            $stmt->execute([(int)$sid]);
            apiJsonResponse(true, 'Meal session created', $stmt->fetch(PDO::FETCH_ASSOC), 201);
            break;

        default:
            apiJsonResponse(false, 'Method not allowed', null, 405);
    }
}
