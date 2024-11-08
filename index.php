<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSV File Viewer</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            
            background-image: url('./background.svg');
            background-size: cover;
            background-position: center;
            height: 100vh; /* Full viewport height */
            
            color: #DDDDDD; /*white;*/
        }
        .file-list {
            list-style-type: none;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-evenly;
        }
        .file-item {
            width: calc(33.33% - 10px); /* Three files per row with spacing */
            margin-bottom: 20px;
            text-align: center;
/*            padding: 10px;*/
            border: none; /*1px solid #ccc;*/
            /*border-radius: 5px;*/
/*            cursor: pointer; *//* Cursor pointer to indicate clickable */
            
/*            background-color: white;*/ /* White background for the container */
            background-color: #DDDDDD;
            border-radius: 15px; /* Rounded edges */
            padding: 20px; /* Padding inside the container */
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1); /* Optional: Add a subtle shadow for better visual effect */
        }
        .file-icon {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .file-name {
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .btn-file {
            margin-top: 5px;
        }
        .btn-create {
            margin-bottom: 20px;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center">SVG Anwesenheitsliste</h1>
        <h3>Verfügbare Listen</h3>
        <hr class="hr" />
        <ul class="file-list" id="file-list">
            <?php
            // List all CSV files in the data directory
            $files = glob('data/*.csv');
            foreach ($files as $file) {
                $filename = basename($file);
                echo '<div class="file-item">';
                echo '<div><div class="btn btn-outline-dark" onclick="redirectToContents(\'' . $filename . '\')" style="width: calc(100.00% - 10px);">'; // onclick="redirectToContents(\'' . $filename . '\')">;
                echo '<div class="file-icon"><i class="fas fa-file-csv"></i></div>';
                echo '<div class="file-name">' . htmlspecialchars($filename) . '</div></div></div>';
                echo '<a class="btn btn-primary btn-file mr-1" href="data/' . htmlentities($filename) . '" download>Download</a>';
//                $filename_encoded = urlencode($filename);
//                echo '<a class="btn btn-primary btn-file mr-1" href="data/' . htmlentities($filename_encoded) . '" download>Download</a>';
                echo '<button class="btn btn-danger btn-file" onclick="deleteCsv(\'' . $filename . '\', event)">Delete</button>';
                echo '</div>';
            }
            ?>
        </ul>
        <hr class="hr" />
        <button class="btn btn-success btn-create" onclick="createCsvFile()">Create New CSV File</button>
    </div>

    <script>
        function deleteCsv(filename, event) {
            event.stopPropagation(); // Stop event propagation to prevent triggering the parent click event
            const confirmed = confirm(`Are you sure you want to delete the file "${filename}"?`);
            if (!confirmed) {
                return;
            }
            fetch('functions.php?action=delete&filename=' + encodeURIComponent(filename))
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to delete CSV file');
                    }
                    alert(`File "${filename}" deleted successfully.`);
                    location.reload(); // Reload the page to update the file list
                })
                .catch(error => {
                    console.error('Error deleting CSV file:', error);
                    alert('Error deleting CSV file.');
                });
        }

        function createCsvFile() {
            const filename = prompt('Enter the name of the new CSV file (with .csv extension):');
            if (!filename) {
                return;
            }
            fetch('functions.php?action=create&filename=' + encodeURIComponent(filename))
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to create CSV file');
                    }
                    alert(`File "${filename}" created successfully.`);
                    location.reload(); // Reload the page to update the file list
                })
                .catch(error => {
                    console.error('Error creating CSV file:', error);
                    alert('Error creating CSV file.');
                });
        }
        
        function redirectToContents(filename) {
            window.location.href = 'contents.php?filename=' + encodeURIComponent(filename);
        }
    </script>
</body>
</html>

