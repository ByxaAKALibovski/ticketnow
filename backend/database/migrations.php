<?php
/**
 * Система миграций для базы данных
 */
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/database/Database.php';

class Migrations
{
    private $db;
    private $tables = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
        
        // Определение структуры таблиц
        $this->defineTables();
    }

    /**
     * Определение структуры таблиц
     */
    private function defineTables()
    {
        // Таблица пользователей
        $this->tables['users'] = "
            CREATE TABLE IF NOT EXISTS users (
                id_users INT AUTO_INCREMENT PRIMARY KEY,
                login VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";

        // Таблица типов мероприятий
        $this->tables['type_activity'] = "
            CREATE TABLE IF NOT EXISTS type_activity (
                id_type_activity INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";

        // Таблица площадок
        $this->tables['platform'] = "
            CREATE TABLE IF NOT EXISTS platform (
                id_platform INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                img_src VARCHAR(255) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";

        // Таблица типов билетов площадки
        $this->tables['platform_ticket'] = "
            CREATE TABLE IF NOT EXISTS platform_ticket (
                id_platform_ticket INT AUTO_INCREMENT PRIMARY KEY,
                id_platform INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                FOREIGN KEY (id_platform) REFERENCES platform(id_platform) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";

        // Таблица заказов
        $this->tables['orders'] = "
            CREATE TABLE IF NOT EXISTS orders (
                id_orders INT AUTO_INCREMENT PRIMARY KEY,
                id_events INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                phone VARCHAR(255) NOT NULL,
                FOREIGN KEY (id_events) REFERENCES events(id_events) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";

        // Таблица билетов заказа
        $this->tables['orders_ticket'] = "
            CREATE TABLE IF NOT EXISTS orders_ticket (
                id_orders_ticket INT AUTO_INCREMENT PRIMARY KEY,
                id_order INT NOT NULL,
                id_events_ticket INT NOT NULL,
                count INT NOT NULL,
                FOREIGN KEY (id_order) REFERENCES orders(id_order) ON DELETE CASCADE,
                FOREIGN KEY (id_events_ticket) REFERENCES events_ticket(id_events_ticket) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";

        // Таблица новостей
        $this->tables['news'] = "
            CREATE TABLE IF NOT EXISTS news (
                id_news INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                date_public TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";

        // Таблица событий
        $this->tables['events'] = "
            CREATE TABLE IF NOT EXISTS events (
                id_events INT AUTO_INCREMENT PRIMARY KEY,
                id_type_activity INT NOT NULL,
                id_platform INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                img_src VARCHAR(50) NOT NULL,
                date DATE NOT NULL,
                time TIME NOT NULL,
                price INT NOT NULL,
                FOREIGN KEY (id_type_activity) REFERENCES type_activity(id_type_activity) ON DELETE CASCADE,
                FOREIGN KEY (id_platform) REFERENCES platform(id_platform) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";

        // Таблица билетов события
        $this->tables['events_ticket'] = "
            CREATE TABLE IF NOT EXISTS events_ticket (
                id_events_ticket INT AUTO_INCREMENT PRIMARY KEY,
                id_events INT NOT NULL,
                id_platform_ticket INT NOT NULL,
                price INT NOT NULL,
                FOREIGN KEY (id_events) REFERENCES events(id_events) ON DELETE CASCADE,
                FOREIGN KEY (id_platform_ticket) REFERENCES platform_ticket(id_platform_ticket) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
    }

    /**
     * Выполнение миграций
     */
    public function migrate()
    {
        // Создание таблиц в правильном порядке
        $tableOrder = [
            'users',
            'type_activity',
            'platform',
            'platform_ticket',
            'orders',
            'orders_ticket',
            'news',
            'events',
            'events_ticket'
        ];

        $conn = $this->db->getConnection();
        
        // Начало транзакции
        $conn->beginTransaction();

        try {
            // Создание таблиц в правильном порядке
            foreach ($tableOrder as $tableName) {
                if (isset($this->tables[$tableName])) {
                    $conn->exec($this->tables[$tableName]);
                    echo "Таблица '$tableName' создана или уже существует.<br>";
                }
            }
            
            // Создание администратора по умолчанию
            $adminExists = $this->db->fetch("SELECT COUNT(*) as count FROM users", []);
            if (!$adminExists || $adminExists['count'] == 0) {
                $password = password_hash('admin123', PASSWORD_DEFAULT);
                $this->db->insert('users', [
                    'login' => 'admin',
                    'password' => $password
                ]);
                echo "Администратор по умолчанию создан.<br>";
            }
            
            // Фиксация транзакции
            $conn->commit();
            echo "Миграции успешно применены.<br>";
        } catch (PDOException $e) {
            // Откат транзакции в случае ошибки
            $conn->rollBack();
            echo "Ошибка при выполнении миграций: " . $e->getMessage() . "<br>";
        }
    }

    /**
     * Сброс (удаление) всех таблиц
     */
    public function reset()
    {
        $conn = $this->db->getConnection();
        
        // Отключение внешних ключей
        $conn->exec('SET FOREIGN_KEY_CHECKS = 0');
        
        // Получение списка всех таблиц
        $tables = $this->db->fetchAll("SHOW TABLES", []);
        
        // Удаление таблиц
        foreach ($tables as $table) {
            $tableName = reset($table);
            $conn->exec("DROP TABLE IF EXISTS `$tableName`");
            echo "Таблица '$tableName' удалена.<br>";
        }
        
        // Включение внешних ключей
        $conn->exec('SET FOREIGN_KEY_CHECKS = 1');
        
        echo "Все таблицы были удалены.<br>";
    }
}

// Проверка, запущен ли скрипт из консоли или через браузер
if (php_sapi_name() === 'cli') {
    // Запуск из консоли
    $migrations = new Migrations();
    
    if (isset($argv[1])) {
        switch ($argv[1]) {
            case 'migrate':
                $migrations->migrate();
                break;
            case 'reset':
                $migrations->reset();
                break;
            default:
                echo "Доступные команды: migrate, reset\n";
                break;
        }
    } else {
        echo "Доступные команды: migrate, reset\n";
    }
} else {
    // Запуск через браузер
    if (isset($_GET['action'])) {
        $migrations = new Migrations();
        
        switch ($_GET['action']) {
            case 'migrate':
                $migrations->migrate();
                break;
            case 'reset':
                $migrations->reset();
                break;
            default:
                echo "Доступные действия: migrate, reset";
                break;
        }
    } else {
        echo "Укажите действие в параметре 'action': migrate или reset";
    }
} 