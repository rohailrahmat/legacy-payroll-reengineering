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

## Student & Group Information

| Field | Details |
|---|---|
| Group Number | Group-04 |
| Semester | 6th Semester (BS Computer Science) |
| University | Karakorum International University, Gilgit |
| Supervisor | Instructor Asif Hussain |
| Group Members | 1. **Rohail Rahmat** (2023-KIU-BS4163)<br>2. **Wasim Ali** (2023-KIU-BS4188)<br>3. **Akhlaq Hussein** (2023-KIU-BS4116)<br>4. **Yawar Abbas** (2023-KIU-BS4189)<br>5. **Basit Ali** (2023-KIU-BS4651) |
| GitHub | [GitHub Repo](https://github.com/rohailrahmat/legacy-payroll-reengineering) |

---

## Project Artifacts

| Artifact | Location | Description |
|---|---|---|
| **Interactive Showcase Dashboard** | [`/index.html`](file:///c:/Users/rohai/Downloads/legacy-payroll-reengineering/index.html) | **Live HTML5 application** containing slides, code comparison, strangler fig visualizer, live payroll sandbox, and report viewer |
| Project Proposal | [`/PROPOSAL.md`](file:///c:/Users/rohai/Downloads/legacy-payroll-reengineering/PROPOSAL.md) | Official software re-engineering project proposal |
| Architecture Recovery Report | [`/architecture/architecture-recovery.md`](file:///c:/Users/rohai/Downloads/legacy-payroll-reengineering/architecture/architecture-recovery.md) | Full As-Is architecture analysis |
| Code Smell & Refactoring Analysis | [`/docs/code-smells.md`](file:///c:/Users/rohai/Downloads/legacy-payroll-reengineering/docs/code-smells.md) | 6 smells and mapped Fowler refactoring remedies |
| Modernization Strategy | [`/docs/modernization-strategy.md`](file:///c:/Users/rohai/Downloads/legacy-payroll-reengineering/docs/modernization-strategy.md) | To-Be microservices architecture |
| System Architecture Diagrams | [`/diagrams/`](file:///c:/Users/rohai/Downloads/legacy-payroll-reengineering/diagrams/) | As-Is, To-Be, and Strangler Fig roadmap diagrams |
| Legacy Source Code Sample | [`/src/`](file:///c:/Users/rohai/Downloads/legacy-payroll-reengineering/src/) | Representative legacy PHP code and refactored TypeScript |
| Full Academic Report | [`/report/full-report.md`](file:///c:/Users/rohai/Downloads/legacy-payroll-reengineering/report/full-report.md) | Complete academic submission |
| Presentation Slides | [`/docs/presentation-slides.md`](file:///c:/Users/rohai/Downloads/legacy-payroll-reengineering/docs/presentation-slides.md) | Speaker notes for final presentation |

---

## Interactive Showcase Dashboard

The repository includes a single-page interactive showcase dashboard ([`index.html`](file:///c:/Users/rohai/Downloads/legacy-payroll-reengineering/index.html)) designed for final presentations and grading evaluation. 

### Key Features
1. **Presentation Slides Deck**: A fully integrated slide viewer of slides 1–15 with speaker notes toggling and keyboard navigation.
2. **Code Smell side-by-side comparison**: View the legacy PHP smells side-by-side with refactored NestJS/TypeScript code blocks, highlighting specific violations.
3. **Strangler Fig Simulator**: An interactive slider animating week-by-week (0 to 28) component migration.
4. **Payroll Sandbox**: Side-by-side payroll calculator executing legacy procedural PHP calculations (simulating the "Year Bleed" bug and SQL Injection risk warning) vs modernized NestJS TypeORM parameterized service logic.
5. **Academic Report Reader**: A clean typography layout of the full report with sidebar TOC navigation.

---

## Run and Setup Instructions

### Local Execution (No Server Needed)
Simply open the [`index.html`](file:///c:/Users/rohai/Downloads/legacy-payroll-reengineering/index.html) file directly in any modern browser. Because all report text, presentation slides, code blocks, and simulation libraries are fully self-contained in the file, there are **no CORS issues** when running from the `file://` protocol.

```bash
# Double-click index.html or open via terminal:
# Windows (PowerShell)
Start-Process "index.html"
```

### GitHub Pages Hosting (For Working Public URL)
To deploy this project to GitHub Pages:
1. Push this repository to your GitHub account: `https://github.com/rohailrahmat/legacy-payroll-reengineering`.
2. Go to **Settings** under your GitHub repository dashboard.
3. Click on **Pages** in the left sidebar.
4. Under **Build and deployment**, set the source branch to `main` (or the branch you pushed to) and the folder to `/ (root)`.
5. Click **Save**. GitHub will provide your live URL (e.g. `https://rohailrahmat.github.io/legacy-payroll-reengineering`). This serves as the public URL required by the professor!

---

## Technical Re-Engineering Code Build

To build the TypeScript refactored code:

```bash
# 1. Install NestJS & TypeORM packages
npm install

# 2. Compile TypeScript project
npm run build
```

The compiled JavaScript files will be outputted under the `/dist/` folder.

---

*This project is submitted for academic evaluation. All analysis, diagrams, and documentation are original work.*  
*© 2026 Rohail Rahmat | Karakorum International University, Gilgit*
