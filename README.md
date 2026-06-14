# Legacy Payroll System — Architecture Recovery & Model-Driven Re-Engineering

> **Course Project** | Software Re-Engineering | Submitted by: **Rohail Rahmat** | **2023-KIU-BS4163**  
> **University:** Karakorum International University, Gilgit | **Supervisor:** Asif Hussain

---

## Project Overview

This project presents a comprehensive architecture recovery and model-driven re-engineering study of a legacy PHP/MySQL monolithic Payroll Management System. The goal is to analyze the existing system, recover its hidden architecture, identify design flaws and code smells, and propose a fully modernized cloud-ready target architecture using microservices.

---

## System Under Study

| Property | Details |
|---|---|
| System Name | Legacy Payroll Management System |
| Technology Stack | PHP 5.x, MySQL 5.x, Apache, jQuery |
| Architecture Style | Monolithic, Procedural |
| Codebase Size | ~8,000 Lines of Code |
| Test Coverage | 0% |
| API Layer | None |
| Primary Problems | Tightly coupled modules, no API layer, SQL injection vulnerabilities, zero separation of concerns |

---

## Student Information

| Field | Details |
|---|---|
| Student Name | Rohail Rahmat |
| Roll Number | 2023-KIU-BS4163 |
| University | Karakorum International University, Gilgit |
| Supervisor | Asif Hussain |
| GitHub | [@rohailrahmat](https://github.com/rohailrahmat) |

---

## Project Artifacts

| Artifact | Location | Description |
|---|---|---|
| Architecture Recovery Report | `/architecture/architecture-recovery.md` | Full As-Is architecture analysis |
| As-Is Architecture Diagram | `/diagrams/as-is-architecture.md` | Visual component & dependency map |
| Code Smell Analysis | `/docs/code-smells.md` | 6 smells with code evidence & fixes |
| Re-Engineering Plan | `/docs/reengineering-plan.md` | Refactoring techniques applied |
| Modernization Strategy | `/docs/modernization-strategy.md` | To-Be microservices architecture |
| Legacy Source Code Sample | `/src/` | Representative legacy PHP code |
| Full Academic Report | `/report/full-report.md` | Complete academic submission |
| Presentation Slides | `/docs/presentation-slides.md` | Speaker notes for final presentation |

---

## Key Findings

- **6 major architectural violations** identified through static analysis and reverse engineering
- **6 critical code smells** documented with real PHP code evidence
- **2 critical security vulnerabilities** (SQL Injection + hardcoded credentials)
- **Monolith → Microservices** migration path proposed using Strangler Fig Pattern
- **REST API + React** target architecture designed with full 28-week roadmap

---

## Setup Instructions

```bash
git clone https://github.com/rohailrahmat/legacy-payroll-reengineering
cd legacy-payroll-reengineering
# Open in VS Code
code .
```

---

## Report Structure

1. Introduction & Motivation
2. Legacy System Overview
3. Architecture Recovery (As-Is)
4. Code Smell & Anti-Pattern Analysis
5. Re-Engineering Techniques Applied
6. Proposed Modernization Strategy (To-Be Architecture)
7. Conclusion & References

---

## Technology Comparison

| Dimension | Legacy (As-Is) | Modern (To-Be) |
|---|---|---|
| Architecture | Monolith | Microservices |
| Language | PHP 5.x Procedural | Node.js + TypeScript |
| Frontend | HTML + jQuery | React.js |
| Database | MySQL 5.x (shared) | PostgreSQL (per service) |
| Security | SQL injection, no HTTPS | JWT, bcrypt, HTTPS enforced |
| Deployment | Manual FTP upload | Docker + Kubernetes + CI/CD |
| Testing | 0% coverage | Unit + Integration + E2E |
| API | None | Complete REST API |

---

*This project is submitted for academic evaluation. All analysis, diagrams, and documentation are original work.*  
*© 2024 Rohail Rahmat | Karakorum International University, Gilgit*
