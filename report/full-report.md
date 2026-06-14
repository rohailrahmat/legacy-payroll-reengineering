# Architecture Recovery and Model-Driven Re-Engineering of Legacy Software Systems for Modernization

## Full Academic Report

---

**Student Name:** Rohail Rahmat  
**Roll Number:** 2023-KIU-BS4163  
**University:** Karakorum International University, Gilgit  
**Department:** Department of Computer Science  
**Course:** Software Re-Engineering  
**Supervisor:** Asif Hussain  
**GitHub Repository:** https://github.com/rohailrahmat/legacy-payroll-reengineering  
**Submission Date:** June 2026

---

## Abstract

Legacy software systems represent a critical challenge in modern enterprise computing. Organizations depend on these aging systems for core business functions, yet their architectural decay makes them increasingly expensive to maintain and dangerous to operate. This project presents a systematic application of architecture recovery and model-driven re-engineering techniques to a legacy PHP/MySQL Payroll Management System. Through structured reverse engineering, the original monolithic architecture was recovered and documented. Static code analysis revealed six major architectural violations including critical SQL injection vulnerabilities, a God File anti-pattern spanning 2,000 lines of code, and complete absence of layered architecture. Six code smells were identified and mapped to proven refactoring techniques from Fowler's catalog. The project proposes a comprehensive modernization strategy transforming the monolith into a five-service microservices architecture with a REST API layer, React frontend, and 28-week incremental migration roadmap using the Strangler Fig Pattern. The findings demonstrate that disciplined architecture recovery is an essential prerequisite to effective re-engineering, and that incremental migration strategies significantly reduce modernization risk compared to full system rewrites.

**Keywords:** Legacy Systems, Architecture Recovery, Re-Engineering, Code Smells, Microservices, Strangler Fig Pattern, Refactoring, Technical Debt

---

## Table of Contents

1. Introduction
2. Background and Literature Review
3. System Under Study
4. Methodology
5. Architecture Recovery (As-Is Analysis)
6. Code Smell and Anti-Pattern Analysis
7. Re-Engineering Techniques Applied
8. Proposed Modernization Strategy (To-Be Architecture)
9. Comparative Analysis
10. Conclusion
11. References

---

## 1. Introduction

### 1.1 Problem Statement

Software systems inevitably age. Technologies become obsolete, business requirements evolve, and codebases accumulate technical debt through years of maintenance by multiple developers with varying skill levels and no consistent architectural vision. The result is what the industry terms a "legacy system" — not merely old software, but old software that the organization cannot afford to replace wholesale while still depending on it for critical business operations.

The challenge is particularly acute for payroll systems. Unlike customer-facing applications where downtime is visible and commercially damaging, payroll systems operate in the background until they fail — and when they fail, employees are not paid. The consequences extend beyond technical inconvenience to legal liability, employee relations damage, and regulatory non-compliance.

This project addresses the following research questions:

1. How can the architecture of an undocumented legacy system be systematically recovered?
2. What architectural violations and code smells are most prevalent in legacy PHP systems?
3. How can proven re-engineering techniques be mapped to identified problems?
4. What modernization strategy minimizes risk while maximizing improvement?

### 1.2 Project Objectives

- Apply structured architecture recovery techniques to extract the As-Is architecture from source code
- Identify and document all code smells and architectural violations using established taxonomies
- Map each identified problem to a specific, traceable refactoring or re-engineering technique
- Design a complete To-Be architecture incorporating modern software engineering principles
- Propose a practical, risk-minimized migration roadmap

### 1.3 Scope

This project focuses on a single representative legacy system — a PHP/MySQL Payroll Management System of approximately 8,000 lines of code. The scope includes complete architecture recovery, problem identification, refactoring mapping, and modernization design. Implementation of the proposed architecture is outside the scope of this academic project.

---

## 2. Background and Literature Review

### 2.1 Legacy Systems

Lehman (1980) established that software systems undergo continuous evolution and that unless actively maintained, their complexity increases and their reliability decreases — a principle now known as Lehman's Laws of Software Evolution. Bennett and Rajlich (2000) extended this to define a lifecycle in which systems transition from active development through maintenance into a "legacy" state characterized by high maintenance cost and accumulated technical debt.

Brodie and Stonebraker (1995) provided the seminal definition of legacy systems: "Any information system that significantly resists modification and evolution." This resistance, not age alone, defines a legacy system. A system built two years ago with poor architecture may already qualify, while a well-designed 20-year-old system may not.

### 2.2 Architecture Recovery

Ducasse and Pollet (2009) define software architecture recovery as "the process of obtaining an architectural description of a software system when no such documentation exists or when existing documentation is inconsistent with the implementation." They identify three core techniques: static analysis (examining source code structure), dynamic analysis (observing runtime behavior), and scenario-based analysis (tracing specific use cases through the system).

Christl, Koschke, and Storey (2007) demonstrated that architecture recovery from PHP systems is particularly challenging due to the language's dynamic typing, prevalent use of global variables, and common patterns of mixing presentation and logic — all of which are present in the system under study.

### 2.3 Code Smells

Fowler (1999) introduced the term "code smell" to describe surface indicators of design problems. He catalogued 22 smells ranging from Duplicate Code and Long Method to more structural problems like God Class and Shotgun Surgery. Crucially, Fowler emphasized that code smells are not bugs — they do not prevent the system from functioning — but they consistently signal areas where maintenance will be disproportionately costly.

Wake (2003) extended this taxonomy, and Palomba et al. (2018) empirically demonstrated through analysis of 74 open-source systems that code smells correlate strongly with bug-prone files and with files that require disproportionate maintenance effort.

### 2.4 Re-Engineering

Chikofsky and Cross (1990) established the foundational taxonomy distinguishing reverse engineering (understanding existing systems), re-engineering (restructuring without changing external behavior), and forward engineering (creating new systems). This project applies all three: reverse engineering in Phase 1, re-engineering in Phases 2-3, and forward engineering in Phase 4.

### 2.5 Microservices Architecture

Newman (2015) defines microservices as "small, autonomous services that work together," each owning its own data and deployable independently. Lewis and Fowler (2014) identified the key characteristics: organized around business capabilities, decentralized data management, infrastructure automation, and design for failure.

Dragoni et al. (2017) conducted a systematic review of microservices literature and found consistent evidence that the pattern improves deployment frequency and fault isolation, though at the cost of increased operational complexity — a trade-off explicitly addressed in the migration strategy proposed in this project.

---

## 3. System Under Study

### 3.1 System Overview

The subject of this study is a Payroll Management System developed in PHP 5.x with a MySQL 5.x database backend. The system has been in production use for approximately 8 years and processes monthly payroll for an organization's workforce. It was originally developed by a single developer and subsequently maintained by multiple individuals without formal architectural governance.

### 3.2 Technical Profile

| Property | Details |
|---|---|
| Primary Language | PHP 5.x (Procedural) |
| Database | MySQL 5.x |
| Frontend | HTML 4 + jQuery 1.x |
| Web Server | Apache 2.x |
| Architecture Pattern | Monolithic, Procedural |
| Estimated LOC | ~8,000 |
| Test Coverage | 0% |
| API Layer | None |
| Version Control | None (FTP deployment) |
| Documentation | None |

### 3.3 Business Functions

The system manages five core business processes:

1. **Employee Management** — Registration, profile management, salary structure definition
2. **Payroll Processing** — Monthly salary calculation including base salary, allowances, overtime, and deductions
3. **Leave Management** — Leave application submission, approval workflow, balance tracking
4. **Tax Management** — Income tax calculation, annual tax summary generation
5. **Reporting** — Monthly payroll summaries, department-level cost reports, payslip generation

---

## 4. Methodology

This project follows a four-phase methodology based on the re-engineering framework of Chikofsky and Cross (1990), adapted for legacy web application modernization.

```
Phase 1: Architecture Recovery
├── Static code analysis
├── Database schema extraction  
├── Component identification
└── Dependency mapping
        │
        ▼
Phase 2: Problem Identification
├── Code smell analysis (Fowler taxonomy)
├── Architectural violation detection
└── Security vulnerability assessment
        │
        ▼
Phase 3: Re-Engineering Mapping
├── Smell-to-technique mapping
├── Refactoring application
└── Before/after code documentation
        │
        ▼
Phase 4: Modernization Design
├── To-Be architecture design
├── Technology stack selection
├── Migration roadmap (Strangler Fig)
└── Risk assessment
```

---

## 5. Architecture Recovery (As-Is Analysis)

### 5.1 Recovery Approach

Architecture recovery was performed through manual static analysis of all PHP source files. The primary technique was tracing function calls and `include`/`require` statements to construct a complete dependency graph. MySQL schema was extracted using `SHOW CREATE TABLE` to recover the data model.

### 5.2 Key Recovery Findings

The most significant finding of the recovery exercise was the complete absence of architectural boundaries. Despite exhibiting recognizable functional modules — employee management, payroll, leave, reports — none of these modules exist as separate files, classes, or namespaces. All functionality resides in a single file with no enforced separation.

**Recovered Component Map:**

| Component | Type | Location | Coupling |
|---|---|---|---|
| Employee Module | Functions | `payroll.php:1-600` | Directly coupled to all others |
| Payroll Module | Functions | `payroll.php:601-1400` | Directly coupled to all others |
| Leave Module | Functions | `payroll.php:1401-1800` | Directly coupled to all others |
| Reports Module | Functions | `payroll.php:1801-2000` | Directly coupled to all others |
| Database Layer | Global variable | `payroll.php:1` | Shared across all modules |

### 5.3 Six Architectural Violations

1. **No Layered Architecture** — Business logic, data access, and presentation mixed within individual functions
2. **God File Anti-Pattern** — All system functionality in a single 2,000-line file
3. **Absent API Layer** — No REST or any other interface; impossible to integrate externally
4. **Shared Mutable Global State** — Database connection as global variable accessible by all code
5. **Hardcoded Configuration** — Credentials and business rules embedded in source code
6. **No Authentication Architecture** — Session check copy-pasted across all functions

---

## 6. Code Smell and Anti-Pattern Analysis

Six major code smells were identified through systematic application of Fowler's (1999) taxonomy. Each is documented with source code evidence, impact analysis, and mapped refactoring technique.

| # | Smell | Severity | Primary Impact |
|---|---|---|---|
| 1 | God File / God Class | Critical | Impossible isolation of concerns |
| 2 | Raw SQL / SQL Injection | Critical | Complete data compromise possible |
| 3 | Duplicate Code | High | Inconsistent business rule application |
| 4 | Shotgun Surgery | High | Multi-file changes for single updates |
| 5 | Hardcoded Configuration | High | Credentials exposed in version control |
| 6 | Long Method | High | Untestable 400-line salary function |

Full evidence and analysis are documented in `/docs/code-smells.md`.

**Additional Findings:** During the architecture recovery and code analysis phase, a severe database query logic bug (referred to as the "Year Bleed" bug) was discovered in `payroll.php`'s overtime calculation. The query `MONTH(date) = $month` completely lacks a year filter, meaning overtime hours from prior years are erroneously summed into the current period's calculation. This finding has been incorporated into the code smell and recovery reports.

---

## 7. Re-Engineering Techniques Applied

Each identified code smell is mapped to a specific technique from Fowler's Refactoring Catalog (1999):

| Code Smell | Refactoring Technique | Outcome |
|---|---|---|
| God File | Extract Class | 2,000-line file → 5 focused service classes |
| SQL Injection | Repository Pattern + ORM | Raw SQL eliminated; parameterized queries |
| Duplicate Code | Extract Method + Middleware | Auth logic defined once, applied via decorator |
| Shotgun Surgery | Move Method + Config Service | Tax rate in one config file, read everywhere |
| Hardcoded Config | Externalize Configuration | All secrets in `.env`, never in source code |
| Long Method | Decompose Method | 400-line function → 6 small testable functions |

Before/after code examples for each technique are documented in the source files under `/src/`.

---

## 8. Proposed Modernization Strategy

### 8.1 Target Architecture

The proposed To-Be architecture decomposes the monolith into five independent microservices:

- **Employee Service** — Master data management for employee records
- **Payroll Service** — Salary calculation, payslip generation
- **Leave Service** — Leave application, approval, balance tracking
- **Report Service** — Analytics, summaries, exports
- **Notification Service** — Event-driven email and SMS delivery

An API Gateway provides a single entry point handling authentication, routing, rate limiting, and HTTPS termination. A React.js frontend replaces the jQuery-based HTML pages.

### 8.2 Migration Strategy — Strangler Fig Pattern

The Strangler Fig Pattern (Fowler, 2004) was selected because it allows the legacy system to continue operating throughout migration. New microservices are built alongside the monolith, traffic is gradually redirected, and legacy components are decommissioned only after their replacement is fully proven.

**28-Week Migration Roadmap:**

| Phase | Weeks | Activities |
|---|---|---|
| Foundation | 1-4 | Cloud infrastructure, CI/CD pipeline, API Gateway |
| Employee Service | 5-10 | Extract employee module, build REST API, migrate data |
| Payroll & Leave Services | 11-18 | Extract payroll and leave modules, RabbitMQ setup |
| React Frontend | 19-24 | New frontend connecting to all services, UAT |
| Legacy Decommission | 25-28 | Traffic cutover, data validation, PHP shutdown |

### 8.3 Security Improvements

| Vulnerability | Legacy | Modernized |
|---|---|---|
| SQL Injection | Fully vulnerable | Eliminated via ORM |
| Password Storage | Plaintext | bcrypt (cost 12) |
| Authentication | Fragile sessions | JWT with expiry |
| HTTPS | Not enforced | Enforced at Gateway |
| Input Validation | None | class-validator on all DTOs |
| Secrets Management | Source code | Environment + Vault |

---

## 9. Comparative Analysis

### 9.1 As-Is vs To-Be Summary

| Dimension | Legacy System | Modernized System |
|---|---|---|
| Architecture | Monolith | Microservices |
| Language | PHP 5.x Procedural | Node.js + TypeScript |
| Database | MySQL 5.x (shared) | PostgreSQL 14 (per service) |
| Frontend | HTML + jQuery | React.js |
| Security | Critical vulnerabilities | Industry standard |
| Deployment | Manual FTP | Docker + Kubernetes |
| Testing | 0% | >80% target coverage |
| API Layer | None | Complete REST API |
| Mobile Support | Impossible | Full API available |
| Availability | Single point of failure | 99.9% uptime |

### 9.2 Risk Assessment

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Data loss during migration | Low | Critical | Full backups, parallel operation |
| Service integration failures | Medium | High | Contract testing, staged rollout |
| Team learning curve | High | Medium | Training plan, phased adoption |
| Timeline overrun | Medium | Medium | Buffer built into 28-week plan |

---

## 10. Conclusion

This project successfully demonstrated the complete re-engineering lifecycle applied to a production legacy system. Four primary conclusions emerge from the work:

**Architecture recovery is non-negotiable.** The system's architectural problems were not visible from functional observation — the system processed payroll correctly. Only systematic architecture recovery revealed the depth of structural decay. Attempting re-engineering without this foundation would be analogous to renovating a building without first understanding its structural condition.

**Code smells are diagnostic tools, not the diagnosis.** Each of the six code smells identified was a symptom. The root cause was consistent: the original development lacked any architectural discipline. This reinforces the importance of establishing and enforcing architectural standards from the beginning of any software project.

**Incremental migration is the professional standard.** The Strangler Fig Pattern reduces modernization risk to manageable levels. The alternative — a full rewrite — carries the greatest risk in software engineering: building a new system that must replicate all the behavior of an existing system while the organization depends on the existing system for payroll operations.

**Re-engineering is a science.** Fowler's refactoring catalog, Newman's microservices patterns, and the Strangler Fig migration approach are named, proven, peer-validated techniques. This project demonstrates that legacy modernization need not be a creative art — it can and should be a disciplined, systematic engineering activity.

The complete project artifacts — source code, architecture diagrams, analysis documents, and this report — are publicly available at:  
**https://github.com/rohailrahmat/legacy-payroll-reengineering**

---

## 11. References

1. Bennett, K.H., & Rajlich, V.T. (2000). Software maintenance and evolution: A roadmap. *Proceedings of the Conference on The Future of Software Engineering.* ACM.

2. Brodie, M.L., & Stonebraker, M. (1995). *Migrating Legacy Systems.* Morgan Kaufmann.

3. Chikofsky, E.J., & Cross, J.H. (1990). Reverse engineering and design recovery: A taxonomy. *IEEE Software, 7*(1), 13–17.

4. Christl, A., Koschke, R., & Storey, M.A. (2007). Automated clustering to support the reflexion method. *Information and Software Technology, 49*(3), 255–274.

5. Dragoni, N., et al. (2017). Microservices: Yesterday, today, and tomorrow. *Present and Ulterior Software Engineering.* Springer.

6. Ducasse, S., & Pollet, D. (2009). Software architecture reconstruction: A process-oriented taxonomy. *IEEE Transactions on Software Engineering, 35*(4), 573–591.

7. Evans, E. (2003). *Domain-Driven Design: Tackling Complexity in the Heart of Software.* Addison-Wesley.

8. Fowler, M. (1999). *Refactoring: Improving the Design of Existing Code.* Addison-Wesley.

9. Fowler, M. (2004). Strangler Fig Application. martinfowler.com.

10. Lehman, M.M. (1980). Programs, life cycles, and laws of software evolution. *Proceedings of the IEEE, 68*(9), 1060–1076.

11. Lewis, J., & Fowler, M. (2014). Microservices. martinfowler.com.

12. Martin, R.C. (2008). *Clean Code: A Handbook of Agile Software Craftsmanship.* Prentice Hall.

13. Newman, S. (2015). *Building Microservices: Designing Fine-Grained Systems.* O'Reilly Media.

14. OWASP Foundation. (2021). *OWASP Top 10.* Retrieved from https://owasp.org/Top10/

15. Palomba, F., et al. (2018). Diffuseness and impact of code smells: A large-scale empirical investigation. *Empirical Software Engineering, 23*(3), 1188–1221.

---

*Submitted for academic evaluation by Rohail Rahmat (2023-KIU-BS4163)*  
*Karakorum International University, Gilgit | Supervisor: Asif Hussain*  
*© 2026 — All analysis is original work*
