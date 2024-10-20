<?php

function strCreateCtrlButtons($url, $position) {
    $but_back = '<a href="' . $url . '" class="btn btn-secondary btn-back mr-2"><i class="fa-solid fa-arrow-left-long"></i></a>'; // onclick="goBack()"
    $but_save = '<button class="btn btn-primary btn-save mr-2" onclick="saveTable()">Save</button>';
    $but_create = '<button class="btn btn-success btn-create" onclick="showCreateEntry(\'' . $position . '\')">Create Entry</button>';
    return $but_back . $but_save . $but_create;
}

function strCreateEditEntry($position) {
    $ret_str = '        ';
    $ret_str .= '<div id="create-entry-' . $position . '" class="hidden">';
    $ret_str .= '<input type="text" id="first-name-' . $position . '" placeholder="First Name">';
    $ret_str .= '<input type="text" id="last-name-' . $position . '" placeholder="Last Name">';
    $ret_str .= '<input type="text" id="year-of-birth-' . $position . '" placeholder="Year of Birth (Optional)">';
    $ret_str .= '<input type="email" id="email-' . $position . '" placeholder="Email (Optional)">';
    $ret_str .= '<button class="btn btn-primary" onclick="createEntry(\'' . $position . '\')"><i class="fas fa-plus"></i></button>';
    $ret_str .= '<button class="btn btn-danger" onclick="cancelCreateEntry(\'' . $position . '\')"><i class="fas fa-xmark"></i></button> ';
    $ret_str .= '</div>';
    return $ret_str;
}

function arrReadCsv($filename) {
    // open file and read csv contents to data
    $file = fopen($filename, 'r');
    $data = [];
    while (($row = fgetcsv($file)) !== false) {
        $data[] = $row;
    }
    fclose($file);
    
    return $data;
}

function vAddToday(&$data, $dayOfWeek, $today) {
    // Check if today is spcific day of week
    $correctDay = false;
    foreach ($dayOfWeek as $dayNo) {
        if (date('N') == $dayNo)  {
            $correctDay = true;
        }
    }

    // Add today's date if the last date is not today
    $lastDate = end($data[0]);
               
    // Check if lastDate not today         
    if (($lastDate !== $today) && ($correctDay)) {
        foreach ($data as &$row) {
            $row[] = '';
        }
        $data[0][count($data[0])-1] = $today;
    }	         
}

function strCtreateTableHead($data) {
    
    // Display the table head style
    $hstyle = 'class="rotate" style="vertical-align: text-top;"';

    $ret_str = '<thead><tr>';     // Begin table head             
    $ret_str .= '<th class="fixed-column">Anzahl: ' . count($data[0]) - 4 . '<br>Name</th>'; //

    // Create date  header cells
    for ($i = 4; $i < count($data[0]); $i++) {
        $date = $data[0][$i];                             // Get date
        $formattedDate = date('Y-m-d', strtotime($date)); // Format date as Y-m-d
        $dayOfWeek = date('D', strtotime($date));         // Add day of week
        // Create entry
        $ret_str .= '<th ' . $hstyle . 'isodate="'.$formattedDate.'">' . $dayOfWeek . ' ' . $date . '</th>';
    }
    // Add edit and delte entry
    $ret_str .= '<th ' . $hstyle . '>edit</th><th ' . $hstyle . '>delete</th>';
    $ret_str .= '</tr></thead>';     // End tabel head
    
    return $ret_str;
}

function strCtrateTableBody($data, $today) {
    $ret_str = '<tbody>';           // Begin table body

    for ($i = 1; $i < count($data); $i++) {
        // Create name
        $name = $data[$i][0] . ' ' . $data[$i][1][0] . '. ';
        if ($data[$i][2] != '')
        {
                $name .='(' . $data[$i][2] . ') ';
        }
        $name .= strGetQunatity($data, $i);
        // Create table row
        $ret_str .= '<tr id="row-' . $i . '" data-first-name="' . htmlspecialchars($data[$i][0]) . '" data-last-name="' . htmlspecialchars($data[$i][1]) . '" data-year-of-birth="' . htmlspecialchars($data[$i][2]) . '" data-email="' . htmlspecialchars($data[$i][3]) . '">';
        // Create first column (name)
        $ret_str .= '<td class="fixed-column"  ondblclick="releaseCheck(' . $i . ')">' . htmlspecialchars($name) . '</td>';

        // Call function to generate all checkboxes
        $ret_str .= strGenerateCheckboxes($data, $i, $today);
 
        // add buutons
        $ret_str .= '<td style="text-align: center;"><button class="btn btn-warning btn-sm" onclick="editRow(' . $i . ')"><i class="fas fa-pen"></i></button></td>';
        $ret_str .= '<td style="text-align: center;"><button class="btn btn-danger btn-sm" onclick="deleteRow(' . $i . ')"><i class="fas fa-xmark"></i></button></td>';
        $ret_str .= '</tr>'; // End row
    }
    $ret_str .= '</tbody>';
    return $ret_str;
}

function strGetQunatity($data, $actIndex) {
    $ret_str = '';
    $sum = 0;    
    for ($j = 4; $j < count($data[0]); $j++) {
        if($data[$actIndex][$j] === 'x')
        {
            $sum += 1;
        }
    }
    $ret_str .= "[" . $sum . "]";
    return $ret_str;
}

function strGenerateCheckboxes($data, $actIndex , $today) {
    $ret_str = '';
    // Add next columns with dates
    for ($j = 4; $j < count($data[0]); $j++) {
        $isChecked = $data[$actIndex][$j] === 'x' ? 'checked' : '';
        $isToday = date('Y-m-d', strtotime($data[0][$j])) === $today ? true : false;
        // Create colum
        $ret_str .= '<td class="checkbox-cell" style="text-align: center;">';                
        if ($isToday) {
            // today do not disbale
            $ret_str .= '<input type="checkbox" id="checkbox-' . $actIndex . '-' . $j . '" ' . $isChecked . ' onclick="toggleCheckbox(this)">';
        } else {
            // no today disbale checkbox
            $ret_str .= '<input type="checkbox" id="checkbox-' . $actIndex . '-' . $j . '" ' . $isChecked . ' onclick="return false;" class="greyed-out-checkbox">';
        }
        $ret_str .= '</td>';
    }
    return $ret_str;
}

function strGenerateTableFromFile($filename, ...$dayOfWeek) {
    $ret_str = '';
    
    if (file_exists($filename)) {
        $today = date('Y-m-d');
        
        // Read csv file
        $data = arrReadCsv($filename);
        // Add actual date if wanted
        vAddToday($data, $dayOfWeek, $today);
        // Start create table
        $ret_str .= '<table class="table" id="csv-table">';
        // Create table head        
        $ret_str .= strCtreateTableHead($data);
        // Create table body
        $ret_str .= strCtrateTableBody($data, $today);
        // End table
        $ret_str .= '</table>'; // End table
    } else {
        $ret_str .=  '<p>File not found.</p>';
    }
    return $ret_str;
}

?>
