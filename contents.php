<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSV File Viewer - Contents</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .table-container {
            max-width: 100%;
            overflow-x: auto;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table th, .table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .table th {
            background-color: #f2f2f2;
        }
        .rotate {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            white-space: nowrap;
        }
        .fixed-column {
            position: sticky;
            left: 0;
            background-color: white;
            z-index: 2;
        }
        .btn-back, .btn-create, .btn-save {
            margin-bottom: 20px;
        }
        .hidden {
            display: none;
        }
        .edit-row input {
            width: 100px;
        }
        .greyed-out-checkbox {
            pointer-events: none;
            opacity: 0.6;
        }
    </style>
<script>
/*
    window.onload = function() {
        console.log('Everything on the page has been loaded. Running scrollToRightEnd() + timeout');
        setTimeout(scrollToRightEnd, 500); // Scroll to the right end of the table after a 500ms delay
    };

    // Ensure the function is defined and ready to execute after loading
    function scrollToRightEnd() {
        const table = document.getElementById('csv-table');
        table.scrollLeft = table.scrollWidth - table.clientWidth;
    }
*/
    // Existing functions...
    // Your other JavaScript functions like createEntry(), editRow(), etc.
    
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Page content fully loaded. Running scrollToRightEnd() after a brief delay.');
        setTimeout(scrollToRightEnd, 500); // Scroll to the right end of the table after a 500ms delay
    });

    function scrollToRightEnd() {
        const table = document.getElementById('csv-table');
        const headerCells = document.querySelectorAll('.table th');
        const containerWidth = table.parentElement.getBoundingClientRect().width;

        // Calculate total width of scrollable columns
        let scrollableWidth = 0;
        for (let i = 1; i < headerCells.length; i++) { // Start from 1 to skip the first fixed column
            scrollableWidth += headerCells[i].getBoundingClientRect().width;
        }

        // Adjust scrollLeft to show all scrollable columns
        if (scrollableWidth > containerWidth) {
            table.scrollLeft = scrollableWidth - containerWidth;
        }
    }
</script>
</head>
<body>
    <div class="container">
        <button class="btn btn-secondary btn-back" onclick="goBack()">Back</button>
        <button class="btn btn-success btn-create" onclick="showCreateEntry()">Create Entry</button>
        <div id="create-entry" class="hidden">
            <input type="text" id="first-name" placeholder="First Name">
            <input type="text" id="last-name" placeholder="Last Name">
            <input type="text" id="year-of-birth" placeholder="Year of Birth">
            <input type="email" id="email" placeholder="Email (Optional)">
            <button class="btn btn-primary" onclick="createEntry()">Add</button>
            <button class="btn btn-danger" onclick="cancelCreateEntry()"><i class="fas fa-times"></i></button> <!-- Abort button -->
        </div>
        <div class="table-container">
            <?php
            if (isset($_GET['filename'])) {
                $filename = 'data/' . basename($_GET['filename']);
                if (file_exists($filename)) {
                    $file = fopen($filename, 'r');
                    $data = [];
                    while (($row = fgetcsv($file)) !== false) {
                        $data[] = $row;
                    }
                    fclose($file);

                    // Add today's date if the last date is not today
                    $lastDate = end($data[0]);
                    $today = date('Y-m-d');
                    if ($lastDate !== $today) {
                        foreach ($data as &$row) {
                            $row[] = '';
                        }
                        $data[0][count($data[0]) - 1] = $today;
                    }

                    // Display the table
                    echo '<table class="table" id="csv-table">';
                    echo '<thead><tr>';
                    echo '<th class="fixed-column">Name</th>';
                    for ($i = 4; $i < count($data[0]); $i++) {
                        $date = $data[0][$i];
                        $formattedDate = date('Y-m-d', strtotime($date)); // Format date as Y-m-d
                        $dayOfWeek = date('D', strtotime($date));
                        echo '<th class="rotate">' . $dayOfWeek . ' ' . $date . '</th>';
                    }
                    echo '<th>Actions</th>';
                    echo '</tr></thead><tbody>';

                    for ($i = 1; $i < count($data); $i++) {
                        $name = $data[$i][0] . ' ' . $data[$i][1] . ' (' . $data[$i][2] . ')';
                        echo '<tr id="row-' . $i . '" data-first-name="' . htmlspecialchars($data[$i][0]) . '" data-last-name="' . htmlspecialchars($data[$i][1]) . '" data-year-of-birth="' . htmlspecialchars($data[$i][2]) . '" data-email="' . htmlspecialchars($data[$i][3]) . '">';
                        echo '<td class="fixed-column">' . htmlspecialchars($name) . '</td>';
                        for ($j = 4; $j < count($data[$i]); $j++) {
                            $isChecked = $data[$i][$j] === 'x' ? 'checked' : '';
                            $isToday = date('Y-m-d', strtotime($data[0][$j])) === $today ? true : false;
                            echo '<td class="checkbox-cell">';
                            if ($isToday) {
                                echo '<input type="checkbox" id="checkbox-' . $i . '-' . $j . '" ' . $isChecked . ' onclick="toggleCheckbox(this)">';
                            } else {
                                echo '<input type="checkbox" id="checkbox-' . $i . '-' . $j . '" ' . $isChecked . ' onclick="return false;" class="greyed-out-checkbox">';
                            }
                            echo '</td>';
                        }
                        echo '<td>';
                        echo '<button class="btn btn-warning btn-sm" onclick="editRow(' . $i . ')"><i class="fas fa-pen"></i></button>';
                        echo ' <button class="btn btn-danger btn-sm" onclick="deleteRow(' . $i . ')"><i class="fas fa-times"></i></button>';
                        echo '</td>';
                        echo '</tr>';
                    }
                    echo '</tbody></table>';

                } else {
                    echo '<p>File not found.</p>';
                }
            } else {
                echo '<p>No file specified.</p>';
            } 
            ?>
        </div>
        <button class="btn btn-primary btn-save" onclick="saveTable()">Save</button>
    </div>

    <script>
        function goBack() {
            window.history.back();
        }

        function showCreateEntry() {
            document.getElementById('create-entry').classList.remove('hidden');
            scrollToTableEnd(); // Scroll to the end of the table when creating new entry
        }

        function cancelCreateEntry() {
            document.getElementById('create-entry').classList.add('hidden');
            clearCreateEntryFields();
        }

        function createEntry() {
            const firstName = document.getElementById('first-name').value;
            const lastName = document.getElementById('last-name').value;
            const yearOfBirth = document.getElementById('year-of-birth').value;
            const email = document.getElementById('email').value || '';
            const name = `${firstName} ${lastName} (${yearOfBirth})`;

            if (!firstName || !lastName || !yearOfBirth) {
                alert('First Name, Last Name, and Year of Birth are required.');
                return;
            }

            const table = document.getElementById('csv-table').getElementsByTagName('tbody')[0];
            const row = table.insertRow();
            row.id = `row-${table.rows.length}`;
            row.setAttribute('data-first-name', firstName);
            row.setAttribute('data-last-name', lastName);
            row.setAttribute('data-year-of-birth', yearOfBirth);
            row.setAttribute('data-email', email);
            row.insertCell(0).innerHTML = name;
            // Insert empty cells for each date column
            const headerCells = document.querySelectorAll('.table th');
            for (let i = 1; i < headerCells.length - 1; i++) {
                const isToday = headerCells[i].innerText.includes('<?php echo date("D Y-m-d"); ?>');
                row.insertCell(i).innerHTML = `
                    <input type="checkbox" ${isToday ? '' : 'onclick="return false;"'} class="${isToday ? '' : 'greyed-out-checkbox'}">
                `;
            }
            row.insertCell(headerCells.length - 1).innerHTML = `
                <button class="btn btn-warning btn-sm" onclick="editRow(${table.rows.length})"><i class="fas fa-pen"></i></button>
                <button class="btn btn-danger btn-sm" onclick="deleteRow(${table.rows.length})"><i class="fas fa-times"></i></button>
            `;
            clearCreateEntryFields();
            scrollToTableEnd(); // Scroll to the end of the table after adding new entry
            scrollToRightEnd(); // Scroll to the right end of the table after adding new entry
        }

        function clearCreateEntryFields() {
            document.getElementById('first-name').value = '';
            document.getElementById('last-name').value = '';
            document.getElementById('year-of-birth').value = '';
            document.getElementById('email').value = '';
            document.getElementById('create-entry').classList.add('hidden');
        }

        function editRow(rowIndex) {
            const row = document.getElementById(`row-${rowIndex}`);
            const firstName = row.getAttribute('data-first-name');
            const lastName = row.getAttribute('data-last-name');
            const yearOfBirth = row.getAttribute('data-year-of-birth');
            const email = row.getAttribute('data-email');

            row.cells[0].innerHTML = `
                <input type="text" value="${firstName}" id="edit-first-name-${rowIndex}">
                <input type="text" value="${lastName}" id="edit-last-name-${rowIndex}">
                <input type="text" value="${yearOfBirth}" id="edit-year-of-birth-${rowIndex}">
                <input type="email" value="${email}" id="edit-email-${rowIndex}" placeholder="Email (Optional)">
                <button class="btn btn-success btn-sm" onclick="saveEdit(${rowIndex})"><i class="fas fa-save"></i></button>
                <button class="btn btn-danger btn-sm" onclick="cancelEdit(${rowIndex})"><i class="fas fa-times"></i></button> <!-- Abort button -->
            `;
            scrollToTableEnd(); // Scroll to the end of the table when editing a row
            scrollToRightEnd(); // Scroll to the right end of the table when editing a row
        }

        function cancelEdit(rowIndex) {
            const row = document.getElementById(`row-${rowIndex}`);
            const firstName = row.getAttribute('data-first-name');
            const lastName = row.getAttribute('data-last-name');
            const yearOfBirth = row.getAttribute('data-year-of-birth');
            const email = row.getAttribute('data-email');
            const name = `${firstName} ${lastName} (${yearOfBirth})`;

            row.cells[0].innerHTML = name;
            row.cells[row.cells.length - 1].innerHTML = `
                <button class="btn btn-warning btn-sm" onclick="editRow(${rowIndex})"><i class="fas fa-pen"></i></button>
                <button class="btn btn-danger btn-sm" onclick="deleteRow(${rowIndex})"><i class="fas fa-times"></i></button>
            `;
            scrollToRightEnd(); // Scroll to the right end of the table after canceling edit
        }

        function deleteRow(rowIndex) {
            document.getElementById('csv-table').deleteRow(rowIndex); // Adjust rowIndex to match table row index
        }

        function saveTable() {
            const table = document.getElementById('csv-table');
            const rows = table.getElementsByTagName('tr');
            let csvContent = '';

            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName(i === 0 ? 'th' : 'td');
                const rowContent = [];
                if (i === 0) {
                    rowContent.push('first name', 'last name', 'year of birth', 'email');
                    for (let j = 1; j < cells.length - 1; j++) {
                        const dateText = cells[j].innerText.trim();
                        const date = new Date(dateText); // Parse the date from the cell
                        const formattedDate = date.getFullYear() + '-' + ('0' + (date.getMonth() + 1)).slice(-2) + '-' + ('0' + date.getDate()).slice(-2);
                        rowContent.push(formattedDate); // Format date as Y-m-d
                    }
                } else {
                    rowContent.push(
                        rows[i].getAttribute('data-first-name'),
                        rows[i].getAttribute('data-last-name'),
                        rows[i].getAttribute('data-year-of-birth'),
                        rows[i].getAttribute('data-email')
                    );
                    for (let j = 1; j < cells.length - 1; j++) {
                        const checkbox = cells[j].getElementsByTagName('input')[0];
                        rowContent.push(checkbox.checked ? 'x' : ''); // Save 'x' if checkbox is checked, otherwise empty
                    }
                }
                csvContent += rowContent.join(',') + '\n';
            }

            fetch('functions.php?action=save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `filename=${encodeURIComponent('<?php echo basename($_GET['filename']); ?>')}&data=${encodeURIComponent(csvContent)}`,
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to save CSV file');
                }
                alert('File saved successfully.');
            })
            .catch(error => {
                console.error('Error saving CSV file:', error);
                alert('Error saving CSV file.');
            });
        }

        function scrollToTableEnd() {
            const table = document.getElementById('csv-table');
            table.scrollTop = table.scrollHeight;
        }
/*
        function scrollToRightEnd() {
            const table = document.getElementById('csv-table');
            table.scrollLeft = table.scrollWidth;
        }
  
        function scrollToRightEnd() {
        const table = document.getElementById('csv-table');
        const tableWidth = table.getBoundingClientRect().width;
        const containerWidth = table.parentElement.getBoundingClientRect().width;

        if (tableWidth > containerWidth) {
            table.scrollLeft = tableWidth - containerWidth;
        }        
        }
*/
    </script>
</body>
</html>

