<?php
/**
 * Контроллер для категорий
 */
class NewsController
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
        $news = $db->fetchAll("SELECT * FROM news ORDER BY id_news ASC");
        
        sendSuccessResponse([
            'news' => $news
        ], 'Список новостей получен');
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
                'title' => 'required|min:3',
                'description' => 'required|min:5'
            ]);
            
            if ($validation !== true) {
                sendErrorResponse($validation, 400);
            }
            
            // Подготовка данных для создания
            $newsData = [
                'title' => $data["body"]['title'],
                'description' => $data["body"]['description']
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
            $newsId = $db->insert('news', $newsData);
            
            if (!$newsId) {
                sendErrorResponse('Ошибка при создании новости', 500);
            }
            
            // Получение данных созданной категории
            $news = $db->fetch("SELECT * FROM news WHERE id_news = ?", [$newsId]);
            
            sendSuccessResponse([
                'news' => $news
            ], 'Новость успешно создана');
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
                sendErrorResponse('Идентификатор новости не указан', 400);
            }
            
            $newsId = $data['route_params']['id'];
            $db = Database::getInstance();
            
            // Проверка существования категории
            $news = $db->fetch("SELECT * FROM news WHERE id_news = ?", [$newsId]);
            if (!$news) {
                sendErrorResponse('Новость не найдена', 404);
            }
            
            // Проверка данных
            $validation = validateData($data["body"], [
                'title' => 'required|min:3',
                'description' => 'required|min:5'
            ]);
            
            if ($validation !== true) {
                sendErrorResponse($validation, 400);
            }
            
            // Подготовка данных для обновления
            $newsData = [
                'title' => $data["body"]['title'],
                'description' => $data["body"]['description']
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
            if (!$db->update('news', $newsData, 'id_news = ?', [$newsId])) {
                sendErrorResponse('Ошибка при обновлении новости', 500);
            }
            
            // Получение обновленных данных категории
            $updatedNews = $db->fetch("SELECT * FROM news WHERE id_news = ?", [$newsId]);
            
            sendSuccessResponse([
                'news' => $updatedNews
            ], 'Новость успешно обновлена');
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
                sendErrorResponse('Идентификатор новости не указан', 400);
            }
            
            $newsId = $data['route_params']['id'];
            $db = Database::getInstance();
            
            // Проверка существования категории
            $news = $db->fetch("SELECT * FROM news WHERE id_news = ?", [$newsId]);
            if (!$news) {
                sendErrorResponse('Новость не найдена', 404);
            }
            
            // Удаление изображения категории
            // if ($typeActivity['image_link']) {
            //     @unlink(UPLOAD_DIR . $category['image_link']);
            // }
            
            // Удаление категории
            if (!$db->delete('news', 'id_news = ?', [$newsId])) {
                sendErrorResponse('Ошибка при удалении новости', 500);
            }
            
            sendSuccessResponse([], 'Новость успешно удалена');
        });
    }
} 