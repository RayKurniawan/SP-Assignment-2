<?php
// ur mission is after create connection to database, you need to implement the following functionalities in this file:
// 1. Create a POST request to handle registration form submission
// 2. Validate the input fields: username, email, password, confirm_password make sure confirm_password matches password
// 3. Check if the username or email already exists in the database
// 4. If validation passes, hash the password and insert the new user into the database
// 5. If registration is successful, redirect to login.php with a success message
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$host = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "db_upload_exercise";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username_db, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fileUpload'])) {
    $file = $_FILES['fileUpload'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $originalName = $file['name'];
        $fileTmpPath = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileType = $file['type'];

        $uploadFileDir = 'uploads/';
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }

        $fileExtension = pathinfo($originalName, PATHINFO_EXTENSION);
        $storedName = md5(time() . $originalName) . '.' . $fileExtension;
        $dest_path = $uploadFileDir . $storedName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            // 2. Simpan informasi file ke database sesuai kolom tabel files
            $stmt = $pdo->prepare("INSERT INTO files (user_id, original_name, stored_name, file_size, file_type, uploaded_at) VALUES (:user_id, :original_name, :stored_name, :file_size, :file_type, NOW())");
            $success = $stmt->execute([
                'user_id' => $_SESSION['user_id'],
                'original_name' => $originalName,
                'stored_name' => $storedName,
                'file_size' => $fileSize,
                'file_type' => $fileType
            ]);

            if ($success) {
                header('Location: list.php');
                exit();
            } else {
                $_SESSION['error'] = "Gagal menyimpan ke database.";
                header('Location: upload.php');
                exit();
            }
        } else {
            $_SESSION['error'] = "Gagal memindahkan file ke folder uploads.";
            header('Location: upload.php');
            exit();
        }
    } else {
        $_SESSION['error'] = "Terjadi kesalahan saat upload.";
        header('Location: upload.php');
        exit();
    }
} else {
    header('Location: upload.php');
    exit();
}
?>