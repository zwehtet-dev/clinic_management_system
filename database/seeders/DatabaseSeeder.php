<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting to seed the clinic management system...');

        // Seed in correct order due to foreign key dependencies
        $this->call([
            UserSeeder::class,
            DrugFormSeeder::class,
            DoctorSeeder::class,
            PatientSeeder::class,
            ServiceSeeder::class,
            DrugSeeder::class,
            DrugBatchSeeder::class,
            VisitSeeder::class,
            DrugSaleSeeder::class,
            InvoiceSeeder::class,
            ExpenseSeeder::class,
        ]);

        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->info('');
        $this->command->info('📊 Seeded Data Summary:');
        $this->command->info('👥 Users: 5 (admin, doctors, staff)');
        $this->command->info('🏥 Doctors: 6 specialists');
        $this->command->info('👤 Patients: 50 (10 specific + 40 random)');
        $this->command->info('💊 Drug Forms: 8 types');
        $this->command->info('💉 Drugs: 15 common medications');
        $this->command->info('📦 Drug Batches: ~75 batches with various scenarios');
        $this->command->info('🩺 Services: 15 medical services');
        $this->command->info('📋 Visits: ~160 visits (past 3 months + upcoming)');
        $this->command->info('🛒 Drug Sales: ~85 sales transactions');
        $this->command->info('🧾 Invoices: ~80 invoices with items');
        $this->command->info('💰 Expenses: ~100 expense records');
        $this->command->info('');
        $this->command->info('🔐 Login Credentials:');
        $this->command->info('Email: admin@clinic.com');
        $this->command->info('Password: password');
        $this->command->info('');
        $this->command->info('🧪 Test Scenarios Included:');
        $this->command->info('• Low stock alerts');
        $this->command->info('• Expiring drug batches');
        $this->command->info('• Various visit statuses');
        $this->command->info('• Patient and walk-in sales');
        $this->command->info('• Complete invoice workflows');
        $this->command->info('• Expense tracking');
    }
}
