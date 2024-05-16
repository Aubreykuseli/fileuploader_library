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
            // Images
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/bmp',
            'image/webp',
            // Documents
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation', // .pptx
            // Audio
            'audio/mpeg',
            'audio/wav',
            'audio/x-wav',
            'audio/ogg',
            // Video
            'video/mp4',
            'video/quicktime',
            'video/x-msvideo',
            'video/x-ms-wmv',
            // Text
            'text/plain',
            'text/csv',
            // Archives
            'application/zip',
            'application/x-rar-compressed',
            'application/x-tar',
            'application/x-gzip',
            'application/x-bzip2',
            'application/x-7z-compressed',
            // Add more allowed MIME types as needed
        ];

        return in_array($mimeType, $allowedMimeTypes);
    }

    protected function insertFilePath($filename, $path, $size, $type)
    {
        $db = new mysqli('localhost', 'root', '', 'wap');
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
