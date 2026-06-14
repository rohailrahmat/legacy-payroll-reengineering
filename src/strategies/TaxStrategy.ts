import { Injectable } from '@nestjs/common';

@Injectable()
export class TaxStrategy {
  calculate(baseSalary: number): number {
    // Flat 10% tax rate logic for demo purposes
    return baseSalary * 0.10;
  }
}
