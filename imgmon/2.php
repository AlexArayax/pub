<?php
// Подключение к базе данных (замените данными вашей БД)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "test";

// Функция для проверки и сохранения загруженных файлов
function uploadFiles() {
    global $servername, $username, $password, $dbname;

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Создание директории uploads, если её нет
    if (!file_exists('uploads')) {
        mkdir('uploads', 0777, true);
    }

    // Максимальное количество файлов для загрузки
    $max_files = 5;

    // Счетчик успешно загруженных файлов
    $uploaded_count = 0;

    // Обработка загруженных файлов
    foreach ($_FILES["files"]["error"] as $key => $error) {
        if ($error == UPLOAD_ERR_OK) {
            $file_name = $_FILES["files"]["name"][$key];
            $file_name = strtolower($file_name);
            $file_name = preg_replace('/[^a-z0-9_.]/', '', $file_name); // Транслитерация

            // Генерация уникального имени файла, если такой файл уже существует
            $i = 1;
            $original_file_name = $file_name;
            while (file_exists("uploads/$file_name")) {
                $file_name = pathinfo($original_file_name, PATHINFO_FILENAME) . '_' . $i . '.' . pathinfo($original_file_name, PATHINFO_EXTENSION);
                $i++;
            }

            $target_path = "uploads/" . $file_name;

            if (move_uploaded_file($_FILES["files"]["tmp_name"][$key], $target_path)) {
                // Запись информации о загруженном файле в базу данных
                $sql = "INSERT INTO uploaded_files (file_name, date_uploaded) VALUES ('$file_name', NOW())";
                if ($conn->query($sql) === TRUE) {
                    $uploaded_count++;
                }
            }
        }
    }

    $conn->close();
    return $uploaded_count;
}

// Обработка POST-запроса для загрузки файлов
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $uploaded_count = uploadFiles();
    // После загрузки изображений перенаправляем на корневую страницу
    header("Location: /");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Загрузка и просмотр изображений</title>
</head>
<body>
    <h1>Загрузка изображений</h1>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="file" name="files[]" multiple>
        <input type="submit" value="Загрузить">
    </form>

    <h1>Просмотр изображений</h1>
    <?php
    // Запрос на получение информации о загруженных файлах
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT * FROM uploaded_files";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<div>';
            echo '<a href="uploads/' . $row["file_name"] . '" target="_blank">';
            echo '<img src="uploads/' . $row["file_name"] . '" alt="' . $row["file_name"] . '" style="max-width: 200px; max-height: 200px;">';
            echo '</a>';
            echo '<p>' . $row["file_name"] . '</p>';
            echo '<p>' . $row["date_uploaded"] . '</p>';
            echo '</div>';
        }
    } else {
        echo "Нет загруженных изображений.";
    }

    $conn->close();
    ?>

</body>
</html>
