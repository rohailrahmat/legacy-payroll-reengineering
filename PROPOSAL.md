# Project Proposal: Monolithic Legacy Payroll System Re-Engineering & Modernization

---

<div align="right">
  <strong>Course:</strong> Software Re-Engineering (CSE-312)<br>
  <strong>Semester:</strong> 6th Semester (BS Computer Science)<br>
  <strong>University:</strong> Karakorum International University, Gilgit<br>
  <strong>Supervisor:</strong> Instructor Asif Hussain<br>
  <strong>Submission Date:</strong> June 2026
</div>

### Project Group Members (Group-04)

| S. No | Student Name | Registration / Roll Number | Role / Domain |
|:---:|:---|:---|:---|
| 1 | **Rohail Rahmat** | 2023-KIU-BS4163 | Team Lead / Architecture Recovery & Logic Design |
| 2 | **Wasim Ali** | 2023-KIU-BS4188 | Frontend Architect / Interface Modernization |
| 3 | **Akhlaq Hussein** | 2023-KIU-BS4116 | Backend Developer / Database Refactoring & ORM |
| 4 | **Yawar Abbas** | 2023-KIU-BS4189 | Security Analyst / Vulnerability Auditing & Gateway |
| 5 | **Basit Ali** | 2023-KIU-BS4651 | QA Engineer / Test Suite & CI/CD Pipelines |

---

## 1. Executive Summary

This proposal outlines a systematic plan to reverse-engineer, refactor, and modernize a mission-critical but technologically obsolete monolithic Payroll Management System. In modern enterprise computing, legacy systems represent a double-edged sword: they host vital business logic and employee workflows, yet their structural decay renders them highly vulnerable to security breaches, operational failures, and astronomical maintenance costs.

Our group proposes a structured, model-driven modernization approach. We will:
1. **Analyze and recover** the undocumented monolithic architecture of the legacy system (PHP 5.x/MySQL).
2. **Identify and document** critical code smells, logic bugs (such as the "Year Bleed" bug), and vulnerability paths.
3. **Map and model** refactoring remedies using Martin Fowler’s industry-standard catalog.
4. **Design and architect** a modern, secure, and cloud-native microservices platform using Node.js, TypeScript, PostgreSQL, and React.
5. **Develop a transition plan** using the **Strangler Fig Pattern** to migrate operations incrementally without system downtime.

---

## 2. Problem Statement

The system under study is a legacy Payroll Management System developed in procedural PHP 5.x and MySQL 5.x. Having been maintained without architectural governance for over eight years, the codebase (~8,000 LOC) has accumulated significant technical debt, manifesting in the following critical issues:

* **The "God File" Anti-Pattern:** A single procedural file (`payroll.php`) orchestrates all concerns—including employee database CRUD, payroll calculations, leave tracking, PDF reporting, and direct HTML rendering. This violates the Single Responsibility Principle, leading to extreme code coupling.
* **SQL Injection Vulnerabilities:** User inputs are concatenated directly into raw database query strings. An attacker can easily exploit these endpoints to extract sensitive salary details or drop database tables.
* **The "Year Bleed" Overtime Logic Bug:** The legacy overtime aggregation script filters records solely by month (e.g., `MONTH(date) = 6`), omitting the year constraint. Over time, this leads to historical overtime hours from previous years compounding into current salary payments, causing silent and progressive financial leakages.
* **Hardcoded Configurations & Credentials:** Production database root passwords, API credentials, and tax/deduction rates are stored directly in plaintext within version-controlled files.
* **Lack of API & Test Automation:** The monolithic system does not expose a REST API layer, preventing integration with third-party HR systems or mobile applications. Additionally, with **0% test coverage**, any code modifications are highly risky.

---

## 3. Project Objectives

The primary objectives of this re-engineering project are to transition the legacy monolith into a robust, secure, and evolvable software architecture:

1. **Architecture Recovery (As-Is State):** Perform static analysis and schema reverse engineering to reconstruct the undocumented monolithic design, mapping database dependencies and logical boundaries.
2. **Technical Debt Auditing:** Document and catalog all structural code smells and security risks using Martin Fowler’s refactoring taxonomy.
3. **Model-Driven Refactoring:** Modernize legacy calculations into type-safe, modular TypeScript classes. This includes resolving the "Year Bleed" bug and parameterizing all queries using TypeORM.
4. **Pluggable Policy Design:** Utilize the **Strategy Design Pattern** to isolate changing business rules (e.g., tax brackets, provident fund models, and allowance slabs) into clean, pluggable algorithms.
5. **Target Microservices Design (To-Be State):** Architect a decoupled microservices topology consisting of five focused services (Employee, Payroll, Leave, Notification, and Report Services) coordinated via an Nginx API Gateway and RabbitMQ event broker.
6. **Incremental Migration Planning:** Draft a 28-week phased migration roadmap using the **Strangler Fig Pattern** to ensure continuous business operations throughout deployment.

---

## 4. Proposed Target Architecture (To-Be)

The proposed target system moves away from a single database and a single server. It leverages a modern cloud-native stack:

* **Frontend:** A component-driven, responsive **React.js** single-page application.
* **API Gateway:** An **Nginx API Gateway** serving as a single entry point, handling JWT-based stateless authentication, rate-limiting, SSL termination, and service routing.
* **Microservices (Node.js + NestJS):** 
  * **Employee Service:** Single source of truth for staff records.
  * **Payroll Service:** Handles slab-based progressive calculations and JSON pay slips.
  * **Leave Service:** Manages request registrations and approvals.
  * **Report Service:** Conducts annual aggregates and exports.
  * **Notification Service:** Listens to event-driven queues (RabbitMQ) to dispatch payslips via email.
* **Data Tier:** Isolated **PostgreSQL** instances for each service, eliminating shared database state coupling.

---

## 5. Implementation & Migration Methodology

To eliminate the high risks associated with a "Big Bang" system migration, we will apply the **Strangler Fig Pattern** across a **28-week roadmap**:

```
┌─────────────────────────────────────────────────────────────────────────┐
│                          28-WEEK MIGRATION PATH                         │
├─────────────┬───────────────────────────────────────────────────────────┤
│ Weeks 1-4   │ Cloud Infrastructure setup (PostgreSQL, Docker, Gateway)  │
├─────────────┼───────────────────────────────────────────────────────────┤
│ Weeks 5-10  │ Extraction and launch of the modern Employee Service      │
├─────────────┼───────────────────────────────────────────────────────────┤
│ Weeks 11-18 │ Deployment of Payroll and Leave Services; RabbitMQ setup  │
├─────────────┼───────────────────────────────────────────────────────────┤
│ Weeks 19-24 │ Launch React Frontend; parallel testing for UAT validation│
├─────────────┼───────────────────────────────────────────────────────────┤
│ Weeks 25-28 │ Complete decommissioning of the legacy monolithic PHP server│
└─────────────┴───────────────────────────────────────────────────────────┘
```

---

## 6. Key Deliverables

Upon completion, our group will deliver the following artifacts to the department:

1. **Re-Engineering Showcase Dashboard:** An interactive HTML5 dashboard containing:
   * A 15-slide presentation deck detailing findings.
   * A side-by-side legacy vs. refactored code smell explorer.
   * A timeline simulator animating the Strangler Fig migration stages.
   * A live payroll sandbox executing procedural PHP logs side-by-side with secure TypeORM parameters.
   * An integrated academic report reader.
2. **Modernized Source Codebase:** A clean, compiled TypeScript backend matching the NestJS structure and a React.js client interface.
3. **Comprehensive Re-Engineering Report:** A detailed academic thesis detailing static analysis outputs, reverse-engineered schema graphs, and comparative metrics.
4. **Docker Container Manifests:** Ready-to-deploy docker-compose files representing the complete microservices topology.

---

## 7. Signatures & Approvals

We submit this proposal for academic evaluation, review, and supervisor approval.

\
**Submitted by:**

*____________________* (Rohail Rahmat)  
*____________________* (Wasim Ali)  
*____________________* (Akhlaq Hussein)  
*____________________* (Yawar Abbas)  
*____________________* (Basit Ali)  

\
**Supervisor Review & Approval:**

*____________________*  
**Instructor Asif Hussain**  
Department of Computer Science  
Karakorum International University, Gilgit  
