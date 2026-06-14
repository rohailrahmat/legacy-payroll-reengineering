<?php
/**
 * LEGACY PAYROLL MANAGEMENT SYSTEM
 * File: payroll.php (God File - 2000+ lines)
 *
 * This file is provided as a representative sample of the legacy codebase
 * analyzed in this re-engineering project.
 *
 * Student: Rohail Rahmat | 2023-KIU-BS4163
 * University: Karakorum International University, Gilgit
 *
 * NOTE: This code intentionally contains the anti-patterns and vulnerabilities
 * documented in the code smell analysis. It is preserved here for study purposes.
 */

// ============================================================
// SMELL #1: HARDCODED CONFIGURATION (Critical)
// Database credentials stored directly in source code
// ============================================================
$db_host = "localhost";
$db_user = "root";
$db_pass = "admin123";          // Hardcoded password in source code
$db_name = "payroll_system";

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// ============================================================
// SMELL #2: GOD FILE / GOD CLASS (Critical)
// One file handles: employees, payroll, leave, tax, reports
// ============================================================

// ---- EMPLOYEE MODULE (should be separate file/class) ----

function getEmployee() {
    global $conn;
    // SMELL #3: RAW SQL INJECTION VULNERABILITY (Critical)
    $id = $_GET['employee_id'];   // Unvalidated user input
    $query = "SELECT * FROM employees WHERE id = " . $id;
    // Attacker can enter: 1; DROP TABLE employees; --
    // Result: entire database destroyed
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

function getAllEmployees() {
    global $conn;
    // Another raw SQL — same vulnerability
    $dept = $_GET['department'];
    $query = "SELECT * FROM employees WHERE department = '" . $dept . "'";
    $result = mysqli_query($conn, $query);
    $employees = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $employees[] = $row;
    }
    return $employees;
}

function addEmployee($name, $email, $salary, $dept) {
    global $conn;
    // SMELL #4: DUPLICATE CODE — same raw SQL pattern repeated
    $query = "INSERT INTO employees (name, email, salary, department)
              VALUES ('" . $name . "', '" . $email . "', " . $salary . ", '" . $dept . "')";
    return mysqli_query($conn, $query);
}

function updateEmployee($id, $name, $salary) {
    global $conn;
    $query = "UPDATE employees SET name='" . $name . "', salary=" . $salary . " WHERE id=" . $id;
    return mysqli_query($conn, $query);
}

function deleteEmployee($id) {
    global $conn;
    $query = "DELETE FROM employees WHERE id=" . $id;
    return mysqli_query($conn, $query);
}

// ---- PAYROLL MODULE (should be separate file/class) ----

// SMELL #5: LONG METHOD — 400+ line function doing everything
function calculate_salary($employee_id, $month, $year) {
    global $conn;

    // Step 1: Get base salary
    $query = "SELECT * FROM employees WHERE id = " . $employee_id;
    $result = mysqli_query($conn, $query);
    $employee = mysqli_fetch_assoc($result);
    $base_salary = $employee['salary'];

    // Step 2: Get attendance (copy-pasted logic from leave module)
    $query2 = "SELECT COUNT(*) as present_days FROM attendance
               WHERE employee_id = " . $employee_id . "
               AND MONTH(date) = " . $month . "
               AND YEAR(date) = " . $year;
    $result2 = mysqli_query($conn, $query2);
    $attendance = mysqli_fetch_assoc($result2);
    $present_days = $attendance['present_days'];

    // Step 3: Calculate leave deductions (SMELL: Shotgun Surgery)
    // TAX RATES hardcoded here AND in tax_report.php AND in payslip.php
    $tax_rate = 0.10;           // 10% — if this changes, update 3 files!
    $income_tax = $base_salary * $tax_rate;

    // Step 4: Calculate overtime (more hardcoded values)
    $overtime_rate = 1.5;       // Also hardcoded in reports.php
    $overtime_hours = 0;

    $query3 = "SELECT SUM(overtime_hours) as total FROM overtime
               WHERE employee_id = " . $employee_id . "
               AND MONTH(date) = " . $month;
    $result3 = mysqli_query($conn, $query3);
    $ot = mysqli_fetch_assoc($result3);
    $overtime_hours = $ot['total'] ?? 0;

    $hourly_rate = $base_salary / 26 / 8;
    $overtime_pay = $overtime_hours * $hourly_rate * $overtime_rate;

    // Step 5: Calculate provident fund
    $pf_rate = 0.12;            // Also in payslip.php — Shotgun Surgery!
    $provident_fund = $base_salary * $pf_rate;

    // Step 6: Calculate allowances (more duplicated logic)
    $house_allowance = $base_salary * 0.20;
    $transport_allowance = 2000;
    $medical_allowance = 1500;

    // Step 7: Check loan deductions
    $query4 = "SELECT SUM(monthly_deduction) as loan FROM loans
               WHERE employee_id = " . $employee_id . " AND status = 'active'";
    $result4 = mysqli_query($conn, $query4);
    $loan_data = mysqli_fetch_assoc($result4);
    $loan_deduction = $loan_data['loan'] ?? 0;

    // Step 8: Calculate final salary
    $gross_salary = $base_salary + $house_allowance + $transport_allowance
                  + $medical_allowance + $overtime_pay;
    $total_deductions = $income_tax + $provident_fund + $loan_deduction;
    $net_salary = $gross_salary - $total_deductions;

    // Step 9: Save to database
    $query5 = "INSERT INTO payroll_records
               (employee_id, month, year, basic_salary, gross_salary,
                tax_deduction, pf_deduction, loan_deduction, net_salary)
               VALUES (" . $employee_id . ", " . $month . ", " . $year . ",
               " . $base_salary . ", " . $gross_salary . ", " . $income_tax . ",
               " . $provident_fund . ", " . $loan_deduction . ", " . $net_salary . ")";
    mysqli_query($conn, $query5);

    // Step 10: Generate payslip HTML inline in this function (no separation)
    $payslip_html = "<html><body>";
    $payslip_html .= "<h1>PAYSLIP - " . $month . "/" . $year . "</h1>";
    $payslip_html .= "<p>Employee: " . $employee['name'] . "</p>";
    $payslip_html .= "<p>Net Salary: PKR " . $net_salary . "</p>";
    $payslip_html .= "</body></html>";

    // Return everything mixed together — no clean data structure
    return [
        'net_salary' => $net_salary,
        'gross_salary' => $gross_salary,
        'payslip_html' => $payslip_html,
        'employee_name' => $employee['name']
    ];
}

// ---- LEAVE MODULE (should be separate file/class) ----

// SMELL #4: DUPLICATE CODE — same auth check copy-pasted from employee module
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

function applyLeave($employee_id, $leave_type, $from_date, $to_date, $reason) {
    global $conn;
    $query = "INSERT INTO leaves (employee_id, leave_type, from_date, to_date, reason, status)
              VALUES (" . $employee_id . ", '" . $leave_type . "', '" . $from_date . "',
              '" . $to_date . "', '" . $reason . "', 'pending')";
    return mysqli_query($conn, $query);
}

function approveLeave($leave_id) {
    global $conn;
    $query = "UPDATE leaves SET status = 'approved' WHERE id = " . $leave_id;
    return mysqli_query($conn, $query);
}

// ---- REPORTS MODULE (mixed in same file) ----

function generatePayrollReport($month, $year) {
    global $conn;
    // SMELL: Tax rate hardcoded again — third place!
    $tax_rate = 0.10;
    $query = "SELECT e.name, e.salary, e.department,
                     (e.salary * " . $tax_rate . ") as tax
              FROM employees e
              WHERE e.status = 'active'";
    $result = mysqli_query($conn, $query);
    // ... 200 more lines of report generation
}

// ============================================================
// ROUTING — No framework, just if-else on $_GET parameters
// ============================================================
session_start();
checkAuth();

$action = $_GET['action'] ?? 'list';

if ($action == 'list') {
    $employees = getAllEmployees();
    include 'views/employee_list.php';  // No template engine, raw PHP mixing
} elseif ($action == 'view') {
    $employee = getEmployee();
    include 'views/employee_view.php';
} elseif ($action == 'payroll') {
    $result = calculate_salary($_GET['emp_id'], $_GET['month'], $_GET['year']);
    echo $result['payslip_html'];       // Mixing data logic + presentation
} elseif ($action == 'report') {
    generatePayrollReport($_GET['month'], $_GET['year']);
} elseif ($action == 'leave') {
    applyLeave($_POST['emp_id'], $_POST['type'], $_POST['from'],
               $_POST['to'], $_POST['reason']);
}

?>
