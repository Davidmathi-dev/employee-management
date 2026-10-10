<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$method = $_SERVER['REQUEST_METHOD'];

$userId = authenticateToken($pdo);

if ($method === 'GET') {
    $search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    
    if ($limit <= 0 || $limit > 100) $limit = 10;
    if ($page <= 0) $page = 1;
    
    $offset = ($page - 1) * $limit;
    
    $sql = "SELECT id, name, email, department, designation FROM employees";
    $countSql = "SELECT COUNT(*) as total FROM employees";
    $params = [];
    
    if (!empty($search)) {
        $where = " WHERE name LIKE :search1 OR email LIKE :search2";
        $sql .= $where;
        $countSql .= $where;
        $params['search1'] = "%$search%";
        $params['search2'] = "%$search%";
    }
    
    $sql .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";
    
    try {
        $countStmt = $pdo->prepare($countSql);
        foreach ($params as $key => $val) {
            $countStmt->bindValue(":$key", $val);
        }
        $countStmt->execute();
        $total = $countStmt->fetch()['total'];
        
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(":$key", $val);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $data = $stmt->fetchAll();
        
        sendJsonResponse(true, null, [
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ]
        ]);
        
    } catch (PDOException $e) {
        sendJsonResponse(false, 'Internal Server Error while fetching employees.', [], 500);
    }
} elseif ($method === 'POST') {
    $inputJSON = file_get_contents('php://input');
    $data = json_decode($inputJSON, true);
    
    if (!$data) {
        sendJsonResponse(false, 'Invalid JSON input', [], 400);
    }
    
    $name = cleanInput($data['name'] ?? '');
    $email = cleanInput($data['email'] ?? '');
    $mobile = cleanInput($data['mobile'] ?? '');
    $department = cleanInput($data['department'] ?? '');
    $designation = cleanInput($data['designation'] ?? '');
    $salary = isset($data['salary']) ? (float)$data['salary'] : -1;
    $joining_date = cleanInput($data['joining_date'] ?? '');
    $status = cleanInput($data['status'] ?? '');
    
    // Validations
    $errors = [];
    
    if (empty($name)) $errors[] = 'Name is required.';
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required.';
    }

    if (empty($mobile) || !preg_match('/^[0-9]{10,15}$/', $mobile)) {
        $errors[] = 'Valid mobile number (10-15 digits) is required.';
    }
    
    if (empty($department)) $errors[] = 'Department is required.';
    if (empty($designation)) $errors[] = 'Designation is required.';
    
    if ($salary < 0) {
        $errors[] = 'Salary must be a non-negative number.';
    }
    
    if (empty($joining_date) || !strtotime($joining_date)) {
        $errors[] = 'Valid joining date is required.';
    }
    
    if (!in_array($status, ['Active', 'Inactive'])) {
        $errors[] = 'Status must be Active or Inactive.';
    }
    
    if (!empty($errors)) {
        sendJsonResponse(false, 'Validation failed.', ['errors' => $errors], 400);
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO employees (name, email, mobile, department, designation, salary, joining_date, status) VALUES (:name, :email, :mobile, :department, :designation, :salary, :joining_date, :status)");
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'mobile' => $mobile,
            'department' => $department,
            'designation' => $designation,
            'salary' => $salary,
            'joining_date' => $joining_date,
            'status' => $status
        ]);
        
        sendJsonResponse(true, 'Employee registered successfully.', [], 201);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            sendJsonResponse(false, 'Email already exists for another employee.', [], 409);
        } else {
            sendJsonResponse(false, 'Internal Server Error while registering employee.', [], 500);
        }
    }
} else {
    sendJsonResponse(false, 'Method Not Allowed', [], 405);
}
?>
