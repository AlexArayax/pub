<?php
// Подключение к базе данных (замените данными вашей БД)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "test";

// Функция для вывода информации о всех загруженных файлах в JSON
function getAllFilesInfo() {
    global $servername, $username, $password, $dbname;

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die(json_encode(array("error" => "Connection failed: " . $conn->connect_error)));
    }

    $sql = "SELECT * FROM uploaded_files";
    $result = $conn->query($sql);

    $files = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $files[] = $row;
        }
    }

    $conn->close();
    return json_encode($files);
}

// Функция для получения информации о загруженном файле по его ID в JSON
function getFileInfoById($id) {
    global $servername, $username, $password, $dbname;

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die(json_encode(array("error" => "Connection failed: " . $conn->connect_error)));
    }

    $sql = "SELECT * FROM uploaded_files WHERE id = $id";
    $result = $conn->query($sql);

    $file = array();
    if ($result->num_rows > 0) {
        $file = $result->fetch_assoc();
    }

    $conn->close();
    return json_encode($file);
}

// Обработка запросов
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["id"])) {
        $id = $_GET["id"];
        echo getFileInfoById($id);
    } else {
        echo getAllFilesInfo();
    }
} else {
    http_response_code(405); // Метод не разрешен
    echo json_encode(array("error" => "Метод не разрешен"));
}
?>
