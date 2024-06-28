<!DOCTYPE html>
<html lang="de">
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
        <?php
        if (isset($_GET['filename'])) {
            echo '<h1 class="text-center">Teilnehmerliste ' . pathinfo($_GET['filename'], PATHINFO_FILENAME) . '</h1>';
        }
        
        $parse_urled = parse_url($_SERVER['REQUEST_URI']);
        $main_url = $parse_urled['scheme'] . 'index.php' . $parse_urled['host'];
        
        $but_back = '<a href="' . $main_url . '" class="btn btn-secondary btn-back mr-2" onclick="goBack()"><i class="fa-solid fa-arrow-left-long"></i></a>';
        $but_save = '<button class="btn btn-primary btn-save mr-2" onclick="saveTable()">Save</button>';
        $but_create_top = '<button class="btn btn-success btn-create" onclick="showCreateEntry(\'top\')">Create Entry</button>';
        $but_create_bot = '<button class="btn btn-success btn-create" onclick="showCreateEntry(\'bottom\')">Create Entry</button>';
          
        echo $but_back . $but_save . $but_create_top
        ?>
        <div id="create-entry-top" class="hidden">
            <input type="text" id="first-name-top" placeholder="First Name">
            <input type="text" id="last-name-top" placeholder="Last Name">
            <input type="text" id="year-of-birth-top" placeholder="Year of Birth">
            <input type="email" id="email-top" placeholder="Email (Optional)">
            <button class="btn btn-primary" onclick="createEntry('top')"><i class="fas fa-plus"></i><!..Add--></button>
            <button class="btn btn-danger" onclick="cancelCreateEntry('top')"><i class="fas fa-xmark"></i></button> 
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
                    $noDayOfWeek = date('N');
                    if (($lastDate !== $today) && (($noDayOfWeek == 1) || ($noDayOfWeek == 4))) {
                        foreach ($data as &$row) {
                            $row[] = '';
                        }
                        $data[0][count($data[0])-1] = $today;
                    }		    
                    // Display the table
                    $hstyle = 'class="rotate" style="vertical-align: text-top;"';
                    
                    echo '<table class="table" id="csv-table">';
                    echo '<thead><tr>';
                    echo '<th class="fixed-column">Name</th>';
                    for ($i = 4; $i < count($data[0]); $i++) {
                        $date = $data[0][$i];
                        $formattedDate = date('Y-m-d', strtotime($date)); // Format date as Y-m-d
                        $dayOfWeek = date('D', strtotime($date));
                        echo '<th ' . $hstyle . '>' . $dayOfWeek . ' ' . $date . '</th>';
                    }
                    echo '<th ' . $hstyle . '>edit</th><th ' . $hstyle . '>delete</th>';
                    echo '</tr></thead><tbody>';

                    for ($i = 1; $i < count($data); $i++) {
                        $name = $data[$i][0] . ' ' . $data[$i][1][0] . '. (' . $data[$i][2] . ')';
                        echo '<tr id="row-' . $i . '" data-first-name="' . htmlspecialchars($data[$i][0]) . '" data-last-name="' . htmlspecialchars($data[$i][1]) . '" data-year-of-birth="' . htmlspecialchars($data[$i][2]) . '" data-email="' . htmlspecialchars($data[$i][3]) . '">';
                        echo '<td class="fixed-column"  ondblclick="releaseCheck(' . $i . ')">' . htmlspecialchars($name) . '</td>';
                        for ($j = 4; $j < count($data[0]); $j++) {
                            $isChecked = $data[$i][$j] === 'x' ? 'checked' : '';
                            $isToday = date('Y-m-d', strtotime($data[0][$j])) === $today ? true : false;
                            echo '<td class="checkbox-cell" style="text-align: center;">';
                            if ($isToday) {
                                echo '<input type="checkbox" id="checkbox-' . $i . '-' . $j . '" ' . $isChecked . ' onclick="toggleCheckbox(this)">';
                            } else {
                                echo '<input type="checkbox" id="checkbox-' . $i . '-' . $j . '" ' . $isChecked . ' onclick="return false;" class="greyed-out-checkbox">';
                            }
                            echo '</td>';
                        }
                        echo '<td style="text-align: center;">';
                        echo '<button class="btn btn-warning btn-sm" onclick="editRow(' . $i . ')"><i class="fas fa-pen"></i></button>';
                        echo '</td>';
                        echo '<td style="text-align: center;">';
                        echo '<button class="btn btn-danger btn-sm" onclick="deleteRow(' . $i . ')"><i class="fas fa-xmark"></i></button>';
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
        <div id="create-entry-bottom" class="hidden">
            <input type="text" id="first-name-bottom" placeholder="First Name">
            <input type="text" id="last-name-bottom" placeholder="Last Name">
            <input type="text" id="year-of-birth-bottom" placeholder="Year of Birth">
            <input type="email" id="email-bottom" placeholder="Email (Optional)">
            <button class="btn btn-primary" onclick="createEntry('bottom')"><i class="fas fa-plus"></i></button>
            <button class="btn btn-danger" onclick="cancelCreateEntry('bottom')"><i class="fas fa-xmark"></i></button> 
        </div>
        <?php
            echo $but_back . $but_save . $but_create_bot
        ?>
    </div>

    <script>
        function goBack() {
            window.history.back();
        }

        function showCreateEntry(Position) {
            document.getElementById(`create-entry-${Position}`).classList.remove('hidden');
            scrollToTableEnd(); // Scroll to the end of the table when creating new entry
        }

        function cancelCreateEntry(Position) {
            document.getElementById(`create-entry-${Position}`).classList.add('hidden');
            clearCreateEntryFields(`${Position}`);
        }

        function createEntry(Position) {
            const firstName = document.getElementById(`first-name-${Position}`).value;
            const lastName = document.getElementById(`last-name-${Position}`).value;
            const shortName = lastName.substring(0,1);
            const yearOfBirth = document.getElementById(`year-of-birth-${Position}`).value;
            const email = document.getElementById(`email-${Position}`).value || '';
            const name = `${firstName} ${shortName}. (${yearOfBirth})`;

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
            const firstcell = row.insertCell(0);
            firstcell.innerHTML = name;
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
            const butedit = row.insertCell(headerCells.length - 2);
            butedit.setAttribute('style', 'text-align: center;');
            butedit.innerHTML = `<button class="btn btn-warning btn-sm" onclick="editRow(${table.rows.length})"><i class="fas fa-pen"></i></button>`;
            const butdel = row.insertCell(headerCells.length - 1)
            butdel.setAttribute('style', 'text-align: center;');
            butdel.innerHTML = `<button class="btn btn-danger btn-sm" onclick="deleteRow(${table.rows.length})"><i class="fas fa-xmark"></i></button>`;
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
        }

        function saveEdit(rowIndex) {
            const firstName = document.getElementById(`edit-first-name-${rowIndex}`).value;
            const lastName = document.getElementById(`edit-last-name-${rowIndex}`).value;
            const shortName = lastName.substring(0,1);
            const yearOfBirth = document.getElementById(`edit-year-of-birth-${rowIndex}`).value;
            const email = document.getElementById(`edit-email-${rowIndex}`).value || '';
            const name = `${firstName} ${shortName}. (${yearOfBirth})`;

            const row = document.getElementById(`row-${rowIndex}`);
            row.setAttribute('data-first-name', firstName);
            row.setAttribute('data-last-name', lastName);
            row.setAttribute('data-year-of-birth', yearOfBirth);
            row.setAttribute('data-email', email);
            row.cells[0].innerHTML = name;
            row.cells[row.cells.length - 1].innerHTML = `
                <button class="btn btn-warning btn-sm" onclick="editRow(${rowIndex})"><i class="fas fa-pen"></i></button>
                <button class="btn btn-danger btn-sm" onclick="deleteRow(${rowIndex})"><i class="fas fa-times"></i></button>
            `;
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
            if (checkbox.checked) {
            	checkbox.setAttribute('checked','')
            }
            else {
                checkbox.removeAttribute('checked')
            }
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

