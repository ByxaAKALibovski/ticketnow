<?php
/**
 * Контроллер для категорий
 */
class TypeActivityController
{
    /**
     * Получение всех категорий
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function getAll($data)
    {
        $db = Database::getInstance();
        $categories = $db->fetchAll("SELECT * FROM type_activity ORDER BY id_type_activity ASC");
        
        sendSuccessResponse([
            'type_activity' => $categories
        ], 'Список типов мероприятий получен');
    }
    
    /**
     * Создание новой категории (только для администраторов)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function create($data)
    {
        adminMiddleware(function($user) use ($data) {
            // Проверка данных
            $validation = validateData($data["body"], [
                'title' => 'required|min:3'
            ]);
            
            if ($validation !== true) {
                sendErrorResponse($validation, 400);
            }
            
            // Подготовка данных для создания
            $typeActivityData = [
                'title' => $data["body"]['title']
            ];
            
            // Обработка загруженного изображения, если оно есть
            // if (isset($data['files']) && isset($data['files']['image'])) {
            //     $uploadDir = 'uploads/category/';
            //     $imagePath = handleUploadedFile($data['files'], 'image', $uploadDir);
                
            //     if ($imagePath) {
            //         $typeActivityData['image_link'] = str_replace(UPLOAD_DIR, '', $imagePath);
            //     }
            // }
            
            // Создание категории
            $db = Database::getInstance();
            $typeActivityId = $db->insert('type_activity', $typeActivityData);
            
            if (!$typeActivityId) {
                sendErrorResponse('Ошибка при создании типа мероприятий', 500);
            }
            
            // Получение данных созданной категории
            $typeActivity = $db->fetch("SELECT * FROM type_activity WHERE id_type_activity = ?", [$typeActivityId]);
            
            sendSuccessResponse([
                'type_activity' => $typeActivity
            ], 'Тип мероприятия успешно создан');
        });
    }
    
    /**
     * Обновление категории (только для администраторов)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function update($data)
    {
        adminMiddleware(function($user) use ($data) {
            // Проверяем, передан ли идентификатор категории
            if (!isset($data['route_params']['id'])) {
                sendErrorResponse('Идентификатор типа мероприятия не указан', 400);
            }
            
            $typeActivityId = $data['route_params']['id'];
            $db = Database::getInstance();
            
            // Проверка существования категории
            $typeActivity = $db->fetch("SELECT * FROM type_activity WHERE id_type_activity = ?", [$typeActivityId]);
            if (!$typeActivity) {
                sendErrorResponse('Тип мероприятия не найдена', 404);
            }
            
            // Проверка данных
            $validation = validateData($data["body"], [
                'title' => 'required|min:3'
            ]);
            
            if ($validation !== true) {
                sendErrorResponse($validation, 400);
            }
            
            // Подготовка данных для обновления
            $typeActivityData = [
                'title' => $data["body"]['title']
            ];
            
            // Обработка загруженного изображения, если оно есть
            // if (isset($data['files']) && isset($data['files']['image'])) {
            //     $uploadDir = 'uploads/category/';
            //     $imagePath = handleUploadedFile($data['files'], 'image', $uploadDir);
                
            //     if ($imagePath) {
            //         $categoryData['image_link'] = str_replace(UPLOAD_DIR, '', $imagePath);
                    
            //         // Удаление старого изображения
            //         if ($category['image_link']) {
            //             @unlink(UPLOAD_DIR . $category['image_link']);
            //         }
            //     }
            // }
            
            // Обновление типа мероприятия
            if (!$db->update('type_activity', $typeActivityData, 'id_type_activity = ?', [$typeActivityId])) {
                sendErrorResponse('Ошибка при обновлении типа мероприятия', 500);
            }
            
            // Получение обновленных данных категории
            $updatedTypeActivity = $db->fetch("SELECT * FROM type_activity WHERE id_type_activity = ?", [$typeActivityId]);
            
            sendSuccessResponse([
                'type_activity' => $updatedTypeActivity
            ], 'Тип мероприятия успешно обновлен');
        });
    }
    
    /**
     * Удаление категории (только для администраторов)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function delete($data)
    {
        adminMiddleware(function($user) use ($data) {
            // Проверяем, передан ли идентификатор категории
            if (!isset($data['route_params']['id'])) {
                sendErrorResponse('Идентификатор типа мероприятия не указан', 400);
            }
            
            $typeActivityId = $data['route_params']['id'];
            $db = Database::getInstance();
            
            // Проверка существования категории
            $typeActivity = $db->fetch("SELECT * FROM type_activity WHERE id_type_activity = ?", [$typeActivityId]);
            if (!$typeActivity) {
                sendErrorResponse('Тип мероприятия не найден', 404);
            }
            
            // Удаление изображения категории
            // if ($typeActivity['image_link']) {
            //     @unlink(UPLOAD_DIR . $category['image_link']);
            // }
            
            // Удаление категории
            if (!$db->delete('type_activity', 'id_type_activity = ?', [$typeActivityId])) {
                sendErrorResponse('Ошибка при удалении типа мероприятия', 500);
            }
            
            sendSuccessResponse([], 'Тип мероприятия успешно удален');
        });
    }
} 