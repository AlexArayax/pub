<?php
// Путь к папке для загрузки файлов
$upload_directory = 'uploads/';

// Создание папки, если она не существует
if (!file_exists($upload_directory) && !is_dir($upload_directory)) {
    mkdir($upload_directory, 0777, true);
}

// Обработка загрузки изображений
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = [];
    $uploadedFiles = [];
    $extension = ['jpg', 'jpeg', 'png', 'gif'];

    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        $file_name = $_FILES['images']['name'][$key];
        $file_tmp = $_FILES['images']['tmp_name'][$key];
        $file_size = $_FILES['images']['size'][$key];
        $file_type = $_FILES['images']['type'][$key];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $file_name_transliterated = strtolower(transliterator_transliterate('Any-Latin; Latin-ASCII', $file_name));
        $file_name_unique = uniqid('', true) . '.' . $file_ext;

        if (in_array($file_ext, $extension) === false) {
            $errors[] = "Расширение файла {$file_name} запрещено. Разрешены только файлы: jpg, jpeg, png, gif.";
        }

        if ($file_size > 2097152) {
            $errors[] = "Файл {$file_name} слишком велик. Максимальный размер файла 2MB.";
        }

        if (file_exists($upload_directory . $file_name_transliterated)) {
            $file_name_transliterated = uniqid('', true) . '.' . $file_ext;
        }

        if (empty($errors)) {
            move_uploaded_file($file_tmp, $upload_directory . $file_name_transliterated);
            $uploadedFiles[] = $file_name_transliterated;

            // Подключение к базе данных (замените данными вашей БД)
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "test";

            $conn = new mysqli($servername, $username, $password, $dbname);

            // Запись информации о файле в базу данных
            $date_uploaded = date('Y-m-d H:i:s');
            $sql = "INSERT INTO uploaded_files (file_name, date_uploaded) VALUES ('$file_name_transliterated', '$date_uploaded')";
            $conn->query($sql);
        }
    }

    if ($errors) {
        print_r($errors);
    }
    if ($uploadedFiles) {
        print_r($uploadedFiles);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Загрузка изображений</title>
</head>
<body>
    <form id="uploadForm" action="" method="post" enctype="multipart/form-data">
        <input type="file" name="images[]" id="fileInput" multiple accept="image/*">
        <button type="submit">Загрузить</button>
    </form>

    <script type="text/javascript">
        document.getElementById('uploadForm').addEventListener('submit', function(event) {
            var fileInput = document.getElementById('fileInput');
            if (fileInput.files.length > 5) {
                alert('Максимальное количество файлов для загрузки - 5.');
                event.preventDefault(); // Отменить отправку формы
            }
        });
    </script>
</body>
</html>
