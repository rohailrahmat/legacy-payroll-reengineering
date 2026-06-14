import { Employee } from './Employee.entity';

export class PayrollRecord {
  id: number;
  employee: Employee;
  month: number;
  year: number;
  basicSalary: number;
  grossSalary: number;
  taxDeduction: number;
  pfDeduction: number;
  loanDeduction: number;
  netSalary: number;
}
