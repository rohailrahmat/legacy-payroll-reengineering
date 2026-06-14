# Code Smell Analysis Report
## Legacy Payroll Management System

**Student:** Rohail Rahmat | **Roll No:** 2023-KIU-BS4163  
**University:** Karakorum International University, Gilgit  
**Supervisor:** Asif Hussain  
**Reference:** Fowler, M. (1999). *Refactoring: Improving the Design of Existing Code.* Addison-Wesley.

---

## Overview

Code smells are surface indicators of deeper design problems in software. They do not always represent bugs, but they consistently signal areas where the design violates established principles and will create maintenance problems. This analysis systematically identifies, documents, and maps each code smell in the legacy payroll system to a proven refactoring remedy.

---

## Code Smell #1 — God File / God Class

**Severity:** 🔴 Critical  
**Fowler Category:** Large Class  
**Location:** `payroll.php` — 2,000+ lines

### Description
The entire system — employee management, payroll calculation, leave tracking, report generation, authentication, and routing — is contained within a single PHP file. This violates the Single Responsibility Principle: a class or module should have one reason to change.

### Evidence
```php
// payroll.php — contains ALL of the following:
function getEmployee() { ... }          // Employee module
function getAllEmployees() { ... }       // Employee module
function addEmployee() { ... }          // Employee module
function calculate_salary() { ... }     // Payroll module (400 lines alone)
function applyLeave() { ... }           // Leave module
function approveLeave() { ... }         // Leave module
function generatePayrollReport() { ... } // Reports module
function checkAuth() { ... }            // Auth module
// ... and 40+ more functions
```

### Impact
- Any change to payroll logic risks breaking employee management
- New developers must read 2,000 lines to understand any single feature
- Merge conflicts occur on every parallel development task
- Impossible to deploy one module fix without deploying the entire system

### Refactoring Technique: Extract Class
```
Before: payroll.php (2,000 lines, all responsibilities)

After:
├── EmployeeService.ts      (Employee CRUD)
├── PayrollService.ts       (Salary calculation)
├── LeaveService.ts         (Leave management)
├── ReportService.ts        (Report generation)
└── AuthMiddleware.ts       (Authentication)
```

---

## Code Smell #2 — SQL Injection Vulnerability (Raw SQL)

**Severity:** 🔴 Critical  
**Category:** Security Anti-Pattern (OWASP Top 10 — A03:2021)  
**Location:** All database-access functions

### Description
User input is concatenated directly into SQL query strings without any sanitization or parameterization. This is the most dangerous vulnerability in web applications and can result in complete data loss, unauthorized access to all payroll records, and full database destruction.

### Evidence — The Vulnerable Code
```php
// VULNERABLE — Any attacker can exploit this
function getEmployee() {
    global $conn;
    $id = $_GET['employee_id'];  // Unvalidated user input
    $query = "SELECT * FROM employees WHERE id = " . $id;
    // If attacker enters: 1; DROP TABLE employees; --
    // The executed query becomes:
    // SELECT * FROM employees WHERE id = 1; DROP TABLE employees; --
    // Result: entire employees table destroyed
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

// Another instance — string injection
function getAllEmployees() {
    $dept = $_GET['department'];
    $query = "SELECT * FROM employees WHERE department = '" . $dept . "'";
    // Attacker enters: ' OR '1'='1
    // Query returns ALL employees regardless of department
}
```

### Attack Scenarios
| Attack | Payload | Result |
|---|---|---|
| Data theft | `1 UNION SELECT username,password FROM users--` | All credentials exposed |
| Data destruction | `1; DROP TABLE employees;--` | All employee data lost |
| Authentication bypass | `' OR '1'='1'--` | Login without password |
| Data modification | `1; UPDATE employees SET salary=1 WHERE 1=1--` | All salaries set to 1 |

### Refactoring Technique: Repository Pattern + Parameterized Queries
```typescript
// SECURE — After applying Repository Pattern
async findEmployeeById(id: number): Promise<Employee> {
    // TypeORM generates parameterized queries automatically
    // User input NEVER touches the query string
    return this.employeeRepository.findOneOrFail({ where: { id } });
}

// Or with explicit parameterized query:
const employee = await this.repository.query(
    'SELECT * FROM employees WHERE id = $1',
    [id]  // id is passed as parameter, never concatenated
);
```

---

## Code Smell #3 — Duplicate Code

**Severity:** 🟠 High  
**Fowler Category:** Duplicated Code  
**Location:** 15+ files — authentication check repeated throughout

### Description
The authentication check function is copy-pasted across every module function. The same SQL patterns for retrieving employee data are repeated with minor variations. Tax and PF rates appear as hardcoded literals in multiple locations.

### Evidence
```php
// This EXACT block appears in 15+ functions:
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}
// Called or re-implemented in: getEmployee(), addEmployee(),
// calculate_salary(), applyLeave(), generatePayrollReport(), etc.

// Tax rate: appears in 3 separate locations
// payroll.php line 89:    $tax_rate = 0.10;
// tax_report.php line 34: $tax_rate = 0.10;
// payslip.php line 67:    $tax_rate = 0.10;
```

### Impact
- When tax rate changes from 10% to 12%, developer must find and update 3 files
- If they miss one file, salary calculation and tax report show different numbers
- Payroll discrepancies → employee complaints → management trust issues

### Refactoring Technique: Extract Method + Middleware Pattern
```typescript
// ONE authentication middleware used everywhere
@Injectable()
export class JwtAuthGuard extends AuthGuard('jwt') {
    // Defined once, applied via decorator on any route
}

// Usage — no copy-paste, just one decorator:
@UseGuards(JwtAuthGuard)
async getEmployee(id: number) { ... }

// Configuration centralized in one place:
// config/payroll.config.ts
export const payrollConfig = {
    taxRate: parseFloat(process.env.TAX_RATE || '0.10'),
    pfRate: parseFloat(process.env.PF_RATE || '0.12'),
    overtimeMultiplier: parseFloat(process.env.OVERTIME_RATE || '1.5'),
};
```

---

## Code Smell #4 — Shotgun Surgery

**Severity:** 🟠 High  
**Fowler Category:** Shotgun Surgery  
**Location:** Tax rates, PF rates, overtime rates across multiple files

### Description
Shotgun Surgery describes a situation where a single logical change requires modifications to many different files. In this system, changing the income tax rate requires hunting through at least 3 separate files. Missing any one of them creates a data inconsistency.

### Evidence — Files Requiring Change for a Single Tax Rate Update
```
payroll.php        line 89:   $tax_rate = 0.10;   ← Must change
tax_report.php     line 34:   $tax_rate = 0.10;   ← Must change
payslip.php        line 67:   $tax_rate = 0.10;   ← Must change
annual_summary.php line 112:  $tax_rate = 0.10;   ← Easy to miss!
```

### Impact
- Developer updates 3 files, misses `annual_summary.php`
- Monthly payslip shows PKR 5,000 tax deducted
- Annual summary shows PKR 4,800 tax paid (wrong rate × 12)
- Employee files incorrect tax returns — legal consequences

### Refactoring Technique: Move Method + Configuration Service
```typescript
// Single source of truth — change here, applies everywhere
@Injectable()
export class ConfigService {
    get taxRate(): number {
        return this.config.get<number>('TAX_RATE');
    }
    get pfRate(): number {
        return this.config.get<number>('PF_RATE');
    }
}
// Every service injects ConfigService — one place to update
```

---

## Code Smell #5 — Hardcoded Configuration

**Severity:** 🟠 High  
**Category:** Magic Numbers / Hardcoded Literals  
**Location:** `payroll.php` lines 89, 103, 118 and multiple other files

### Description
Sensitive credentials and business-rule constants are embedded directly in source code. This creates both security vulnerabilities (credentials in version control) and maintenance problems (no way to change configuration without code deployment).

### Evidence
```php
// CRITICAL: Production database password in source code
$db_host = "localhost";
$db_user = "root";
$db_pass = "admin123";     // Anyone with repo access has this password

// Business rules as magic numbers
$tax_rate = 0.10;          // Why 0.10? No comment, no named constant
$pf_rate = 0.12;           // What is this? Provident Fund? Rate of what?
$overtime_rate = 1.5;      // Multiplier? Per what period?
$house_allowance = 0.20;   // 20% of what?
```

### Impact
- Database password exposed to every developer, contractor, and anyone with repository access
- Pakistani income tax rates change with every federal budget — each change requires code deployment
- Without code comments, no developer knows *why* these numbers exist

### Refactoring Technique: Externalize Configuration
```bash
# .env file (NEVER committed to git — in .gitignore)
DATABASE_URL=postgresql://payroll_user:SecurePass123@db:5432/payroll
JWT_SECRET=super-secret-jwt-key-256-bits
TAX_RATE=0.10
PF_RATE=0.12
OVERTIME_RATE=1.5
HOUSE_ALLOWANCE_RATE=0.20
```
```typescript
// Usage — named, readable, configurable without code changes
const taxRate = this.configService.get<number>('TAX_RATE');
```

---

## Code Smell #6 — Long Method

**Severity:** 🟠 High  
**Fowler Category:** Long Method  
**Location:** `calculate_salary()` function — 400+ lines

### Description
The `calculate_salary()` function performs at least 10 distinct operations: fetching employee data, calculating attendance, computing overtime, applying tax, calculating provident fund, checking loan deductions, summing allowances, computing net salary, saving to database, and generating HTML output. No function should do more than one thing.

### Evidence
```php
function calculate_salary($employee_id, $month, $year) {
    // Step 1: Database query for employee (lines 1-10)
    // Step 2: Database query for attendance (lines 11-25)
    // Step 3: Tax calculation (lines 26-35)
    // Step 4: Overtime calculation + DB query (lines 36-60)
    // Step 5: Provident fund (lines 61-70)
    // Step 6: Allowances (lines 71-90)
    // Step 7: Loan deductions + DB query (lines 91-110)
    // Step 8: Net salary calculation (lines 111-125)
    // Step 9: Save to database (lines 126-150)
    // Step 10: Generate and return HTML (lines 151-400)
    // ... 400 lines total
}
```

### Impact
- Testing any one step requires executing all 400 lines and all 5 database queries
- A bug in HTML generation on line 350 can only be diagnosed by reading 400 lines
- Cannot reuse salary calculation without also getting HTML output

### Refactoring Technique: Decompose Method
```typescript
// 400-line method becomes an orchestrator calling small methods:
async calculateSalary(employeeId, month, year) {
    const employee    = await this.findEmployee(employeeId);        // 5 lines
    const gross       = await this.calculateGross(employee, month); // 10 lines
    const deductions  = await this.calculateDeductions(employee);   // 10 lines
    const netSalary   = gross - deductions.total;                   // 1 line
    return this.saveRecord({ employee, gross, deductions, net });   // 5 lines
}
// Each sub-method is independently testable — total: 5 small functions
```

---

## Bonus Finding — Legacy Calculation Logic Bug (Year Bleed)

**Severity:** 🟠 High  
**Category:** Logic Flaw / Data Integrity Bug  
**Location:** `payroll.php` line 112–114 (`calculate_salary()` function)

### Description
In the legacy overtime calculation, the system fetches overtime hours from the database. However, the SQL query only filters by employee ID and month, failing to filter by the target year:
```php
$query3 = "SELECT SUM(overtime_hours) as total FROM overtime
           WHERE employee_id = " . $employee_id . "
           AND MONTH(date) = " . $month;
```
As a result, as the system remains in production across multiple years, any payroll calculation for June 2026 will also sum overtime hours logged in June 2025, June 2024, etc.

### Impact
- Inflated salary payments.
- Discrepancies in payroll records year-over-year.
- Accumulating financial error as database history grows.

### Refactoring & Modernization Fix
In the target TypeScript architecture (`src/PayrollService.refactored.ts`), this is resolved using type-safe parameters that explicitly match the correct year in the query using PostgreSQL's `EXTRACT`:
```typescript
const result = await this.payrollRepository.query(
  `SELECT COALESCE(SUM(overtime_hours), 0) as total
   FROM overtime
   WHERE employee_id = $1 AND EXTRACT(MONTH FROM date) = $2 AND EXTRACT(YEAR FROM date) = $3`,
  [employeeId, month, year]
);
```

---

## Summary Table

| # | Code Smell | Severity | Files Affected | Refactoring Applied |
|---|---|---|---|---|
| 1 | God File / God Class | 🔴 Critical | payroll.php | Extract Class → 5 services |
| 2 | Raw SQL / SQL Injection | 🔴 Critical | All DB functions | Repository Pattern + ORM |
| 3 | Duplicate Code | 🟠 High | 15+ functions | Extract Method + Middleware |
| 4 | Shotgun Surgery | 🟠 High | 4 files | Move Method + Config Service |
| 5 | Hardcoded Configuration | 🟠 High | 3+ files | Externalize Configuration |
| 6 | Long Method | 🟠 High | payroll.php | Decompose Method |

---

## References

- Fowler, M. (1999). *Refactoring: Improving the Design of Existing Code.* Addison-Wesley.
- OWASP Foundation. (2021). *OWASP Top 10.* Retrieved from https://owasp.org/Top10/
- Martin, R.C. (2008). *Clean Code: A Handbook of Agile Software Craftsmanship.* Prentice Hall.

---

*Rohail Rahmat | 2023-KIU-BS4163 | Karakorum International University, Gilgit*
