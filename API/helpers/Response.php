<?php

class Response {
    /**
     * Отправка JSON-ответа
     *
     * @param int $status HTTP-статус-код (200, 400, 500 и т.д.)
     * @param string $message Сообщение, описывающее результат
     * @param mixed|null $data Дополнительные данные для отправки (может быть массивом или объектом)
     */
    public static function send(int $status, string $message, $data = null) {
        // Устанавливаем HTTP-статус
        http_response_code($status);

        // Формируем JSON-ответ
        $response = [
            'status' => $status,
            'message' => $message,
            'data' => $data
        ];

        // Указываем заголовки
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *'); // Разрешает CORS для всех источников
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS'); // Разрешённые методы

        // Отправляем JSON
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit(); // Завершаем выполнение
    }

    /**
     * Отправка успешного ответа
     *
     * @param mixed|null $data Данные для отправки
     * @param string $message Сообщение, описывающее успешный результат
     */
    public static function success($data = null, string $message = "Success") {
        self::send(200, $message, $data);
    }

    /**
     * Отправка ошибки
     *
     * @param int $status HTTP-статус-код ошибки
     * @param string $message Сообщение, описывающее ошибку
     * @param mixed|null $data Дополнительные данные об ошибке
     */
    public static function error(int $status, string $message, $data = null) {
        self::send($status, $message, $data);
    }
}
