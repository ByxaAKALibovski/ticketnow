<?php
/**
 * Основной файл API
 */

// Включение CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

// Обработка предварительных запросов OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Подключение необходимых файлов
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/database/Database.php';
require_once __DIR__ . '/helpers/functions.php';
require_once __DIR__ . '/router/Router.php';

// Создание экземпляра маршрутизатора
$router = new Router();

// Определение маршрутов

// Авторизация
$router->post('auth/login', 'AuthController::login');
// $router->post('auth/register', 'AuthController::register');
// $router->put('auth/change-password', 'AuthController::changePassword');

// Тип деятельности
$router->get('type-activity', 'TypeActivityController::getAll');
$router->post('type-activity', 'TypeActivityController::create');
$router->put('type-activity/{id}', 'TypeActivityController::update');
$router->delete('type-activity/{id}', 'TypeActivityController::delete');

// События
$router->get('events', 'EventsController::getAll');
$router->get('events/{id}', 'EventsController::getOne');
$router->post('events', 'EventsController::create');
$router->put('events/{id}', 'EventsController::update');
$router->delete('events/{id}', 'EventsController::delete');

// Площадки
$router->get('platform', 'PlatformController::getAll');
$router->post('platform', 'PlatformController::create');
$router->put('platform/{id}', 'PlatformController::update');
$router->delete('platform/{id}', 'PlatformController::delete');

// Новости
$router->get('news', 'NewsController::getAll');
$router->post('news', 'NewsController::create');
$router->put('news/{id}', 'NewsController::update');
$router->delete('news/{id}', 'NewsController::delete');

// Заказы
$router->get('orders', 'OrdersController::getAll');
$router->post('orders', 'OrdersController::create');

// Обработка запроса
$router->handleRequest(); 