<!DOCTYPE html>
<html lang="de">
<head>    
    <?php
    // add library to file
    require 'contents_lib.php'
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSV File Viewer - Contents</title>
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
            
            color: #DDDDDD;//white;
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
            background-color: #e9ecef; /*$gray-200;*/
        }
        .table td {
            background-color: #f8f9fa /*$light;*/
        }
        .rotate {
            writing-mode: vertical-rl;
            transform: rotate(180deg);            
            white-space: nowrap;            
        }
        .fixed-column {
            position: sticky;
            left: 0;
            /*background-color: white;*/
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
        <?php
        if (isset($_GET['filename'])) {
            echo '<h1 class="text-center">Teilnehmerliste ' . pathinfo($_GET['filename'], PATHINFO_FILENAME) . '</h1>';
        }
        
        $parse_urled = parse_url($_SERVER['REQUEST_URI']);
        $main_url = $parse_urled['scheme'] . 'index.php' . $parse_urled['host'];

        echo strCreateCtrlButtons($main_url, 'top');
        echo strCreateEditEntry('top');
        ?>
        <div class="table-container">
            <?php
            if (isset($_GET['filename'])) {
                $filename = 'data/' . basename($_GET['filename']);
                
                echo strGenerateTableFromFile($filename, 1, 4);

            } else {
                echo '<p>No file specified.</p>';
            } 
            ?>
        </div>
        <?php
            echo strCreateEditEntry('bottom');
            echo strCreateCtrlButtons($main_url, 'bottom');
        ?>
    </div>

    <script>
        function showCreateEntry(Position) {
            document.getElementById(`create-entry-${Position}`).classList.remove('hidden');
            scrollToTableEnd(); // Scroll to the end of the table when creating new entry
        }

        function cancelCreateEntry(Position) {
            document.getElementById(`create-entry-${Position}`).classList.add('hidden');
            clearCreateEntryFields(`${Position}`);
        }

        function createEntry(Position) {
            const firstName = document.getElementById(`first-name-${Position}`).value.trim();
            const lastName = document.getElementById(`last-name-${Position}`).value.trim();
            const yearOfBirth = document.getElementById(`year-of-birth-${Position}`).value.trim() || '';
            const email = document.getElementById(`email-${Position}`).value || '';

            if (!firstName || !lastName /*|| !yearOfBirth*/) {
//                alert('First Name, Last Name, and Year of Birth are required.');
                alert('First Name and Last Name are required.');
                return;
            }

            const table = document.getElementById('csv-table').getElementsByTagName('tbody')[0];
            const row = table.insertRow();
            row.id = `row-${table.rows.length}`;
            row.setAttribute('data-first-name', firstName);
            row.setAttribute('data-last-name', lastName);
            row.setAttribute('data-year-of-birth', yearOfBirth);
            row.setAttribute('data-email', email);
            const firstcell = row.insertCell(0);
            firstcell.innerHTML = trimName(firstName, lastName, yearOfBirth);
            firstcell.setAttribute('ondblclick', `releaseCheck(${table.rows.length})`);
            firstcell.setAttribute('class', `fixed-column`);
            // Insert empty cells for each date column
            const headerCells = document.querySelectorAll('.table th');
            for (let i = 1; i < headerCells.length - 2; i++) {
                const isToday = headerCells[i].innerText.includes('<?php echo date("D Y-m-d"); ?>');
                const cell = row.insertCell(i);
                cell.setAttribute('style', 'text-align: center;');
                cell.innerHTML = `<input id="checkbox-${table.rows.length}-${i+3}" type="checkbox" ${isToday ? '' : 'onclick="return false;"'}${isToday ? '' : ' class="greyed-out-checkbox"'}>`;
            } 
            
            row.insertCell(headerCells.length - 2); //edit
            row.insertCell(headerCells.length - 1); //delete
            addEditDelteButton(row, table.rows.length);
            
            clearCreateEntryFields(`${Position}`);
            scrollToTableEnd(); // Scroll to the end of the table after adding new entry
            scrollToRightEnd(); // Scroll to the right end of the table after adding new entry
        }

        function clearCreateEntryFields(Position) {
            document.getElementById(`first-name-${Position}`).value = '';
            document.getElementById(`last-name-${Position}`).value = '';
            document.getElementById(`year-of-birth-${Position}`).value = '';
            document.getElementById(`email-${Position}`).value = '';
            document.getElementById(`create-entry-${Position}`).classList.add('hidden');
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
                <input type="text" value="${yearOfBirth}" id="edit-year-of-birth-${rowIndex}" placeholder="Year of Birth (Optional)">
                <input type="email" value="${email}" id="edit-email-${rowIndex}" placeholder="Email (Optional)">
                <button class="btn btn-success btn-sm" onclick="saveEdit(${rowIndex})"><i class="fas fa-save"></i></button>
                <button class="btn btn-danger btn-sm" onclick="cancelEdit(${rowIndex})"><i class="fas fa-times"></i></button>
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

            row.cells[0].innerHTML = trimName(firstName, lastName, yearOfBirth) + ' [' + getChecked(row) + ']';
        }

        function saveEdit(rowIndex) {            
            const firstName = document.getElementById(`edit-first-name-${rowIndex}`).value;
            const lastName = document.getElementById(`edit-last-name-${rowIndex}`).value;
            const yearOfBirth = document.getElementById(`edit-year-of-birth-${rowIndex}`).value;
            const email = document.getElementById(`edit-email-${rowIndex}`).value || '';
                        
            const row = document.getElementById(`row-${rowIndex}`);
            row.setAttribute('data-first-name', firstName);
            row.setAttribute('data-last-name', lastName);
            row.setAttribute('data-year-of-birth', yearOfBirth);
            row.setAttribute('data-email', email);
            
            row.cells[0].innerHTML = trimName(firstName, lastName, yearOfBirth) + ' [' + getChecked(row) + ']';
        }

        function getChecked(row)
        {
            let sum = 0;
            const inputs = row.querySelectorAll('input');
                        
            for(let i = 0; i < inputs.length; i++)
            {                
                console.log(inputs[i]);
                if (inputs[i].hasAttribute("checked"))
                {
                    sum += 1;
                }
            }
            return sum;
        }
        
        function trimName(firstName, lastName, yearOfBirth) {
            const shortName = lastName.substring(0,1);
            const birth = yearOfBirth ? `(${yearOfBirth})`:'';
            return `${firstName} ${shortName}. ${birth}`

        }
        
        function addEditDelteButton(row, rowIndex) {
            row.cells[row.cells.length - 2].setAttribute('style', 'text-align: center;');
            row.cells[row.cells.length - 2].innerHTML = `
                <button class="btn btn-warning btn-sm" onclick="editRow(${rowIndex})"><i class="fas fa-pen"></i></button>`;
            row.cells[row.cells.length - 1].setAttribute('style', 'text-align: center;');    
            row.cells[row.cells.length - 1].innerHTML = `
                <button class="btn btn-danger btn-sm" onclick="deleteRow(${rowIndex})"><i class="fas fa-times"></i></button>`;
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
                    for (let j = 1; j < cells.length - 2; j++) {
                        /* old stuff - seems not to work ios
                        const dateText = cells[j].innerText.trim();
                        const date = new Date(dateText); // Parse the date from the cell
                        const formattedDate = date.getFullYear() + '-' + ('0' + (date.getMonth() + 1)).slice(-2) + '-' + ('0' + date.getDate()).slice(-2);
                        rowContent.push(formattedDate); // Format date as Y-m-d
                        */
                        if (cells[j].getAttribute('isodate') != null)
                        {
                            rowContent.push(cells[j].getAttribute('isodate'));
                        }
                    }
                } else {
                    rowContent.push(
                        rows[i].getAttribute('data-first-name'),
                        rows[i].getAttribute('data-last-name'),
                        rows[i].getAttribute('data-year-of-birth'),
                        rows[i].getAttribute('data-email')
                    );
                    for (let j = 1; j < cells.length - 2; j++) {
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
        
        function toggleCheckbox(checkbox) {
            let rowIndex = checkbox.id.split('-')[1];
            if (checkbox.checked) {
            	checkbox.setAttribute('checked','')
            }
            else {
                checkbox.removeAttribute('checked')
            }
            
            cancelEdit(rowIndex);
        }

        function releaseCheck(rowIndex) {	    
            const d = new Date();
            const day_no = d.getDay();
            let count = 2;
            
            if ((day_no==4) || (day_no==1))
            {
                count+=1;
            }            
            
            const cells = document.getElementById(`row-${rowIndex}`).getElementsByTagName('td');	    
            
            for (let i = 1; i < cells.length - count; i++) {
                const input = cells[i].getElementsByTagName('input')[0];
                if (input.outerHTML.indexOf("return false;") >= 0) {
                    if (!input.checked) {
                        input.removeAttribute('checked');    
                    }
                    input.removeAttribute('onclick');
                    input.removeAttribute('class');
                    input.setAttribute('onclick','toggleCheckbox(this)');
                }
                else {
                    if (input.checked) {
                        input.setAttribute('checked','');    
                    }	        	
                    input.setAttribute('onclick','return false;');
                    input.setAttribute('class','greyed-out-checkbox');	            
                }
                cells[i].innerHTML = input.outerHTML;
            }
        }

        function scrollToTableEnd() {
            const table = document.getElementById('csv-table');
            table.scrollTop = table.scrollHeight;
        }

    </script>
</body>
</html>

