<?php
/**
 * Сохранение временного файла
 * 
 * @param string $content Содержимое файла
 * @return string Путь к временному файлу
 */
function saveTemporaryFile($content) {
    // $tmpFile = tempnam(sys_get_temp_dir(), 'upload_');
    $tmpFile = tempnam("D:\\OSPanel\\userdata\\temp\\upload", 'upload_');
    file_put_contents($tmpFile, $content);
    return $tmpFile;
}

/**
 * Парсинг multipart/form-data для PUT запросов
 * 
 * @param string $rawInput Сырые данные запроса
 * @param string $boundary Граница между данными
 * @return array Распарсенные данные и файлы
 */
function parseFormData($rawInput, $boundary) { 
    $result = [
        'params' => [],
        'files' => []
    ];
    
    // Разбиваем на части по границе
    $parts = array_slice(explode('--' . $boundary, $rawInput), 1);
    
    foreach ($parts as $part) {
        // Пропускаем завершающую границу
        if (strpos($part, '--') === 0) {
            continue;
        }
        
        // Разделяем заголовки и содержимое
        list($rawHeaders, $content) = explode("\r\n\r\n", $part, 2);
        
        // Парсинг заголовков
        $headers = [];
        foreach (explode("\r\n", $rawHeaders) as $header) {
            if (strpos($header, ':') !== false) {
                list($name, $value) = explode(':', $header, 2);
                $headers[strtolower(trim($name))] = trim($value);
            }
        }
        
        // Ищем название поля
        $contentDisposition = isset($headers['content-disposition']) ? $headers['content-disposition'] : '';
        preg_match('/name=\"([^\"]+)\"/', $contentDisposition, $matches);
        
        if (isset($matches[1])) {
            $fieldName = $matches[1];
            
            // Ищем имя файла, если это файл
            $isFile = preg_match('/filename=\"([^\"]+)\"/', $contentDisposition, $filenameMatches);
            
            if ($isFile && isset($filenameMatches[1])) {
                // Обработка файла
                $filename = $filenameMatches[1];
                $content = substr($content, 0, -2); // Удаляем \r\n в конце
                
                $contentType = isset($headers['content-type']) ? $headers['content-type'] : 'application/octet-stream';
                
                $result['files'][$fieldName] = [
                    'name' => $filename,
                    'type' => $contentType,
                    'tmp_name' => saveTemporaryFile($content),
                    'error' => 0,
                    'size' => strlen($content)
                ];
            } else {
                // Обработка обычных полей
                $content = substr($content, 0, -2); // Удаляем \r\n в конце
                $result['params'][$fieldName] = $content;
            }
        }
    }
    
    return $result;
}

/**
 * Вспомогательные функции
 */

/**
 * Отправка успешного ответа
 */
function sendSuccessResponse($data = [], $message = 'OK', $code = 200)
{
    http_response_code($code);
    echo json_encode([
        'status' => 'success',
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

/**
 * Отправка ответа с ошибкой
 */
function sendErrorResponse($message = 'Error', $code = 400)
{
    http_response_code($code);
    echo json_encode([
        'status' => 'error',
        'message' => $message
    ]);
    exit;
}

/**
 * Валидация данных
 */
function validateData($data, $rules)
{
    $errors = [];
    
    foreach ($rules as $field => $fieldRules) {
        $fieldRules = explode('|', $fieldRules);
        
        foreach ($fieldRules as $rule) {
            if ($rule === 'required' && (!isset($data[$field]) || empty($data[$field]))) {
                $errors[$field][] = "Поле $field обязательно для заполнения";
                continue;
            }
            
            if (isset($data[$field]) && !empty($data[$field])) {
                if (strpos($rule, 'min:') === 0) {
                    $min = (int) substr($rule, 4);
                    if (strlen($data[$field]) < $min) {
                        $errors[$field][] = "Поле $field должно содержать минимум $min символов";
                    }
                }
                
                if (strpos($rule, 'max:') === 0) {
                    $max = (int) substr($rule, 4);
                    if (strlen($data[$field]) > $max) {
                        $errors[$field][] = "Поле $field должно содержать максимум $max символов";
                    }
                }
                
                if ($rule === 'email' && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = "Поле $field должно быть корректным email адресом";
                }
                
                if ($rule === 'numeric' && !is_numeric($data[$field])) {
                    $errors[$field][] = "Поле $field должно быть числом";
                }
                
                if ($rule === 'phone') {
                    $phone = preg_replace('/[^0-9]/', '', $data[$field]);
                    if (strlen($phone) < 10 || strlen($phone) > 15) {
                        $errors[$field][] = "Поле $field должно быть корректным номером телефона";
                    }
                }
                
                if ($rule === 'date') {
                    $date = strtotime($data[$field]);
                    if ($date === false) {
                        $errors[$field][] = "Поле $field должно быть корректной датой";
                    }
                }
                
                if (strpos($rule, 'date_after:') === 0) {
                    $afterField = substr($rule, 11);
                    if (isset($data[$afterField])) {
                        $date = strtotime($data[$field]);
                        $afterDate = strtotime($data[$afterField]);
                        if ($date <= $afterDate) {
                            $errors[$field][] = "Дата в поле $field должна быть позже даты в поле $afterField";
                        }
                    }
                }
                
                if ($rule === 'future_date') {
                    $date = strtotime($data[$field]);
                    if ($date < strtotime('today')) {
                        $errors[$field][] = "Дата в поле $field должна быть не раньше текущей даты";
                    }
                }
            }
        }
    }
    
    return empty($errors) ? true : $errors;
}

/**
 * Создание JWT токена
 */
function createJWT($payload)
{
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode($payload);
    
    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}

/**
 * Проверка JWT токена
 */
function verifyJWT($token)
{
    $tokenParts = explode('.', $token);
    if (count($tokenParts) != 3) {
        return false;
    }
    
    $header = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[0]));
    $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1]));
    $signatureProvided = $tokenParts[2];
    
    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
    if ($base64UrlSignature !== $signatureProvided) {
        return false;
    }
    
    $payload = json_decode($payload, true);
    
    // Проверка срока действия токена
    if (isset($payload['exp']) && $payload['exp'] < time()) {
        return false;
    }
    
    return $payload;
}

/**
 * Middleware для проверки авторизации
 */
function authMiddleware($callback)
{
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        sendErrorResponse('Требуется авторизация', 401);
    }
    
    $token = str_replace('Bearer ', '', $headers['Authorization']);
    $payload = verifyJWT($token);
    
    if (!$payload) {
        sendErrorResponse('Недействительный токен', 401);
    }
    
    // Получение данных пользователя
    $db = Database::getInstance();
    $user = $db->fetch("SELECT * FROM users WHERE id_user = ?", [$payload['id']]);
    
    if (!$user) {
        sendErrorResponse('Пользователь не найден', 401);
    }
    
    // Удаление пароля из данных пользователя
    unset($user['password']);
    
    return $callback($user);
}

/**
 * Middleware для проверки прав администратора
 */
function adminMiddleware($callback)
{
    return authMiddleware(function($user) use ($callback) {
        if (false) {
            sendErrorResponse('Доступ запрещен', 403);
        }
        return $callback($user);
    });
}

/**
 * Обработка загруженного файла
 */
function handleUploadedFile($files, $fieldName, $uploadDir)
{
    if (!isset($files[$fieldName])) {
        return null;
    }
    
    $file = $files[$fieldName];
    
    // Проверка ошибок загрузки
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    // Проверка размера файла
    if ($file['size'] > MAX_FILE_SIZE) {
        return null;
    }
    
    // Проверка типа файла
    if (!in_array($file['type'], ALLOWED_FILE_TYPES)) {
        return null;
    }
    
    // Проверка реального MIME-типа файла
    // $finfo = finfo_open(FILEINFO_MIME_TYPE);
    // $mimeType = finfo_file($finfo, $file['tmp_name']);
    // finfo_close($finfo);
    
    // if (!in_array($mimeType, ALLOWED_FILE_TYPES)) {
    //     return null;
    // }
    
    // Создание директории, если не существует
    $targetDir = UPLOAD_DIR . trim($uploadDir, '/') . '/';
    if (!file_exists($targetDir)) {
        if (!mkdir($targetDir, 0777, true)) {
            return null;
        }
    }
    
    // Генерация безопасного имени файла
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
        return null;
    }
    
    $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
    $targetPath = $targetDir . $fileName;
    
    // Проверка, что файл действительно загружен через HTTP POST
    // if (!is_uploaded_file($file['tmp_name'])) {
    //     return null;
    // }
    // die($file['tmp_name']);
    // Перемещение файла
    try { 
        $res = move_uploaded_file($file['tmp_name'], $targetPath);
        die(move_uploaded_file($file['tmp_name'], $targetPath));
    } catch (\Throwable $th) {
        die($th);
    }

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // Установка правильных прав доступа
        chmod($targetPath, 0644);
        return $targetPath;
    }
    
    return null;
} 