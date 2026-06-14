# Legacy Payroll System — Architecture Recovery & Model-Driven Re-Engineering

> **Course Project** | Software Re-Engineering | Submitted by: [Your Name] | [Your Roll No]

## Project Overview

This project presents a comprehensive architecture recovery and model-driven re-engineering study of a legacy PHP/MySQL monolithic Payroll Management System. The goal is to analyze the existing system, recover its architecture, identify design flaws, and propose a modernized cloud-ready target architecture.

---

## System Under Study

| Property | Details |
|---|---|
| System Name | Legacy Payroll Management System |
| Technology Stack | PHP 5.x, MySQL, Apache, jQuery |
| Architecture Style | Monolithic, Procedural |
| Codebase Size | ~8,000 LOC |
| Primary Problem | Tightly coupled modules, no API layer, security vulnerabilities |

---

## Project Artifacts

| Artifact | Location |
|---|---|
| Architecture Recovery Diagram | `/architecture/` |
| Code Smell Analysis | `/docs/code-smells.md` |
| Re-Engineering Plan | `/docs/reengineering-plan.md` |
| Modernization Strategy | `/docs/modernization-strategy.md` |
| Full Academic Report | `/report/` |
| Presentation Slides | `/docs/presentation.md` |

---

## Key Findings

- **6 major architectural violations** identified
- **4 critical code smells** documented
- **Monolith → Microservices** migration path proposed
- **REST API + React** target architecture designed

---

## Setup Instructions

```bash
git clone https://github.com/[your-username]/legacy-payroll-reengineering
cd legacy-payroll-reengineering
code .
```

## Report Structure

1. Introduction & Motivation
2. Legacy System Overview
3. Architecture Recovery (As-Is)
4. Code Smell & Anti-Pattern Analysis
5. Re-Engineering Techniques Applied
6. Proposed Modernization Strategy (To-Be Architecture)
7. Conclusion

---

*Submitted for academic evaluation. All analysis is original work.*