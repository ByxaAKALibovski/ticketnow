<?php

/**
 * Контроллер для категорий
 */
class PlatformController
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
        $platform = $db->fetchAll("SELECT * FROM platform ORDER BY id_platform ASC");

        foreach ($platform as $key => $platformElement) {
            $platformElementId = $platformElement["id_platform"];
            $platformTicket = $db->fetchAll("SELECT * FROM platform_ticket WHERE id_platform = $platformElementId");
            if (!$platformTicket) continue;
            $platform[$key]["platform_ticket"] = $platformTicket;
        }

        sendSuccessResponse([
            'platform' => $platform
        ], 'Список помещений получен');
    }

    /**
     * Создание новой категории (только для администраторов)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function create($data)
    {
        adminMiddleware(function ($user) use ($data) {
            // Проверка данных
            $validation = validateData($data["body"], [
                'title' => 'required|min:3'
            ]);

            if ($validation !== true) {
                sendErrorResponse($validation, 400);
            }

            // Подготовка данных для создания
            $platformData = [
                'title' => $data["body"]['title']
            ];

            // Обработка загруженного изображения, если оно есть
            if (isset($data['files']) && isset($data['files']['image'])) {
                $uploadDir = 'uploads/platform/';
                $imagePath = handleUploadedFile($data['files'], 'image', $uploadDir);

                if ($imagePath) {
                    $platformData['img_src'] = str_replace(UPLOAD_DIR, '', $imagePath);
                }
            }

            // Создание помещения
            $db = Database::getInstance();
            $platformId = $db->insert('platform', $platformData);

            if (!$platformId) {
                sendErrorResponse('Ошибка при создании помещения', 500);
            }

            // Получение данных созданной категории
            $platform = $db->fetch("SELECT * FROM platform WHERE id_platform = ?", [$platformId]);

            // Создание билетов для помещения если они были переданны
            if (!empty($data['body']['platform_ticket'])) {
                $parseData = json_decode($data['body']['platform_ticket'], true);
                foreach ($parseData as $key => $platformTicketElement) {
                    $validation = validateData($platformTicketElement, [
                        'title' => 'required|min:3'
                    ]);

                    if ($validation !== true) {
                        sendErrorResponse($validation, 400);
                    }

                    $platformTicketData = [
                        'title' => $platformTicketElement['title'],
                        'id_platform' => $platformId
                    ];

                    $platformTicketId = $db->insert('platform_ticket', $platformTicketData);

                    if (!$platformTicketId) {
                        sendErrorResponse('Ошибка при создании билета помещения', 500);
                    }

                    $platformTicket = $db->fetch("SELECT * FROM platform_ticket WHERE id_platform_ticket = ?", [$platformTicketId]);

                    $platform['platform_ticket'][] = $platformTicket;
                }
            }

            sendSuccessResponse([
                'platform' => $platform
            ], 'Площадка успешно создана');
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
        adminMiddleware(function ($user) use ($data) {
            // Проверяем, передан ли идентификатор категории
            if (!isset($data['route_params']['id'])) {
                sendErrorResponse('Идентификатор площадки не указан', 400);
            }

            $platformId = $data['route_params']['id'];
            $db = Database::getInstance();

            // Проверка существования категории
            $platform = $db->fetch("SELECT * FROM platform WHERE id_platform = ?", [$platformId]);
            if (!$platform) {
                sendErrorResponse('Площадка не найдена', 404);
            }

            // Проверка данных
            $validation = validateData($data["body"], [
                'title' => 'required|min:3'
            ]);

            if ($validation !== true) {
                sendErrorResponse($validation, 400);
            }

            // Подготовка данных для обновления
            $platformData = [
                'title' => $data["body"]['title']
            ];

            // Обработка загруженного изображения, если оно есть
            if (isset($data['files']) && isset($data['files']['image'])) {
                $uploadDir = 'uploads/platform/';
                $imagePath = handleUploadedFile($data['files'], 'image', $uploadDir);

                if ($imagePath) {
                    $platformData['img_src'] = str_replace(UPLOAD_DIR, '', $imagePath);

                    // Удаление старого изображения
                    if ($platform['img_src']) {
                        @unlink(UPLOAD_DIR . $platform['img_src']);
                    }
                }
            }

            // Обновление площадки
            if (!$db->update('platform', $platformData, 'id_platform = ?', [$platformId])) {
                sendErrorResponse('Ошибка при обновлении площадки', 500);
            }

            // Получение обновленных данных категории
            $updatedPlatform = $db->fetch("SELECT * FROM platform WHERE id_platform = ?", [$platformId]);

            // Обновление билетов площадки если они переданы
            if (!empty($data['body']['platform_ticket'])) {
                $parseData = json_decode($data['body']['platform_ticket'], true);
                foreach ($parseData as $key => $platformTicketElement) {
                    if (!isset($platformTicketElement['id_platform_ticket'])) {
                        sendErrorResponse('Идентификатор билета площадки не указан', 400);
                    }

                    $platformTicketElementId = $platformTicketElement['id_platform_ticket'];
                    $db = Database::getInstance();

                    // Проверка существования категории
                    $platformTicket = $db->fetch("SELECT * FROM platform_ticket WHERE id_platform_ticket = ?", [$platformTicketElementId]);
                    if (!$platform) {
                        sendErrorResponse('Билет площадки не найден', 404);
                    }

                    // Проверка данных
                    $validation = validateData($platformTicketElement, [
                        'title' => 'required|min:3'
                    ]);

                    if ($validation !== true) {
                        sendErrorResponse($validation, 401);
                    }

                    // Подготовка данных для обновления
                    $platformTicketData = [
                        'title' => $platformTicketElement['title']
                    ];

                    // Обновление билета площадки
                    if (!$db->update('platform_ticket', $platformTicketData, 'id_platform_ticket = ?', [$platformTicketElementId])) {
                        sendErrorResponse('Ошибка при обновлении билета площадки', 500);
                    }

                    // Получение обновленных данных билета площадки
                    $updatedPlatformTicket = $db->fetch("SELECT * FROM platform_ticket WHERE id_platform_ticket = ?", [$platformTicketElementId]);

                    $updatedPlatform['platform_ticket'][] = $updatedPlatformTicket;
                }
            }

            // Создание билетов для помещения если они были переданны
            if (!empty($data['body']['platform_ticket_new'])) {
                $parseData = json_decode($data['body']['platform_ticket_new'], true);
                foreach ($parseData as $key => $platformTicketElement) {
                    $validation = validateData($platformTicketElement, [
                        'title' => 'required|min:3'
                    ]);

                    if ($validation !== true) {
                        sendErrorResponse($validation, 400);
                    }

                    $platformTicketData = [
                        'title' => $platformTicketElement['title'],
                        'id_platform' => $platformId
                    ];

                    $platformTicketId = $db->insert('platform_ticket', $platformTicketData);

                    if (!$platformTicketId) {
                        sendErrorResponse('Ошибка при создании билета помещения', 500);
                    }

                    $platformTicket = $db->fetch("SELECT * FROM platform_ticket WHERE id_platform_ticket = ?", [$platformTicketId]);

                    $updatedPlatform['platform_ticket'][] = $platformTicket;
                }
            }

            sendSuccessResponse([
                'platform' => $updatedPlatform
            ], 'Площадка успешно обновлен');
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
        adminMiddleware(function ($user) use ($data) {
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
