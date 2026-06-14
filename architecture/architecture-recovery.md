# Architecture Recovery Report
## Legacy Payroll Management System

**Project:** Software Re-Engineering — Semester Project  
**Document:** Architecture Recovery (As-Is Analysis)  
**Methodology:** Source Code Analysis, Reverse Engineering, Component Identification

---

## 1. Introduction

Architecture recovery is the process of extracting architectural knowledge from an existing system when formal documentation is absent or outdated. This report presents the recovered architecture of a Legacy PHP/MySQL Payroll Management System through systematic reverse engineering and static code analysis.

The recovered architecture serves as the foundation for identifying design flaws, anti-patterns, and modernization opportunities discussed in subsequent phases of this project.

---

## 2. System Overview

| Attribute | Details |
|---|---|
| System Type | Payroll Management System |
| Primary Language | PHP 5.x (Procedural) |
| Database | MySQL 5.x |
| Frontend | HTML, CSS, jQuery |
| Server | Apache HTTP Server |
| Architecture Style | Monolithic — Single deployable unit |
| Estimated Codebase | ~8,000 Lines of Code |
| Development Era | Early 2000s patterns |

---

## 3. Architecture Recovery Methodology

The following techniques were applied to recover the system architecture:

### 3.1 Static Code Analysis
Examination of PHP source files to identify module boundaries, function call graphs, and data flow between components without executing the program.

### 3.2 Database Schema Reverse Engineering
Analysis of SQL dump files and table relationships to reconstruct the data model and identify how data is shared across modules.

### 3.3 Component Identification
Grouping related PHP files by functionality to identify logical modules even though no physical module boundaries exist in the codebase.

### 3.4 Dependency Mapping
Tracing `include`, `require`, and direct SQL calls across files to map inter-module dependencies and coupling relationships.

---

## 4. Recovered Architecture — As-Is

### 4.1 Architectural Style
The system follows a **Monolithic Architecture** — all functionality is packaged and deployed as a single unit. There is no meaningful separation between presentation, business logic, and data access layers.

### 4.2 Identified Layers

#### Presentation Layer
- HTML mixed directly inside PHP files
- jQuery used for basic DOM manipulation
- No templating engine or view separation
- Business logic embedded inside HTML-generating PHP scripts

#### Application Layer (Monolith Core)
The following logical modules were identified within the monolith:

| Module | Responsibility | Files Identified |
|---|---|---|
| Employee Management | CRUD operations for employee records | `employee.php`, `add_emp.php`, `edit_emp.php` |
| Payroll Calculation | Salary computation, deductions, net pay | `payroll.php`, `calculate.php`, `salary.php` |
| Leave Management | Leave requests, approvals, balance tracking | `leave.php`, `leave_request.php` |
| Report Generation | Monthly reports, payslips, summaries | `reports.php`, `generate_report.php` |
| Authentication | Login, session management | `login.php`, `session_check.php` |

#### Cross-Cutting Concerns (Improperly Mixed)
| Concern | How It Is Handled |
|---|---|
| Authentication | Inline session checks copy-pasted across every file |
| Logging | Direct `file_put_contents()` calls scattered across modules |
| Error Handling | Mix of `die()`, `echo`, and silent failures |
| Configuration | Database credentials hardcoded in `config.php` |

#### Data Layer
- Single MySQL 5.x database instance
- No ORM or data abstraction layer
- Raw SQL queries written directly inside PHP business logic files
- No stored procedures or query parameterization (SQL injection risk)
- Flat files used for log storage and report exports

---

## 5. Architectural Violations Identified

The following critical architectural violations were recovered:

### Violation 1 — No Layered Separation
Business logic, presentation, and data access are merged inside single PHP files. A single file like `payroll.php` contains HTML output, salary calculation logic, and direct SQL queries simultaneously.

### Violation 2 — God Files
Files such as `index.php` and `payroll.php` contain thousands of lines of code handling multiple unrelated responsibilities, violating the Single Responsibility Principle.

### Violation 3 — Hardcoded Configuration
Database host, username, and password are hardcoded directly in `config.php` with no environment variable support, making deployment across environments dangerous.

### Violation 4 — Duplicated Authentication Logic
Session validation code is copy-pasted across 15+ PHP files rather than implemented as a central middleware or filter.

### Violation 5 — Unparameterized SQL Queries
SQL queries are constructed using direct string concatenation with user input, creating critical SQL injection vulnerabilities throughout the system.

### Violation 6 — No API Layer
There is no REST or service layer. All data access happens through direct page-to-database calls, making integration with any external system impossible without rewriting core logic.

---

## 6. Component Dependency Analysis

```
login.php
  └── config.php (DB credentials)
  └── session_check.php

payroll.php
  └── config.php
  └── employee.php (direct include)
  └── calculate.php
  └── reports.php (direct include)
  └── [Raw MySQL queries embedded]

employee.php
  └── config.php
  └── [Raw MySQL queries embedded]
  └── leave.php (direct include)
```

Every module depends on every other module through direct file includes, creating a **circular dependency web** that makes independent testing, modification, or replacement of any single module impossible.

---

## 7. Summary of Findings

| Finding | Severity | Impact |
|---|---|---|
| No architectural layering | Critical | Cannot scale or maintain |
| SQL injection vulnerabilities | Critical | Data breach risk |
| Duplicated authentication | High | Security gaps |
| Hardcoded credentials | High | Deployment risk |
| God file anti-pattern | High | Unmaintainable codebase |
| No API layer | High | Zero integration capability |

---

## 8. Conclusion

The architecture recovery process reveals a system built without architectural planning, following procedural programming patterns typical of early 2000s PHP development. The system suffers from critical coupling issues, security vulnerabilities, and a complete absence of separation of concerns.

These findings directly motivate the re-engineering and modernization strategy presented in the subsequent phases of this project.

---

*End of Architecture Recovery Report*
