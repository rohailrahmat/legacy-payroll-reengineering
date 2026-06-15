# Modernization Strategy
## To-Be Architecture — Microservices Design

**Student:** Rohail Rahmat | **Roll No:** 2023-KIU-BS4163  
**University:** Karakorum International University, Gilgit  
**Supervisor:** Asif Hussain

---

## 1. Strategy Overview

The modernization strategy transforms the legacy PHP/MySQL monolith into a cloud-native microservices architecture using the **Strangler Fig Pattern** — a proven incremental migration approach that eliminates the risk of a "big bang" rewrite.

> *"The Strangler Fig Pattern is named after a vine that grows around an existing tree, gradually replacing it piece by piece while the original tree continues to provide support."*  
> — Martin Fowler, 2004

---

## 2. Proposed To-Be Architecture

```
                    ┌─────────────────────────────┐
                    │         React.js             │
                    │       Frontend App           │
                    │   (Employee Self-Service)    │
                    └──────────────┬──────────────┘
                                   │ HTTPS
                                   ▼
                    ┌─────────────────────────────┐
                    │        API Gateway           │
                    │  (Authentication, Routing,   │
                    │   Rate Limiting, Logging)    │
                    └──┬──────┬──────┬──────┬─────┘
                       │      │      │      │
          ┌────────────┘      │      │      └──────────────┐
          │                   │      │                     │
          ▼                   ▼      ▼                     ▼
┌──────────────┐   ┌──────────────┐  ┌──────────────┐  ┌──────────────┐
│  Employee    │   │   Payroll    │  │    Leave     │  │   Report     │
│  Service     │   │   Service   │  │   Service    │  │   Service    │
│  (Node.js)   │   │  (Node.js)  │  │  (Node.js)   │  │  (Node.js)   │
└──────┬───────┘   └──────┬──────┘  └──────┬───────┘  └──────┬───────┘
       │                  │                 │                  │
       ▼                  ▼                 ▼                  ▼
┌──────────┐    ┌──────────────┐   ┌──────────────┐   ┌──────────────┐
│PostgreSQL│    │  PostgreSQL  │   │  PostgreSQL  │   │  PostgreSQL  │
│employees │    │   payroll    │   │    leave     │   │   reports    │
│    DB    │    │     DB       │   │     DB       │   │     DB       │
└──────────┘    └──────────────┘   └──────────────┘   └──────────────┘
                       │
                       ▼
              ┌─────────────────┐
              │   RabbitMQ      │
              │ (Async Events:  │
              │ PayrollProcessed│
              │ LeaveApproved)  │
              └────────┬────────┘
                       │
                       ▼
              ┌─────────────────┐
              │  Notification   │
              │    Service      │
              │ (Email + SMS)   │
              └─────────────────┘
```

---

## 3. Technology Stack Comparison

| Layer | Legacy (As-Is) | Modern (To-Be) | Reason for Change |
|---|---|---|---|
| Backend Language | PHP 5.x Procedural | Node.js + TypeScript | Type safety, async I/O, ecosystem |
| Frontend | HTML + jQuery | React.js | Component model, state management |
| Database | MySQL 5.x (shared) | PostgreSQL 14 (per service) | ACID compliance, JSON support, reliability |
| Architecture | Monolithic | Microservices | Independent deployment, scalability |
| API | None | REST + OpenAPI 3.0 | Mobile support, third-party integration |
| Authentication | Session + copy-paste | JWT + bcrypt | Stateless, secure, standard |
| Deployment | Manual FTP | Docker + Kubernetes | Automated, scalable, reproducible |
| CI/CD | None | GitHub Actions | Automated testing and deployment |
| Monitoring | None | Prometheus + Grafana | Observability, alerting |
| Messaging | None | RabbitMQ | Async decoupled communication |

---

## 4. Microservices Design

### Service 1: Employee Service
**Responsibility:** Single source of truth for all employee data

| Endpoint | Method | Description |
|---|---|---|
| `/api/employees` | GET | List all active employees |
| `/api/employees/:id` | GET | Get employee details |
| `/api/employees` | POST | Register new employee |
| `/api/employees/:id` | PATCH | Update employee record |
| `/api/employees/:id/deactivate` | POST | Deactivate (not delete) employee |

**Database:** PostgreSQL — `employees` schema only  
**Events Published:** `employee.created`, `employee.updated`, `employee.deactivated`

---

### Service 2: Payroll Service
**Responsibility:** Salary calculation, payslip generation, payroll history

| Endpoint | Method | Description |
|---|---|---|
| `/api/payroll/run` | POST | Process payroll for a month |
| `/api/payroll/:employeeId` | GET | Get payroll history |
| `/api/payroll/payslip/:id` | GET | Download payslip PDF |
| `/api/payroll/bulk` | POST | Bulk payroll processing |

**Database:** PostgreSQL — `payroll_records`, `payslips` schemas  
**Events Published:** `payroll.processed`, `payslip.generated`  
**Events Consumed:** `employee.updated` (to refresh cached employee data)

---

### Service 3: Leave Service
**Responsibility:** Leave applications, approvals, balance tracking

| Endpoint | Method | Description |
|---|---|---|
| `/api/leaves` | POST | Submit leave application |
| `/api/leaves/:id/approve` | PATCH | Approve leave (manager) |
| `/api/leaves/:id/reject` | PATCH | Reject leave (manager) |
| `/api/leaves/balance/:employeeId` | GET | Check remaining leave balance |
| `/api/leaves/calendar` | GET | Team leave calendar view |

**Database:** PostgreSQL — `leaves`, `leave_balances` schemas  
**Events Published:** `leave.approved`, `leave.rejected`

---

### Service 4: Report Service
**Responsibility:** Payroll summaries, tax reports, department analytics

| Endpoint | Method | Description |
|---|---|---|
| `/api/reports/monthly` | GET | Monthly payroll summary |
| `/api/reports/tax-annual` | GET | Annual tax summary per employee |
| `/api/reports/department` | GET | Department cost analysis |
| `/api/reports/export/:format` | GET | Export as PDF or Excel |

---

### Service 5: Notification Service
**Responsibility:** Email and SMS notifications for payroll and leave events

**Trigger:** Listens to RabbitMQ events — no direct API calls  
- On `payroll.processed` → Email payslip to employee  
- On `leave.approved` → SMS + email to employee  
- On `leave.rejected` → Email with reason to employee

---

## 5. Security Architecture

| Security Concern | Legacy Approach | Modern Approach |
|---|---|---|
| Authentication | PHP session — no expiry | JWT with 24hr expiry + refresh tokens |
| Password Storage | Plaintext in DB | bcrypt hash (cost factor 12) |
| SQL Injection | Fully vulnerable | Impossible — parameterized queries only |
| HTTPS | Not enforced | HTTPS enforced, HTTP redirected |
| Input Validation | None | class-validator on every DTO |
| Rate Limiting | None | 100 req/min per IP at API Gateway |
| CORS | Not configured | Strict whitelist in API Gateway |
| Secrets Management | Hardcoded in source | Environment variables + HashiCorp Vault |
| Role-Based Access | None | RBAC: Admin, HR Manager, Employee |

---

## 6. Migration Roadmap — Strangler Fig Pattern

```
WEEK 1-4          WEEK 5-10         WEEK 11-18        WEEK 19-24        WEEK 25-28
    │                 │                 │                 │                 │
    ▼                 ▼                 ▼                 ▼                 ▼
Foundation       Employee           Payroll &          React             Legacy
─────────        Service            Leave              Frontend          Decommission
• Cloud infra    • Extract from     Services           ─────────         ─────────────
• PostgreSQL       monolith         ─────────          • Build React     • Redirect all
  setup          • REST API         • Payroll            app               traffic to
• Docker +         built              service          • Connect to        new system
  Kubernetes     • JWT auth         • Leave              all APIs        • Verify data
• CI/CD pipeline • Data             service           • Employee          integrity
• API Gateway      migration        • RabbitMQ           self-service    • Decommission
  deployed       • Old system         setup           • Manager           legacy PHP
                   still runs      • Old system         dashboard       • Archive DB
                   in parallel       still runs       • UAT testing     • Done ✓
```

**Principle:** The legacy system continues to operate throughout migration. Users experience zero downtime. Each new service is fully tested before any legacy component is retired.

---

## 7. Infrastructure Design

```yaml
# docker-compose.yml (simplified)
services:
  api-gateway:
    image: nginx:alpine
    ports: ["443:443"]

  employee-service:
    build: ./services/employee
    environment:
      - DATABASE_URL=${EMPLOYEE_DB_URL}
      - JWT_SECRET=${JWT_SECRET}

  payroll-service:
    build: ./services/payroll
    environment:
      - DATABASE_URL=${PAYROLL_DB_URL}
      - RABBITMQ_URL=${RABBITMQ_URL}

  rabbitmq:
    image: rabbitmq:3-management

  employee-db:
    image: postgres:14
    volumes: [employee_data:/var/lib/postgresql/data]

  payroll-db:
    image: postgres:14
    volumes: [payroll_data:/var/lib/postgresql/data]
```

---

## 8. Expected Outcomes

| Metric | Legacy System | After Modernization |
|---|---|---|
| Deployment frequency | Monthly (manual) | Multiple times per day (automated) |
| Mean time to recovery | Days (manual fix) | Minutes (automated rollback) |
| Security vulnerabilities | Critical (SQL injection) | Zero known vulnerabilities |
| API availability | N/A | 99.9% uptime SLA |
| Mobile app support | Impossible | Full REST API available |
| New feature delivery | Weeks (risky) | Days (safe, tested) |
| Test coverage | 0% | >80% target |

---

## References

- Newman, S. (2015). *Building Microservices.* O'Reilly Media.
- Fowler, M. (2004). *Strangler Fig Application.* martinfowler.com
- Evans, E. (2003). *Domain-Driven Design.* Addison-Wesley.
- OWASP Foundation. (2021). *OWASP Top 10.* owasp.org

---

*Rohail Rahmat | 2023-KIU-BS4163 | Karakorum International University, Gilgit*
