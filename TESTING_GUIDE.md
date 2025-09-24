# Clinic Management System - Complete Testing Guide

## 🚀 Getting Started

### 1. Seed the Database
```bash
# Fresh migration and seed
php artisan migrate:fresh --seed

# Or just seed if database is already migrated
php artisan db:seed
```

### 2. Login Credentials
- **Email**: `admin@clinic.com`
- **Password**: `password`

### 3. Access the System
```bash
php artisan serve
```
Then visit: `http://localhost:8000/admin`

## 📊 Test Data Overview

### Users (5 total)
- **Admin User** - admin@clinic.com
- **Dr. Sarah Johnson** - sarah@clinic.com  
- **Nurse Mary Wilson** - mary@clinic.com
- **Pharmacist John Smith** - john@clinic.com
- **Receptionist Lisa Brown** - lisa@clinic.com

### Doctors (6 specialists)
- Dr. Sarah Johnson (General Medicine)
- Dr. Michael Chen (Pediatrics)
- Dr. Emily Rodriguez (Cardiology)
- Dr. James Wilson (Orthopedics)
- Dr. Lisa Thompson (Dermatology)
- Dr. Robert Kim (Neurology)

### Patients (50 total)
- 10 specific test patients with realistic Myanmar names
- 40 additional random patients via factory

### Drugs & Inventory
- **15 common medications** (Paracetamol, Amoxicillin, Ibuprofen, etc.)
- **~75 drug batches** with various scenarios:
  - ✅ Good stock levels
  - ⚠️ Low stock alerts
  - 🔴 Out of stock items
  - ⏰ Expiring soon batches
  - ❌ Expired batches

### Transactions
- **~160 visits** (past 3 months + upcoming appointments)
- **~85 drug sales** (patient and walk-in customers)
- **~80 invoices** with line items
- **~100 expense records** across 8 categories

## 🧪 Feature Testing Scenarios

### 1. Dashboard & Analytics Testing

#### Test the Stats Overview Widget
- **Location**: Dashboard home page
- **What to Check**:
  - Total active patients count
  - Visits this month vs last month
  - Revenue calculations
  - Net profit calculations
  - Stock alert counts

#### Test the Stock Alert Widget
- **Location**: Dashboard home page
- **What to Check**:
  - Low stock drugs displayed
  - Color coding (red for critical, yellow for warning)
  - Expiry date alerts
  - Stock quantities

### 2. Patient Management Testing

#### Test Patient CRUD Operations
- **Location**: Patients menu
- **Test Cases**:
  - ✅ View patient list with search/filter
  - ✅ Create new patient with validation
  - ✅ Edit existing patient information
  - ✅ View patient details
  - ✅ Patient status (active/inactive)

#### Test Patient Visits Relation Manager
- **Location**: Patient detail page → Visits tab
- **Test Cases**:
  - ✅ View all visits for a patient
  - ✅ Create new visit from patient page
  - ✅ Edit visit information
  - ✅ Filter visits by status/date
  - ✅ Visit status updates

**Specific Test Patient**: "Aung Kyaw" (PAT-2025-000001)
- Has diabetes notes
- Multiple visit history
- Recent and upcoming appointments

### 3. Doctor Management Testing

#### Test Doctor Workflow
- **Location**: Doctors menu
- **Test Cases**:
  - ✅ View doctor list
  - ✅ Doctor specializations
  - ✅ Contact information management
  - ✅ Active/inactive status

#### Test Doctor Visits (if relation manager implemented)
- **Location**: Doctor detail page → Visits tab
- **Test Cases**:
  - ✅ View all visits assigned to doctor
  - ✅ Schedule new appointments
  - ✅ Update visit status
  - ✅ Filter by date range

### 4. Drug Inventory Testing

#### Test Drug Management
- **Location**: Drugs menu
- **Test Cases**:
  - ✅ View drug catalog
  - ✅ Drug forms and strengths
  - ✅ Stock level indicators
  - ✅ Low stock alerts
  - ✅ Add new drugs

#### Test Drug Batch Management
- **Location**: Drug Batches menu
- **Test Cases**:
  - ✅ View all batches
  - ✅ Expiry date tracking
  - ✅ Stock quantities
  - ✅ Batch filtering (expired, expiring, low stock)
  - ✅ Add new batches

**Critical Test Scenarios**:
- **Low Stock**: Look for "Paracetamol 500mg" batch "LOW-STOCK-001" (only 5 units)
- **Expiring Soon**: Look for batch "EXPIRING-001" (expires in 15 days)
- **Out of Stock**: Several batches with 0 quantity
- **Expired**: Some batches with past expiry dates

### 5. Visit Management Testing

#### Test Visit Workflow
- **Location**: Visits menu
- **Test Cases**:
  - ✅ View visit schedule
  - ✅ Create new visits
  - ✅ Assign doctors and patients
  - ✅ Set consultation fees
  - ✅ Update visit status
  - ✅ Add diagnosis and notes

#### Test Visit Status Management
- **Pending Visits**: Today and tomorrow appointments
- **Completed Visits**: Past visits with diagnosis
- **Cancelled Visits**: Some test cancelled visits

### 6. Drug Sales Testing

#### Test Sales Workflow
- **Location**: Drug Sales menu
- **Test Cases**:
  - ✅ View sales history
  - ✅ Create new sales (patient vs walk-in)
  - ✅ Add multiple drugs to sale
  - ✅ Stock validation during sale
  - ✅ Invoice generation
  - ✅ Payment processing

#### Critical Stock Validation Tests
1. **Try to oversell**: Attempt to sell more than available stock
2. **Expired drug test**: Try to sell expired batches
3. **FIFO validation**: Check if system suggests earliest expiry first

**Test Scenarios**:
- **Patient Sale**: "Ma Thida" has recent purchase
- **Walk-in Sales**: "Ko Thura", "Ma Sandar" examples
- **Today's Sales**: 5 recent sales for testing

### 7. Invoice Management Testing

#### Test Invoice Generation
- **Location**: Invoices menu
- **Test Cases**:
  - ✅ View invoice list
  - ✅ Invoice details with line items
  - ✅ Service and drug items
  - ✅ Total calculations
  - ✅ Payment status

#### Test Invoice Items (if relation manager implemented)
- **Location**: Invoice detail page → Items tab
- **Test Cases**:
  - ✅ Add/remove invoice items
  - ✅ Quantity and price validation
  - ✅ Automatic total recalculation
  - ✅ Stock validation for drugs

### 8. Financial Management Testing

#### Test Expense Tracking
- **Location**: Expenses menu
- **Test Cases**:
  - ✅ View expense categories
  - ✅ Record new expenses
  - ✅ Monthly expense patterns
  - ✅ Expense reporting

#### Test Revenue Analysis
- **Location**: Dashboard widgets
- **Test Cases**:
  - ✅ Monthly revenue trends
  - ✅ Profit calculations
  - ✅ Expense vs revenue comparison

### 9. Search & Filter Testing

#### Global Search
- **Location**: Top navigation search
- **Test Cases**:
  - Search for "Aung Kyaw" (patient)
  - Search for "PAT-2025-000001" (patient ID)
  - Search for "Paracetamol" (drug)
  - Search for "Dr. Sarah" (doctor)

#### Advanced Filtering
- **Test in each module**:
  - Date range filters
  - Status filters
  - Category filters
  - Multi-column sorting

### 10. Business Logic Testing

#### Stock Management Logic
1. **Low Stock Alerts**: 
   - Check dashboard for red/yellow indicators
   - Verify min_stock thresholds

2. **Expiry Management**:
   - Check expiring batches widget
   - Verify color coding by days to expiry

3. **FIFO Implementation**:
   - Check if earliest expiry batches are suggested first
   - Verify in drug sales process

#### Financial Calculations
1. **Invoice Totals**:
   - Verify line item calculations
   - Check tax calculations (if applicable)
   - Confirm total accuracy

2. **Profit Margins**:
   - Check drug batch profit calculations
   - Verify purchase vs sell price differences

## 🔍 Specific Test Cases

### Test Case 1: Complete Patient Journey
1. Create new patient
2. Schedule visit with doctor
3. Complete visit with diagnosis
4. Generate invoice with services
5. Add drug prescription to invoice
6. Process payment
7. Schedule follow-up

### Test Case 2: Inventory Management
1. Check low stock alerts
2. Add new drug batch
3. Process drug sale
4. Verify stock reduction
5. Check updated stock levels
6. Test overselling prevention

### Test Case 3: Financial Workflow
1. Record daily expenses
2. Process patient visits
3. Generate drug sales
4. Review daily revenue
5. Check profit calculations
6. Generate financial reports

## 🚨 Critical Issues to Test

### 1. Stock Validation
- ❌ **Should Fail**: Selling more than available stock
- ❌ **Should Fail**: Selling expired drugs
- ✅ **Should Work**: Selling within stock limits

### 2. Data Integrity
- ✅ **Should Work**: Patient-visit relationships
- ✅ **Should Work**: Invoice-item calculations
- ✅ **Should Work**: Stock level updates

### 3. User Experience
- ✅ **Should Work**: Intuitive navigation
- ✅ **Should Work**: Clear error messages
- ✅ **Should Work**: Responsive design

## 📱 Mobile Testing

Test the admin panel on mobile devices:
- ✅ Responsive layout
- ✅ Touch-friendly interface
- ✅ Mobile navigation
- ✅ Form usability

## 🔒 Security Testing

### Authentication
- ✅ Login/logout functionality
- ✅ Session management
- ✅ Password requirements

### Authorization
- ✅ Role-based access (if implemented)
- ✅ Data access restrictions
- ✅ Action permissions

## 📊 Performance Testing

### Load Testing
- Test with large datasets
- Multiple concurrent users
- Complex queries and reports
- File upload/download

### Database Performance
- Query execution times
- Index effectiveness
- Memory usage
- Response times

## 🐛 Bug Reporting

When you find issues, document:
1. **Steps to reproduce**
2. **Expected behavior**
3. **Actual behavior**
4. **Screenshots/videos**
5. **Browser/device info**
6. **Error messages**

## ✅ Testing Checklist

### Core Functionality
- [ ] User authentication works
- [ ] Dashboard displays correctly
- [ ] Patient management CRUD
- [ ] Doctor management CRUD
- [ ] Drug inventory management
- [ ] Visit scheduling and management
- [ ] Drug sales processing
- [ ] Invoice generation
- [ ] Expense tracking
- [ ] Search and filtering

### Business Logic
- [ ] Stock validation prevents overselling
- [ ] Expiry date validation works
- [ ] FIFO batch selection
- [ ] Invoice calculations accurate
- [ ] Low stock alerts functional
- [ ] Financial calculations correct

### User Experience
- [ ] Navigation is intuitive
- [ ] Forms validate properly
- [ ] Error messages are clear
- [ ] Loading states work
- [ ] Mobile responsive
- [ ] Print functionality (if applicable)

### Data Integrity
- [ ] Relationships maintained
- [ ] Foreign keys enforced
- [ ] Data consistency
- [ ] Transaction safety
- [ ] Audit trails (if implemented)

## 🎉 Success Criteria

The system passes testing if:
1. ✅ All core CRUD operations work
2. ✅ Business logic validates correctly
3. ✅ Stock management prevents errors
4. ✅ Financial calculations are accurate
5. ✅ User experience is smooth
6. ✅ Data integrity is maintained
7. ✅ Performance is acceptable
8. ✅ Security measures work

---

**Happy Testing! 🧪**

Remember: The goal is to ensure the system works reliably for real clinic operations. Test thoroughly and document any issues you find.