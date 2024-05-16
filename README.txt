### FileUploader Library Documentation

The FileUploader library provides a simple and secure way to upload files to your server and store their information in a MySQL database. This documentation will guide you through setting up the library, using it to upload files, and integrating it into your PHP projects.

#### 1. Installation

To use the FileUploader library, follow these steps:

1. Download the Library: Download the `FileUploader.php` file from the provided source or repository.

2. Include the Library: Include the `FileUploader.php` file in your PHP project.

    
    <?php
    include_once 'FileUploader.php';
    ?>
    

#### 2. Configuration

Before using the library, you need to configure the database connection. Open the `FileUploader.php` file and find the `insertFilePath` method. Update the database connection details with your own database credentials:

php
$db = new mysqli('localhost', 'your_username', 'your_password', 'your_database');


#### 3. Usage

To upload files using the FileUploader library, follow these steps:

1. Instantiate the FileUploader Class: Create an instance of the `FileUploader` class with the desired upload directory and optional settings.

    
    <?php
    $uploadDir = 'uploads'; // Directory where files will be uploaded
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif']; // Allowed file extensions
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif']; // Allowed MIME types
    $maxFileSize = 5242880; // Maximum file size (in bytes)

    $uploader = new FileUploader($uploadDir, $allowedExtensions, $allowedMimeTypes, $maxFileSize);
    ?>
    

2. Handle File Upload: Process the uploaded file using the `upload` method. This method returns an array with information about the upload status.

    
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
        $result = $uploader->upload($_FILES['file']);
        if ($result['success']) {
            echo 'File uploaded successfully.';
        } else {
            echo 'Error: ' . $result['message'];
        }
    }
    ?>
    

#### 4. Example: File Upload Form

Here's a complete example of an HTML form to upload files using the FileUploader library:


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload Form</title>
</head>
<body>
    <h2>Upload File</h2>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="file" name="file" accept="image/*"> <!-- Accept image files only -->
        <button type="submit" name="submit">Upload</button>
    </form>
</body>
</html>


#### 5. Complete Example

Here's a full example combining the FileUploader library and the file upload form:

FileUploader.php:


<?php

class FileUploader
{
    protected $uploadDir;
    protected $allowedExtensions;
    protected $allowedMimeTypes;
    protected $maxFileSize;

    public function __construct($uploadDir, $allowedExtensions = [], $allowedMimeTypes = [], $maxFileSize = 5242880)
    {
        $this->uploadDir = $uploadDir;
        $this->allowedExtensions = $allowedExtensions;
        $this->allowedMimeTypes = $allowedMimeTypes;
        $this->maxFileSize = $maxFileSize;
    }

    public function upload($file)
    {
        $filename = $file['name'];
        $tempFile = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileError = $file['error'];
        $fileType = $file['type'];

        // Check file size
        if ($fileSize > $this->maxFileSize) {
            return ['success' => false, 'message' => 'File size exceeds the maximum allowed size.'];
        }

        // Check file type
        if (!empty($this->allowedExtensions)) {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            if (!in_array(strtolower($extension), $this->allowedExtensions)) {
                return ['success' => false, 'message' => 'File extension is not allowed.'];
            }
        }

        if (!empty($this->allowedMimeTypes)) {
            if (!$this->isAllowedMimeType($fileType)) {
                return ['success' => false, 'message' => 'File type is not allowed.'];
            }
        }

        // Secure filename
        $safeFilename = preg_replace('/[^\w\d\.\-]/', '_', $filename);
        $destination = $this->uploadDir . '/' . $safeFilename;

        // Move the uploaded file to the destination directory
        if (move_uploaded_file($tempFile, $destination)) {
            // Insert file path into the database
            $inserted = $this->insertFilePath($filename, $destination, $fileSize, $fileType);
            if ($inserted) {
                return ['success' => true, 'message' => 'File uploaded successfully.', 'path' => $destination];
            } else {
                return ['success' => false, 'message' => 'Error uploading file to the database.'];
            }
        } else {
            return ['success' => false, 'message' => 'Error uploading file.'];
        }
    }

    protected function isAllowedMimeType($mimeType)
    {
        // Allowed MIME types
        $allowedMimeTypes = [
            'image/jpeg',
            'image/png',
            'image/gif'
            // Add more allowed MIME types as needed
        ];

        return in_array($mimeType, $allowedMimeTypes);
    }

    protected function insertFilePath($filename, $path, $size, $type)
    {
        $db = new mysqli('localhost', 'root', '', 'your_database');
        if ($db->connect_error) {
            die('Connection failed: ' . $db->connect_error);
        }

        $sql = "INSERT INTO files (filename, path, size, type) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ssis', $filename, $path, $size, $type);

        return $stmt->execute();
    }
}
?>


upload_form.php:


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload Form</title>
</head>
<body>
    <h2>Upload File</h2>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="file" name="file" accept="image/*"> <!-- Accept image files only -->
        <button type="submit" name="submit">Upload</button>
    </form>
</body>
</html>

#### Conclusion

The FileUploader library provides a secure and easy-to-use solution for file uploads in PHP applications. With this library, you can safely upload files, validate their extensions and MIME types, and store their information in a MySQL database.
