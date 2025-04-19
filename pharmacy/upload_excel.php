<?php
// Include database connection and PhpSpreadsheet library
require_once 'connect.php';
require 'vendor/autoload.php'; // Ensure PhpSpreadsheet is included

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    // Validate if the uploaded file is Excel
    $fileType = strtolower(pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION));
    if ($fileType !== 'xls' && $fileType !== 'xlsx') {
        echo json_encode(["status" => "error", "message" => "Only Excel files are allowed."]);
        exit;
    }

    // Move uploaded file to a temporary location
    $filePath = 'uploads/' . basename($_FILES['excel_file']['name']);
    if (move_uploaded_file($_FILES['excel_file']['tmp_name'], $filePath)) {
        try {
            // Load Excel file
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray();

            // Loop through rows and insert data into the database
            foreach ($data as $row) {
                if (!empty($row[0])) { // Assuming the first column is for ID and is not empty
                    $drugName = $row[0];
                    $costPrice = $row[1];
                    $manufacturingDate = $row[2];
                    $expiryDate = $row[3];
                    $quantity = $row[4];
                    $category = $row[5];
                    $sellingPrice = $row[6];
                    $imageName = $row[7]; // Assuming image is provided in Excel as file path

                    // Insert into database
                    $sql = "INSERT INTO drug_product (name, cost_price, manufacturing_date, expiry_date, quantity, category, selling_price, image, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssdssss", $drugName, $costPrice, $manufacturingDate, $expiryDate, $quantity, $category, $sellingPrice, $imageName);

                    if (!$stmt->execute()) {
                        echo json_encode(["status" => "error", "message" => "Error inserting data into database."]);
                        exit;
                    }
                }
            }

            echo json_encode(["status" => "success", "message" => "Excel data uploaded successfully!"]);
        } catch (Exception $e) {
            echo json_encode(["status" => "error", "message" => "Error reading Excel file: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to upload the Excel file."]);
    }
    exit;
}
?>
