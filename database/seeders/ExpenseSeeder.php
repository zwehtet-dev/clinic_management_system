<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        // First create expense categories
        $categories = [
            [
                'name' => 'Medical Supplies',
                'description' => 'Purchase of medical equipment and supplies',
                'is_active' => true,
            ],
            [
                'name' => 'Utilities',
                'description' => 'Electricity, water, internet bills',
                'is_active' => true,
            ],
            [
                'name' => 'Staff Salaries',
                'description' => 'Employee salaries and benefits',
                'is_active' => true,
            ],
            [
                'name' => 'Rent',
                'description' => 'Office and clinic rent',
                'is_active' => true,
            ],
            [
                'name' => 'Maintenance',
                'description' => 'Equipment maintenance and repairs',
                'is_active' => true,
            ],
            [
                'name' => 'Marketing',
                'description' => 'Advertising and promotional expenses',
                'is_active' => true,
            ],
            [
                'name' => 'Office Supplies',
                'description' => 'Stationery and office equipment',
                'is_active' => true,
            ],
            [
                'name' => 'Transportation',
                'description' => 'Vehicle fuel and maintenance',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::create($category);
        }

        // Create expenses for the past 3 months
        $expenseCategories = ExpenseCategory::all();
        
        for ($i = 0; $i < 100; $i++) {
            $category = $expenseCategories->random();
            $expenseDate = Carbon::now()->subDays(rand(1, 90));
            
            Expense::create([
                'expense_category_id' => $category->id,
                'amount' => $this->getExpenseAmount($category->name),
                'expense_date' => $expenseDate,
                'name' => $this->getExpenseDescription($category->name),
                'notes' => rand(1, 3) === 1 ? $this->getRandomNotes() : null,
            ]);
        }

        // Create some specific monthly expenses
        $this->createMonthlyExpenses($expenseCategories);
    }

    private function getExpenseAmount($categoryName): float
    {
        return match ($categoryName) {
            'Medical Supplies' => rand(50000, 500000),
            'Utilities' => rand(80000, 200000),
            'Staff Salaries' => rand(300000, 1500000),
            'Rent' => rand(500000, 1000000),
            'Maintenance' => rand(20000, 150000),
            'Marketing' => rand(30000, 200000),
            'Office Supplies' => rand(10000, 80000),
            'Transportation' => rand(15000, 100000),
            default => rand(10000, 100000),
        };
    }

    private function getExpenseDescription($categoryName): string
    {
        return match ($categoryName) {
            'Medical Supplies' => collect([
                'Surgical instruments purchase',
                'Disposable syringes and needles',
                'Medical gloves and masks',
                'Bandages and gauze',
                'Thermometers and BP monitors',
                'Laboratory reagents',
                'X-ray films',
                'Disinfectants and sanitizers',
            ])->random(),
            
            'Utilities' => collect([
                'Monthly electricity bill',
                'Water supply charges',
                'Internet and phone bills',
                'Generator fuel',
                'Air conditioning maintenance',
            ])->random(),
            
            'Staff Salaries' => collect([
                'Doctor salaries',
                'Nurse salaries',
                'Administrative staff salaries',
                'Pharmacist salary',
                'Security guard salary',
                'Cleaning staff wages',
            ])->random(),
            
            'Rent' => collect([
                'Clinic building rent',
                'Parking space rent',
                'Storage facility rent',
            ])->random(),
            
            'Maintenance' => collect([
                'X-ray machine servicing',
                'Computer system maintenance',
                'Air conditioning repair',
                'Plumbing repairs',
                'Electrical work',
                'Equipment calibration',
            ])->random(),
            
            'Marketing' => collect([
                'Newspaper advertisement',
                'Social media promotion',
                'Brochure printing',
                'Website maintenance',
                'Health camp sponsorship',
            ])->random(),
            
            'Office Supplies' => collect([
                'Stationery purchase',
                'Printer paper and ink',
                'Filing cabinets',
                'Office furniture',
                'Computer accessories',
            ])->random(),
            
            'Transportation' => collect([
                'Ambulance fuel',
                'Vehicle maintenance',
                'Driver allowance',
                'Vehicle insurance',
                'Parking fees',
            ])->random(),
            
            default => 'General expense',
        };
    }

    private function getRandomNotes(): string
    {
        $notes = [
            'Urgent purchase required',
            'Bulk discount applied',
            'Emergency expense',
            'Planned maintenance',
            'Quality upgrade',
            'Regulatory compliance',
            'Patient safety requirement',
            'Cost optimization measure',
        ];

        return $notes[array_rand($notes)];
    }

    private function createMonthlyExpenses($categories): void
    {
        // Create regular monthly expenses for the past 3 months
        for ($month = 0; $month < 3; $month++) {
            $expenseDate = Carbon::now()->subMonths($month)->startOfMonth()->addDays(rand(1, 5));
            
            // Rent (monthly)
            $rentCategory = $categories->where('name', 'Rent')->first();
            if ($rentCategory) {
                Expense::create([
                    'expense_category_id' => $rentCategory->id,
                    'amount' => 800000,
                    'expense_date' => $expenseDate,
                    'name' => 'Monthly clinic rent',
                    'notes' => 'Regular monthly payment',
                ]);
            }

            // Utilities (monthly)
            $utilitiesCategory = $categories->where('name', 'Utilities')->first();
            if ($utilitiesCategory) {
                Expense::create([
                    'expense_category_id' => $utilitiesCategory->id,
                    'amount' => rand(120000, 180000),
                    'expense_date' => $expenseDate->copy()->addDays(rand(1, 3)),
                    'name' => 'Monthly utility bills',
                    'notes' => 'Electricity, water, internet',
                ]);
            }

            // Staff Salaries (monthly)
            $salaryCategory = $categories->where('name', 'Staff Salaries')->first();
            if ($salaryCategory) {
                Expense::create([
                    'expense_category_id' => $salaryCategory->id,
                    'amount' => rand(1200000, 1800000),
                    'expense_date' => $expenseDate->copy()->addDays(rand(25, 30)),
                    'name' => 'Monthly staff salaries',
                    'notes' => 'All staff salary payments',
                ]);
            }
        }
    }
}