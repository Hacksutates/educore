<?php
include 'connection.php';

if ($connect) {
    echo "Подключение к базе данных успешно!";
} else {
    echo "Ошибка подключения: " . mysqli_connect_error();
}
?>
