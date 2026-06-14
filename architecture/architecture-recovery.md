# Architecture Recovery Report
## As-Is Architecture Analysis — Legacy Payroll Management System

**Student:** Rohail Rahmat | **Roll No:** 2023-KIU-BS4163  
**University:** Karakorum International University, Gilgit  
**Supervisor:** Asif Hussain  
**Document:** Architecture Recovery Report v1.0

---

## 1. Introduction

Architecture recovery is the process of extracting architectural information from existing source code, documentation, and system behavior when no formal architecture documentation exists. In legacy systems, architecture is rarely documented — it must be *discovered* through systematic reverse engineering.

This report presents the complete architecture recovery of a legacy PHP/MySQL Payroll Management System. The system has been in production for over 8 years, has no architecture documentation, and has been maintained by multiple developers without any consistent design discipline.

---

## 2. Recovery Methodology

The architecture was recovered using three complementary techniques:

### 2.1 Static Code Analysis
- Manual inspection of all PHP source files
- Identification of `include` and `require` dependencies
- Function call mapping across files
- Database query pattern analysis

### 2.2 Database Schema Reverse Engineering
- MySQL schema extraction using `SHOW CREATE TABLE`
- Foreign key relationship mapping
- Identification of implicit relationships (no foreign keys enforced)

### 2.3 Component Identification
- Grouping of related functions into logical modules
- Identification of data flows between modules
- Detection of shared state and global variables

---

## 3. Recovered System Components

### 3.1 Logical Modules Identified

| Module | Primary File | Lines of Code | Responsibility |
|---|---|---|---|
| Employee Management | `payroll.php` | ~600 LOC | CRUD operations on employee records |
| Payroll Calculation | `payroll.php` | ~800 LOC | Salary computation, payslip generation |
| Leave Management | `payroll.php` | ~400 LOC | Leave applications, approvals |
| Reports | `payroll.php` | ~500 LOC | Payroll reports, summaries |
| Authentication | `payroll.php` | ~200 LOC | Session management, login |
| Database Layer | `payroll.php` | ~500 LOC | Raw SQL queries, connection |

**Critical Finding:** All 6 modules exist in a **single file**. There are no module boundaries, no interfaces, and no separation of concerns.

### 3.2 Recovered Dependency Map

```
┌─────────────────────────────────────────────────────┐
│               payroll.php (God File)                │
│                                                     │
│  ┌──────────┐    ┌──────────┐    ┌──────────────┐  │
│  │ Employee │◄──►│ Payroll  │◄──►│    Leave     │  │
│  │  Module  │    │  Module  │    │   Module     │  │
│  └────┬─────┘    └────┬─────┘    └──────┬───────┘  │
│       │               │                  │          │
│       └───────────────┼──────────────────┘          │
│                       ▼                             │
│              ┌──────────────┐                       │
│              │   Reports    │                       │
│              │   Module     │                       │
│              └──────┬───────┘                       │
│                     │                               │
│  ┌──────────────────▼──────────────────────────┐   │
│  │            Global $conn Variable             │   │
│  │         (Shared MySQL Connection)            │   │
│  └──────────────────┬──────────────────────────┘   │
└─────────────────────┼───────────────────────────────┘
                      │
                      ▼
              ┌───────────────┐
              │ MySQL 5.x DB  │
              │ (Single DB,   │
              │  All Tables)  │
              └───────────────┘
```

---

## 4. Architectural Violations Identified

### Violation 1: Absence of Layered Architecture
**Description:** The system has no separation between presentation, business logic, and data access layers. PHP logic, HTML output, and SQL queries appear in the same functions.

**Evidence:**
```php
function calculate_salary($id, $month, $year) {
    // Data access (SQL) mixed with...
    $result = mysqli_query($conn, "SELECT * FROM employees WHERE id=" . $id);
    // ...business logic mixed with...
    $net_salary = $gross - $tax - $pf;
    // ...presentation (HTML generation)
    echo "<h1>Payslip: PKR " . $net_salary . "</h1>";
}
```

**Impact:** Any change to payslip presentation requires touching salary calculation logic — high risk of introducing calculation bugs.

---

### Violation 2: God File Anti-Pattern
**Description:** A single file (`payroll.php`, 2,000+ lines) contains all system functionality. This violates the Single Responsibility Principle fundamentally.

**Evidence:** The file contains employee CRUD, payroll calculation, leave management, report generation, authentication, and routing — all in one file.

**Impact:** Every developer working on any feature must understand the entire 2,000-line file. Merge conflicts are constant. Testing any one feature requires the entire file to load.

---

### Violation 3: No API Layer
**Description:** The system has no API. All interactions are page-based PHP with direct database access. There is no way to integrate with other systems without modifying source code.

**Evidence:** No REST endpoints, no response format standardization, no authentication tokens. Mobile integration is impossible.

**Impact:** The HR module cannot share data with Finance. A mobile app cannot be built. Any integration requires direct database access — a serious security risk.

---

### Violation 4: Shared Mutable Global State
**Description:** The database connection (`$conn`) is a global variable accessed by all functions throughout the file. Any function can modify or close this connection.

**Evidence:**
```php
$conn = mysqli_connect(...);  // Global at top of file

function getEmployee() {
    global $conn;  // Every function accesses global state
    ...
}
```

**Impact:** Impossible to test functions in isolation. A bug in one function corrupting `$conn` breaks the entire system unpredictably.

---

### Violation 5: Hardcoded Configuration
**Description:** Database credentials, tax rates, allowance rates, and overtime multipliers are hardcoded directly in source code, appearing in multiple locations.

**Evidence:**
```php
$db_pass = "admin123";  // Production password in code
$tax_rate = 0.10;       // Appears in payroll.php, tax_report.php, payslip.php
$pf_rate = 0.12;        // Appears in payroll.php AND payslip.php
```

**Impact:** Changing tax rate requires editing multiple files — high risk of inconsistency. Database password is visible to every developer with repository access.

---

### Violation 6: No Authentication Architecture
**Description:** Authentication is a simple session check copy-pasted into every function with no centralized middleware or security layer.

**Evidence:**
```php
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}
// This same function is copy-pasted or called 15+ times
```

**Impact:** No role-based access control. Any authenticated user can access any function. Privilege escalation attacks are trivial.

---

## 5. Data Flow Analysis

### Current Data Flow (As-Is)
```
Browser Request
      │
      ▼
payroll.php ($_GET['action'])
      │
      ├─► if action == 'list'     → getAllEmployees() → Raw SQL → MySQL
      ├─► if action == 'payroll'  → calculate_salary() → Raw SQL → MySQL
      ├─► if action == 'leave'    → applyLeave() → Raw SQL → MySQL
      └─► if action == 'report'   → generateReport() → Raw SQL → MySQL
                                                            │
                                                            ▼
                                                    HTML echoed directly
                                                    to browser response
```

**Problem:** No data validation at any layer. User input flows directly into SQL queries. No output encoding — XSS vulnerabilities also present.

---

## 6. Database Schema Analysis

### Recovered Table Structure

| Table | Columns | Relationships |
|---|---|---|
| `employees` | id, name, email, salary, department, status | None enforced |
| `payroll_records` | id, employee_id, month, year, net_salary | Implicit FK to employees |
| `leaves` | id, employee_id, from_date, to_date, status | Implicit FK to employees |
| `attendance` | id, employee_id, date, status | Implicit FK to employees |
| `overtime` | id, employee_id, date, hours | Implicit FK to employees |
| `loans` | id, employee_id, amount, monthly_deduction, status | Implicit FK to employees |

**Critical Finding:** No foreign key constraints exist. The database accepts orphaned records (payroll for deleted employees). Data integrity is enforced only in PHP code — which itself is unreliable.

**Critical Query Logic Bug Discovered:** During database query recovery, a major logic flaw was identified in the overtime query used inside the payroll calculation module. The query calculates monthly hours by filtering only on the month: `AND MONTH(date) = $month`. Because it completely lacks a year filter, it sums overtime hours from the same month across *all previous years* present in the database, resulting in highly inflated and incorrect salary calculations as the database grows over time.


---

## 7. Summary of Architectural State

| Metric | Assessment |
|---|---|
| Architectural Style | Monolithic — Procedural |
| Layer Separation | None |
| Module Boundaries | None |
| Interface Definitions | None |
| Security Architecture | None |
| API Design | None |
| Test Architecture | None |
| Configuration Management | None |
| Overall Health Score | 2/10 — Critical |

---

## 8. Conclusion

The recovered architecture reveals a system in critical condition. While it fulfills its functional purpose — processing payroll — it does so at significant risk. The absence of any architectural discipline means that every maintenance activity carries risk of introducing new bugs, every feature addition increases complexity exponentially, and every security vulnerability remains undetected and unfixed.

The recovered architecture serves as the definitive baseline for the re-engineering work documented in subsequent project artifacts.

---

*Rohail Rahmat | 2023-KIU-BS4163 | Karakorum International University, Gilgit*
