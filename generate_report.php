<?php
require 'vendor/autoload.php';
require 'config.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Query the database to retrieve all stored reports
$sql = "SELECT * FROM reports";
$result = mysqli_query($conn, $sql);

// Check if any reports are found
if (mysqli_num_rows($result) == 0) {
  echo 'No reports found';
  exit;
}

// Create a new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set the column headers
$sheet->setCellValue('A1', 'Date');
$sheet->setCellValue('B1', 'Male');
$sheet->setCellValue('C1', 'Female');
$sheet->setCellValue('D1', 'Total');

// Set row index to start writing report data
$rowIndex = 2;

// Fetch and write the report data for each stored report
while ($report = mysqli_fetch_assoc($result)) {
  $sheet->setCellValue('A' . $rowIndex, $report['date']);
  $sheet->setCellValue('B' . $rowIndex, $report['male']);
  $sheet->setCellValue('C' . $rowIndex, $report['female']);
  $sheet->setCellValue('D' . $rowIndex, $report['total']);
  
  $rowIndex++;
}

// Set the file name and type
$filename = 'all_reports.xlsx';
$writer = new Xlsx($spreadsheet);

// Set the appropriate headers for file download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Save the spreadsheet to the output
$writer->save('php://output');
