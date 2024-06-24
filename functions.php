<?php

$action = $_GET['action'] ?? '';

// Handle action based on request
switch ($action) {
    case 'create':
        createCsvFile($_GET['filename'] ?? '');
        break;
    case 'delete':
        deleteCsvFile($_GET['filename'] ?? '');
        break;
    case 'save':
        saveCsvFile($_POST['filename'] ?? '', $_POST['data'] ?? '');
        break;
    default:
        break;
}

// Function to create a new CSV file
function createCsvFile($filename) {
    if (!str_ends_with($filename, '.csv')) {
        $filename = $filename . '.csv';
    }
    $file = 'data/' . $filename;

    if (!file_exists($file)) {
        $defaultContent = "first name,last name,year of birth,email\n";
        file_put_contents($file, $defaultContent);
        echo 'File created successfully.';
    } else {
        echo 'File already exists.';
    }
}

// Function to delete a CSV file
function deleteCsvFile($filename) {
    $file = 'data/' . $filename;
    if (file_exists($file)) {
        unlink($file);
        echo 'File deleted successfully.';
    } else {
        echo 'File not found.';
    }
}

// Function to save CSV file content
function saveCsvFile($filename, $csvContent) {
    $file = 'data/' . $filename;
    if (file_exists($file)) {
        unlink($file);
        echo 'File deleted successfully.';
    }
    file_put_contents($file, $csvContent);
    echo $csvContent;
    echo 'File saved successfully.';
}

?>

