# Presentation Slides & Speaker Notes
## Architecture Recovery and Model-Driven Re-Engineering of Legacy Software Systems

**Presenter:** Rohail Rahmat | **Roll No:** 2023-KIU-BS4163  
**University:** Karakorum International University, Gilgit  
**Supervisor:** Asif Hussain  
**Duration:** 15–20 minutes

---

## SLIDE 1 — Title Slide

**Title:**
> Architecture Recovery and Model-Driven Re-Engineering of Legacy Software Systems for Modernization

**Subtitle:**
> A Case Study: Legacy PHP/MySQL Payroll Management System

**Rohail Rahmat | 2023-KIU-BS4163 | Karakorum International University, Gilgit**

---
**🎤 Speaker Notes:**
"Good [morning/afternoon] everyone. My name is Rohail Rahmat, roll number 2023-KIU-BS4163. Today I will present my semester project on architecture recovery and model-driven re-engineering. I selected a legacy payroll management system as the case study and applied a complete four-phase re-engineering methodology — from recovering the hidden architecture all the way to designing a modern cloud-native replacement. Let me walk you through what I found and what I propose."

---

## SLIDE 2 — The Problem

**Heading:** Why Legacy Systems Become a Crisis

- 🔴 Built years ago — the technology stack is now obsolete
- 🔴 Business-critical functions depend on them — they cannot simply be switched off
- 🔴 Every change is expensive, risky, and feared by developers
- 🔴 Security vulnerabilities accumulate silently over years

**Quote:**
> *"Any information system that significantly resists modification and evolution."*
> — Brodie & Stonebraker, 1995 (defining a legacy system)

---
**🎤 Speaker Notes:**
"Legacy systems are not just old software — they are old software that the organization cannot live without. The payroll system I studied processes salaries every month. But it also contains critical security holes that could expose every employee's financial data. And because it has no architecture, every maintenance task risks breaking something else. This tension between business dependency and technical decay is exactly what re-engineering addresses."

---

## SLIDE 3 — System Under Study

**Heading:** The Legacy Payroll System — Technical Profile

| Property | Details |
|---|---|
| Language | PHP 5.x — Procedural |
| Database | MySQL 5.x |
| Frontend | HTML + jQuery 1.x |
| Architecture | Monolithic |
| Codebase | ~8,000 Lines of Code |
| Test Coverage | **0%** |
| API Layer | **None** |
| Version Control | **None** |
| Security | **Critical vulnerabilities** |

---
**🎤 Speaker Notes:**
"Here is the technical profile of the system. PHP 5.x, MySQL, HTML with jQuery. No tests, no API, no version control, and critical security vulnerabilities. It manages employee records, calculates salaries, handles leave — all essential business functions — yet it has zero test coverage. This means every deployment is a gamble. This is not an unusual situation. This is typical of legacy systems across industries."

---

## SLIDE 4 — Methodology

**Heading:** Four-Phase Re-Engineering Methodology

```
Phase 1           Phase 2           Phase 3           Phase 4
──────────        ──────────        ──────────        ──────────
Architecture  →   Problem      →    Re-Engineering →  Modernization
Recovery          Identification    Mapping           Strategy

Reverse Eng.      Code Smells       Refactoring       To-Be Design
```

---
**🎤 Speaker Notes:**
"I followed a structured four-phase methodology. Phase 1 is architecture recovery — I reverse engineered the system to understand what it actually does and how it is structured. Phase 2 is problem identification — I systematically catalogued every code smell and architectural violation. Phase 3 is re-engineering mapping — I matched each problem to a specific proven solution. Phase 4 is modernization design — I designed the complete target architecture. Each phase feeds the next."

---

## SLIDE 5 — Phase 1: What I Found

**Heading:** Architecture Recovery — Key Findings

**Left column:**
**Techniques Used:**
- Static code analysis
- Database schema reverse engineering
- Component identification
- Dependency mapping

**Right column:**
**Shocking Findings:**
- All 6 functional modules in ONE file
- Zero enforced module boundaries
- Every module couples to every other
- Single shared database connection (global variable)

---
**🎤 Speaker Notes:**
"When I recovered the architecture, the most striking finding was simple: there is no architecture. Despite having recognizable functions — employee management, payroll, leave, reports — none of these exist as separate modules with defined boundaries. Everything is in one PHP file, 2,000 lines long, with every function able to call every other function directly. The architecture diagram you are about to see shows this."

---

## SLIDE 6 — The As-Is Architecture

**Heading:** As-Is Architecture — The Monolithic Reality

```
┌─────────────────────────────────────┐
│         payroll.php                 │
│  ┌──────────┐  ┌──────────┐         │
│  │ Employee │◄►│ Payroll  │         │
│  └────┬─────┘  └────┬─────┘         │
│       │              │              │
│  ┌────▼─────┐  ┌─────▼────┐         │
│  │  Leave   │◄►│ Reports  │         │
│  └──────────┘  └──────────┘         │
│         Global $conn                │
└──────────────┬──────────────────────┘
               │
               ▼
          MySQL Database
```
*All modules in one file. All coupled to each other. Zero separation.*

---
**🎤 Speaker Notes:**
"This is the recovered architecture. One file containing everything. The arrows represent direct coupling — the payroll module directly calls employee functions, leave functions call payroll functions, all of them share one global database connection. A bug in leave management can corrupt payroll. A database error anywhere brings down everything. This is what technical debt looks like architecturally."

---

## SLIDE 7 — Six Code Smells Found

**Heading:** Phase 2 — Code Smell Analysis Results

| # | Code Smell | Severity | Location |
|---|---|---|---|
| 1 | God File / God Class | 🔴 Critical | payroll.php — 2,000+ lines |
| 2 | Raw SQL + SQL Injection | 🔴 Critical | All database functions |
| 3 | Duplicate Code | 🟠 High | 15+ functions — copy-pasted auth |
| 4 | Shotgun Surgery | 🟠 High | Tax rate hardcoded in 4 files |
| 5 | Hardcoded Configuration | 🟠 High | DB password in source code |
| 6 | Long Method | 🟠 High | calculate_salary() — 400 lines |

---
**🎤 Speaker Notes:**
"Using Fowler's code smell taxonomy from his 1999 book Refactoring, I identified six major problems. Two are critical severity — the God File where one file does everything, and SQL Injection where an attacker can destroy the entire database. The remaining four are high severity and together create a system that is nearly impossible to maintain safely. Let me show you the most dangerous one."

---

## SLIDE 8 — The Critical Vulnerability

**Heading:** SQL Injection — A Live Security Crisis

**❌ VULNERABLE — Current legacy code:**
```php
$id = $_GET['employee_id'];  // Unvalidated user input
$query = "SELECT * FROM employees WHERE id = " . $id;
// Attacker types: 1; DROP TABLE employees; --
// Database executes: destroys all employee records
```

**✅ SECURE — After re-engineering:**
```php
$stmt = $pdo->prepare(
    "SELECT * FROM employees WHERE id = :id"
);
$stmt->execute(['id' => $id]);
// User input never touches the query — injection impossible
```

---
**🎤 Speaker Notes:**
"This is the most alarming finding. The system builds SQL queries by joining user input directly into the query string. An attacker does not need to hack anything — they type a malicious value in the browser URL bar. The system willingly executes whatever they provide. The fix requires changing exactly two lines of code using a parameterized query. That two-line fix completely eliminates this entire class of vulnerability. The fact that this exists in a production payroll system is a serious risk."

---

## SLIDE 8A — Legacy Calculation Logic Bug

**Heading:** Legacy Logic Flaw — The "Year Bleed" Bug

- ❌ **The Legacy Code Query (`payroll.php`):**
  ```php
  $query3 = "SELECT SUM(overtime_hours) as total FROM overtime
             WHERE employee_id = " . $employee_id . "
             AND MONTH(date) = " . $month;
  // MISSING Year filter: AND YEAR(date) = $year
  ```
- ⚠️ **The Issue:** Overtime hours from the same month of *all previous years* are added together.
- 🔴 **Impact:** Payroll calculations become increasingly inflated and incorrect every year the database grows.
- ✅ **The Fix (TypeScript):**
  ```typescript
  WHERE employee_id = $1 
  AND EXTRACT(MONTH FROM date) = $2 
  AND EXTRACT(YEAR FROM date) = $3
  ```

---
**🎤 Speaker Notes:**
"Beyond standard code smells and security vulnerabilities, my recovery analysis uncovered a severe logical bug embedded in the legacy payroll calculation. When summing overtime hours, the legacy code filters by month but completely ignores the year. In a production environment spanning multiple years, an employee calculating salary for June 2026 would automatically have their overtime hours from June 2025 and 2024 added as well. This causes silent, compounding financial losses. Our refactored TypeScript code solves this by explicitly extracting and matching both the month and the year."

---


## SLIDE 9 — Re-Engineering Mapping

**Heading:** Phase 3 — Every Problem Has a Proven Solution

| Code Smell | Technique Applied | Source |
|---|---|---|
| God File | Extract Class → 5 Service Classes | Fowler, 1999 |
| SQL Injection | Repository Pattern + ORM | Fowler, 1999 |
| Duplicate Code | Extract Method + Middleware | Fowler, 1999 |
| Shotgun Surgery | Move Method + Config Service | Fowler, 1999 |
| Hardcoded Config | Externalize Configuration | 12-Factor App |
| Long Method | Decompose Method | Fowler, 1999 |

---
**🎤 Speaker Notes:**
"For every problem I identified, I applied a specific named technique from Fowler's refactoring catalog. These are not my inventions — they are proven, peer-validated solutions with decades of industry use. What I demonstrated is that re-engineering is a science, not guesswork. You identify the smell, you look up the technique, you apply it. The before-and-after code for each of these transformations is documented in the repository."

---

## SLIDE 10 — The To-Be Architecture

**Heading:** Phase 4 — Proposed Microservices Architecture

```
        React Frontend
              │
              ▼
         API Gateway (Auth, Routing, HTTPS)
         ┌────┴────┬────────┬────────┐
         ▼         ▼        ▼        ▼
    Employee   Payroll   Leave   Reports
    Service    Service  Service  Service
       │          │        │        │
    Own DB     Own DB   Own DB   Own DB
                  │
              RabbitMQ
                  │
           Notification
             Service
```

---
**🎤 Speaker Notes:**
"The proposed To-Be architecture decomposes the monolith into five independent microservices. An API Gateway handles all security at the single entry point — authentication, rate limiting, HTTPS. Each service has its own dedicated database, so a failure or data corruption in one service cannot affect any other. RabbitMQ handles asynchronous communication so that when payroll is processed, the notification service automatically emails payslips without the payroll service needing to know anything about email."

---

## SLIDE 11 — Migration Strategy

**Heading:** How We Get There — 28-Week Strangler Fig Pattern

```
Weeks 1-4    Weeks 5-10   Weeks 11-18  Weeks 19-24  Weeks 25-28
─────────    ──────────   ───────────  ───────────  ───────────
Foundation   Employee     Payroll &    React        Legacy
             Service      Leave        Frontend     Decommission
Cloud, CI/CD Extract +    Services     Build + UAT  Cutover + ✓
API Gateway  API built    Built        Testing      Done
```

**Key Principle:** The old system keeps running until the new one is fully proven. Zero downtime.

---
**🎤 Speaker Notes:**
"The Strangler Fig Pattern is named after a vine that grows around a tree, gradually replacing it while the tree continues to stand. We never switch off the legacy system during migration. Over 28 weeks, we replace one component at a time. Employees continue to receive their salaries throughout. The legacy PHP system only stops when every feature has been fully migrated, tested, and validated in the new system. This is the responsible approach — it eliminates the biggest risk in modernization."

---

## SLIDE 12 — Complete Transformation

**Heading:** As-Is vs To-Be — The Full Picture

| Dimension | Legacy | Modern |
|---|---|---|
| Architecture | Monolith | Microservices |
| Security | SQL injection vulnerable | JWT + bcrypt + parameterized queries |
| Deployment | Manual FTP | Docker + Kubernetes + CI/CD |
| Testing | 0% coverage | >80% coverage |
| API | None | Complete REST API |
| Mobile Support | Impossible | Full API available |
| Availability | Single point of failure | 99.9% uptime |
| Scalability | None | Per-service auto-scaling |

---
**🎤 Speaker Notes:**
"Every dimension improves. Security goes from critical vulnerabilities to industry standard. Deployment goes from manual FTP to fully automated. Testing goes from zero to comprehensive. The system gains a complete API that makes mobile apps and third-party integration possible. And critically, availability goes from a single point of failure — if this one PHP file has a bug, nobody gets paid — to 99.9% uptime through Kubernetes health management and automatic restarts."

---

## SLIDE 13 — Key Learnings

**Heading:** What This Project Taught Me

1. **Recover before you re-engineer** — you cannot fix what you have not understood and mapped

2. **Code smells are symptoms, not the disease** — the root cause is always absence of architectural discipline from day one

3. **Incremental migration beats big rewrites** — the Strangler Fig Pattern eliminates the biggest risk in legacy modernization

4. **Every problem has a named solution** — Fowler's catalog proves that re-engineering is engineering, not art

---
**🎤 Speaker Notes:**
"These four learnings are the most transferable lessons from this project. The one that surprised me most was the third. Before this project I might have said: just rewrite everything from scratch. But a big rewrite is the highest-risk move in software engineering — you must perfectly replicate the behavior of a system you are simultaneously trying to replace. The Strangler Fig Pattern achieves the same outcome with a fraction of the risk. This is why experienced engineers choose it."

---

## SLIDE 14 — Summary

**Heading:** What Was Accomplished

| Phase | Deliverable |
|---|---|
| Architecture Recovery | Complete As-Is documentation, 6 violations identified |
| Code Smell Analysis | 6 smells with code evidence, 2 critical security vulnerabilities |
| Re-Engineering Mapping | Each smell mapped to Fowler technique with before/after code |
| Modernization Strategy | 5-service microservices design + 28-week migration roadmap |

> *"Disciplined re-engineering — not risky rewrites — is the professional path from legacy to modern."*

---
**🎤 Speaker Notes:**
"In conclusion: this project applied a complete, structured re-engineering methodology to a real production legacy system. I recovered an architecture that did not exist in any document. I identified every major problem using established taxonomies. I mapped each problem to a proven solution. And I designed a practical modernization path that minimizes risk. The complete repository is publicly available on GitHub with all artifacts organized for review."

---

## SLIDE 15 — Thank You

**Heading:** Thank You — Questions Welcome

**GitHub Repository:**
```
https://github.com/rohailrahmat/legacy-payroll-reengineering
```

**Repository Contains:**
- `/src/` — Legacy PHP code + refactored TypeScript comparison
- `/architecture/` — Full architecture recovery report
- `/docs/` — Code smell analysis + modernization strategy
- `/report/` — Complete academic report
- `/docs/presentation-slides.md` — This presentation

**Key References:**
- Fowler, M. (1999). *Refactoring.* Addison-Wesley.
- Newman, S. (2015). *Building Microservices.* O'Reilly.
- OWASP Top 10 (2021). owasp.org

---

## ANTICIPATED PROFESSOR QUESTIONS

**Q: Why microservices and not just clean up the monolith?**
A: Cleaning the monolith removes code smells but does not solve scalability, independent deployment, or technology lock-in. Microservices address architectural problems that refactoring alone cannot fix. The God File can be split into better-organized PHP classes — but you still cannot scale just the payroll processing independently of employee management.

**Q: Is the Strangler Fig Pattern realistic for a small organization?**
A: Yes — it is specifically designed for organizations that cannot afford downtime. The phased approach means the organization continues payroll operations throughout. Cost is controlled by doing one service at a time. It was used by Netflix, Amazon, and ThoughtWorks with teams of all sizes.

**Q: Why Node.js and not PHP for the new system?**
A: Node.js provides non-blocking I/O ideal for API services, TypeScript adds type safety that PHP 5.x lacks entirely, and sharing JavaScript with the React frontend means developers can work across both layers. PHP 8.x could work, but carries the cultural risk of reverting to old patterns.

**Q: What happens to existing payroll data during migration?**
A: Database migration scripts are written for each phase. Data is migrated from MySQL to PostgreSQL with full validation before any legacy component is retired. The old database remains live and read-accessible throughout migration as the fallback.

**Q: What if the 28-week timeline slips?**
A: The Strangler Fig Pattern's biggest advantage is that timeline slippage does not cause a crisis — the legacy system continues running. A delay in Week 10 simply means the legacy employee module runs longer. The risk profile is fundamentally different from a big-bang rewrite where a delay means nothing works.

---

*Rohail Rahmat | 2023-KIU-BS4163 | Karakorum International University, Gilgit*
