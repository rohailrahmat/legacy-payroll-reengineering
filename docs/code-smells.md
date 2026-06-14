# Code Smell & Anti-Pattern Analysis
## Legacy Payroll Management System — Phase 3

**Document Type:** Model-Driven Re-Engineering Analysis  
**Technique:** Static Analysis + Pattern Recognition + Refactoring Catalog (Fowler, 1999)

---

## 1. Introduction

Code smells are surface indicators of deeper problems in software design. They do not prevent a program from functioning but signal that the codebase will become increasingly difficult to maintain, extend, and secure over time. This document identifies and analyzes six major code smells found in the Legacy Payroll Management System and maps each smell to a concrete re-engineering technique drawn from established refactoring literature.

---

## 2. Code Smell Catalog

### Smell 1 — God File / God Class

**Location:** `payroll.php`, `index.php`  
**Severity:** Critical

**Description:**  
A God Class is a class or file that knows too much and does too much. In the legacy system, `payroll.php` spans over 2,000 lines of code and is responsible for rendering HTML output, performing salary calculations, querying the database, validating user input, and generating PDF reports — all within a single file.

**Evidence:**
```php
// payroll.php (excerpt showing mixed responsibilities)
echo "<html><body>";               // Presentation
$salary = $base + $allowances;    // Business logic
$result = mysqli_query($conn, "SELECT * FROM employees"); // Data access
file_put_contents("log.txt", $salary); // Logging
echo "</body></html>";
```

**Impact:** Any modification to one responsibility risks breaking the others. Unit testing is impossible because all concerns are entangled.

**Re-Engineering Technique — Extract Class:**  
Decompose the God File into focused, single-responsibility classes:
- `EmployeeService.php` — employee data operations
- `PayrollCalculator.php` — salary computation logic
- `PayrollController.php` — request handling and response
- `PayrollRepository.php` — database queries only

---

### Smell 2 — Duplicate Code

**Location:** All 15+ PHP page files  
**Severity:** High

**Description:**  
The same session validation block appears copy-pasted at the top of every PHP file in the system. When the authentication logic needs to change, a developer must manually update 15+ files, and missing even one creates a security gap.

**Evidence:**
```php
// Repeated verbatim in employee.php, payroll.php, leave.php, reports.php ...
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    die();
}
```

**Impact:** Security vulnerabilities arise when copies fall out of sync. Maintenance cost multiplies with every duplicated block.

**Re-Engineering Technique — Extract Method + Middleware Pattern:**  
Create a single `AuthMiddleware.php`:
```php
class AuthMiddleware {
    public static function check() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }
    }
}
// All pages call: AuthMiddleware::check();
```

---

### Smell 3 — Shotgun Surgery

**Location:** Tax rate configuration across codebase  
**Severity:** High

**Description:**  
When the government updates tax rates — a routine annual event — a developer must locate and update hardcoded tax values scattered across `payroll.php`, `calculate.php`, `reports.php`, and `generate_report.php`. A single logical change triggers modifications in multiple unrelated locations.

**Evidence:**
```php
// In payroll.php
$tax = $salary * 0.15;

// In calculate.php (same hardcoded value)
$income_tax = $gross * 0.15;

// In reports.php (same again)
$tax_deduction = $total * 0.15;
```

**Impact:** High risk of inconsistency. One missed update produces incorrect payroll calculations with financial and legal consequences.

**Re-Engineering Technique — Move Method + Single Source of Truth:**  
Centralize all business rules in a dedicated configuration service:
```php
class TaxConfigService {
    public function getCurrentTaxRate(): float {
        return Config::get('tax.income_rate'); // From .env or DB
    }
}
```

---

### Smell 4 — Hardcoded Configuration

**Location:** `config.php`, inline throughout business logic  
**Severity:** High

**Description:**  
Database credentials, server hostnames, tax rates, and allowance multipliers are hardcoded directly in source files. This makes deploying to different environments (development, staging, production) dangerous and error-prone.

**Evidence:**
```php
// config.php
$host = "localhost";
$user = "root";
$pass = "admin123";       // Plaintext password in source control
$db   = "payroll_db";

// payroll.php
$housing_allowance = $salary * 0.40;  // Magic number, no explanation
$medical_allowance = $salary * 0.10;  // Magic number
```

**Impact:** Credentials exposed in version control history. Cannot deploy to cloud or CI/CD without manual file editing.

**Re-Engineering Technique — Externalize Configuration:**  
Move all environment-specific values to `.env` files:
```
DB_HOST=localhost
DB_USER=payroll_user
DB_PASS=secure_password
TAX_RATE=0.15
HOUSING_ALLOWANCE_RATE=0.40
```
Access via a typed configuration class that validates values at startup.

---

### Smell 5 — Raw SQL + Data Clumps

**Location:** All module files  
**Severity:** Critical

**Description:**  
SQL queries are constructed through direct string concatenation with user-supplied input throughout the codebase. This is the textbook definition of an SQL injection vulnerability — one of the most critical security weaknesses in web applications (OWASP Top 10).

**Evidence:**
```php
// employee.php — SQL Injection vulnerability
$id = $_GET['employee_id'];  // Unvalidated user input
$query = "SELECT * FROM employees WHERE id = " . $id;
$result = mysqli_query($conn, $query);
// Attacker can input: 1 OR 1=1 -- (returns all records)
// Or: 1; DROP TABLE employees; -- (destroys data)
```

**Impact:** Complete database compromise. An attacker can read, modify, or delete all payroll data with a single crafted URL.

**Re-Engineering Technique — Repository Pattern + Parameterized Queries:**  
```php
class EmployeeRepository {
    public function findById(int $id): ?Employee {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM employees WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetchObject(Employee::class);
    }
}
```

---

### Smell 6 — Long Method

**Location:** `calculate.php` — `calculate_salary()` function  
**Severity:** High

**Description:**  
The `calculate_salary()` function spans over 400 lines and handles base salary calculation, tax computation, all allowance types, deductions, overtime, bonuses, and final net pay in a single unbroken block of code with no sub-function decomposition.

**Evidence:**
```php
function calculate_salary($emp_id) {
    // 400+ lines handling:
    // - Base salary lookup
    // - Housing allowance
    // - Medical allowance  
    // - Transport allowance
    // - Income tax (multiple brackets)
    // - EOBI deductions
    // - Loan deductions
    // - Overtime calculation
    // - Bonus computation
    // - Net pay calculation
    // All in one giant function with no sub-functions
}
```

**Impact:** Impossible to test individual calculation steps. A bug in tax computation requires reading through 400 lines to locate.

**Re-Engineering Technique — Decompose Method:**  
```php
class PayrollCalculator {
    public function calculate(Employee $emp): PayrollResult {
        $base       = $this->calculateBaseSalary($emp);
        $allowances = $this->calculateAllowances($emp, $base);
        $tax        = $this->calculateTax($base + $allowances);
        $deductions = $this->calculateDeductions($emp);
        return new PayrollResult($base, $allowances, $tax, $deductions);
    }
    private function calculateBaseSalary(Employee $emp): float { ... }
    private function calculateAllowances(Employee $emp, float $base): float { ... }
    private function calculateTax(float $gross): float { ... }
    private function calculateDeductions(Employee $emp): float { ... }
}
```

---

## 3. Anti-Pattern Summary Table

| # | Code Smell | Location | Severity | Re-Engineering Technique |
|---|---|---|---|---|
| 1 | God File / God Class | `payroll.php` | Critical | Extract Class |
| 2 | Duplicate Code | All 15+ files | High | Extract Method + Middleware |
| 3 | Shotgun Surgery | Tax rate logic | High | Move Method + Config Service |
| 4 | Hardcoded Configuration | `config.php` + inline | High | Externalize Configuration |
| 5 | Raw SQL + Data Clumps | All module files | Critical | Repository Pattern + ORM |
| 6 | Long Method | `calculate_salary()` | High | Decompose Method |

---

## 4. Refactoring Priority

Based on severity and business impact, the recommended refactoring order is:

1. **Fix SQL Injection (Smell 5)** — Immediate security risk
2. **Extract God Class (Smell 1)** — Foundation for all other refactoring
3. **Eliminate Duplicate Auth (Smell 2)** — Security and consistency
4. **Externalize Configuration (Smell 4)** — Enables proper deployment
5. **Decompose Long Methods (Smell 6)** — Enables unit testing
6. **Fix Shotgun Surgery (Smell 3)** — Maintainability

---

## 5. Conclusion

The six code smells identified in this analysis collectively reveal a system that has grown organically without architectural discipline. Each smell has a well-established re-engineering solution documented in Fowler's *Refactoring: Improving the Design of Existing Code* (1999). The application of these techniques in the proposed order will progressively transform the legacy monolith into a maintainable, testable, and secure system — setting the stage for the full modernization strategy presented in Phase 4.

---

*References: Fowler, M. (1999). Refactoring: Improving the Design of Existing Code. Addison-Wesley.*  
*OWASP Top 10 Web Application Security Risks (2021).*

