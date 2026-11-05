<?php
// ur mission is after create connection to database, you need to implement the following functionalities in this file:
// 1. Create a POST request to handle file upload form submission & validate the user session to ensure the user is authenticated before allowing file upload
// 2. Validate the uploaded file to ensure it meets the required criteria (e.g., file type, size limit)
// try to limit the file size to 5MB and only allow certain file types (e.g., PDF, DOCX, JPG, PNG)
// 3. Move the uploaded file to a designated directory on the server (e.g., "uploads/")
// 4. Store the file information (e.g., file name, size, upload date, user ID) in the database for future reference
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
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username_db, $password_db);$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fileUpload'])) {
    $file =$_FILES['fileUpload'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $originalName =$file['name'];
        $fileTmpPath =$file['tmp_name'];
        $fileSize =$file['size'];

        $uploadFileDir = 'uploads/';
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }

        $fileExtension = pathinfo($originalName, PATHINFO_EXTENSION);$newFileName = md5(time() . $originalName) . '.' .$fileExtension;
        $dest_path = $uploadFileDir .$newFileName;

        if (move_uploaded_file($fileTmpPath,$dest_path)) {

            $stmt =$pdo->prepare("INSERT INTO files (user_id, original_name, stored_name, file_size, uploaded_at) VALUES (:user_id, :original_name, :stored_name, :file_size, NOW())");
            $success =$stmt->execute([
                'user_id' => $_SESSION['user_id'],
                'original_name' => $originalName,
                'stored_name' => $newFileName,
                'file_size' => $fileSize,
                'file_type' => $file['type']
            ]);

            if ($success) {                
                header('Location: upload.php');
                exit();
            }

        } else {
            $_SESSION['error'] = "Gagal memindahkan file yang di-upload ke folder tujuan.";
            header('Location: upload.php');
            exit();
        }

    } else {
        $_SESSION['error'] = "Terjadi kesalahan pada sistem upload file (Error code: " . $file['error'] . ").";
        header('Location: upload.php');
        exit();
    }
} else {
    header('Location: upload.php');
    exit();
}
?>