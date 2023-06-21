<?php
require 'config.php';
require 'vendor/autoload.php'; // Require the Composer autoloader

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Function to calculate the total number of members
function calculateTotal($male, $female) {
  return $male + $female;
}

// Function to insert a new report into the database
function insertReport($date, $male, $female, $total) {
  global $conn;
  
  $sql = "INSERT INTO reports (date, male, female, total) VALUES ('$date', $male, $female, $total)";
  
  if ($conn->query($sql) === TRUE) {
    echo "Report inserted successfully";
  } else {
    echo "Error inserting report: " . $conn->error;
  }
}

// Function to generate Excel report for a specific date
function generateExcelReport($date) {
  global $conn;
  
  $sql = "SELECT * FROM reports WHERE date='$date'";
  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1', 'Date');
    $sheet->setCellValue('B1', 'Male');
    $sheet->setCellValue('C1', 'Female');
    $sheet->setCellValue('D1', 'Total');

    $row = 2;

    while ($row = $result->fetch_assoc()) {
      $sheet->setCellValue('A' . $row, $row['date']);
      $sheet->setCellValue('B' . $row, $row['male']);
      $sheet->setCellValue('C' . $row, $row['female']);
      $sheet->setCellValue('D' . $row, $row['total']);
      $row++;
    }

    $writer = new Xlsx($spreadsheet);
    $filename = 'report_' . $date . '.xlsx';
    $writer->save($filename);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    readfile($filename);
    exit();
  }
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $male = $_POST['male'];
  $female = $_POST['female'];
  $total = calculateTotal($male, $female);
  $date = date('Y-m-d');
  
  insertReport($date, $male, $female, $total);
}

// Retrieve all reports from the database
$sql = "SELECT * FROM reports";
$result = $conn->query($sql);
$reports = [];

if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $reports[] = $row;
  }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Weekly Member Report</title>
  <style>
    /* CSS for Glassmorphism UI */
    body {
      background-color: #e7f3fe;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      margin: 0;
      font-family: Arial, sans-serif;
    }
    
    .container {
      background-color: #ffffff;
      border-radius: 10px;
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
      padding: 30px;
      width: 400px;
      display: flex;
      flex-direction: column;
    }
    
    h1 {
      font-size: 24px;
      margin-bottom: 20px;
    }
    
    input[type="number"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }
    
    button {
      background-color: #4CAF50;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    
    button:hover {
      background-color: #45a049;
    }
    
    table {
      margin-top: 30px;
      width: 100%;
      border-collapse: collapse;
    }
    
    th, td {
      padding: 10px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    
    .actions {
      display: flex;
      gap: 5px;
    }
    
    .icon {
      width: 20px;
      height: 20px;
    }
    
    .pagination {
      margin-top: 20px;
      display: flex;
      justify-content: center;
    }
    
    .pagination button {
      margin: 0 5px;
      padding: 5px 10px;
      border: none;
      border-radius: 5px;
      background-color: #4CAF50;
      color: white;
      cursor: pointer;
    }
    
    .pagination button.active {
      background-color: #45a049;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Weekly Member Report</h1>
    <form method="POST">
      <label for="male">Number of Male:</label>
      <input type="number" id="male" name="male" required>
      
      <label for="female">Number of Female:</label>
      <input type="number" id="female" name="female" required>
      
      <label for="total">Total Number of Members:</label>
      <input type="number" id="total" name="total" readonly>
      
      <button type="submit">Generate Report</button>
    </form>
    
    <table id="reportTable">
      <thead>
        <tr>
          <th>Date</th>
          <th>Male</th>
          <th>Female</th>
          <th>Total</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($reports as $report) { ?>
          <tr>
            <td><?= $report['date'] ?></td>
            <td><?= $report['male'] ?></td>
            <td><?= $report['female'] ?></td>
            <td><?= $report['total'] ?></td>
            <td class="actions">
              <i class="fas fa-edit icon"></i>
              <a href="generate_report.php?date=<?= $report['date'] ?>" target="_blank">View</a>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
    
    <div class="pagination">
      <button class="active">Dates</button>
      <button>Males</button>
      <button>Females</button>
    </div>
  </div>
  
  <script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
  <script>
    // Calculate total number of members
    function calculateTotal() {
      var male = parseInt(document.getElementById('male').value) || 0;
      var female = parseInt(document.getElementById('female').value) || 0;
      var total = male + female;
      document.getElementById('total').value = total;
    }
    
    // Attach event listener to generate report button
    var generateReportBtn = document.querySelector('button[type="submit"]');
    generateReportBtn.addEventListener('click', calculateTotal);
  </script>
</body>
</html>
