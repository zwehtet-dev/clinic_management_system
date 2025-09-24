# Manual Testing Checklist - Clinic Management System

## 🚀 Getting Started

### 1. Access the System
- **URL**: http://localhost:8000/admin
- **Login**: admin@clinic.com
- **Password**: password

### 2. First Login Test
- [ ] Login page loads correctly
- [ ] Authentication works with provided credentials
- [ ] Dashboard loads after successful login
- [ ] Navigation menu is visible and functional

## 📊 Dashboard Testing

### Stats Overview Widget
- [ ] Total active patients count displays
- [ ] Visits this month vs last month comparison
- [ ] Revenue calculations show correctly
- [ ] Net profit calculations are accurate
- [ ] Stock alert counts are visible

### Stock Alert Widget
- [ ] Low stock drugs are displayed
- [ ] Color coding works (red for critical, yellow for warning)
- [ ] Expiry date alerts show correctly
- [ ] Stock quantities are accurate
- [ ] "Paracetamol 500mg" shows in low stock (5 units)

## 👥 Patient Management Testing

### Patient List
- [ ] Patient list loads with 50 patients
- [ ] Search functionality works (try "Aung Kyaw")
- [ ] Filter by gender works
- [ ] Pagination works correctly
- [ ] Patient details are accurate

### Patient CRUD Operations
- [ ] Create new patient form works
- [ ] Required field validation works
- [ ] Age validation (numeric, positive)
- [ ] Gender selection works
- [ ] Phone number format validation
- [ ] Edit existing patient works
- [ ] Patient status (active/inactive) toggle

### Patient Visits Relation Manager
- [ ] Navigate to patient "Aung Kyaw" (PAT-2025-000001)
- [ ] Click "Visits" tab
- [ ] View existing visits for the patient
- [ ] Create new visit from patient page
- [ ] Visit form validation works
- [ ] Doctor assignment works
- [ ] Visit status updates work

## 🏥 Doctor Management Testing

### Doctor List
- [ ] All 6 doctors are displayed
- [ ] Specializations show correctly
- [ ] Contact information is visible
- [ ] Search functionality works
- [ ] Doctor status management

### Doctor Details
- [ ] View doctor profile
- [ ] Edit doctor information
- [ ] License number validation
- [ ] Specialization categories

## 💊 Drug Inventory Testing

### Drug Catalog
- [ ] All 15 drugs are listed
- [ ] Drug forms display correctly
- [ ] Stock levels show accurately
- [ ] Low stock alerts are visible
- [ ] Search for "Paracetamol" works

### Drug Batch Management
- [ ] Navigate to Drug Batches
- [ ] View all batches (~75 total)
- [ ] Check expiry date color coding
- [ ] Filter by "Low Stock" shows results
- [ ] Filter by "Expiring Soon" shows results
- [ ] Filter by "Expired" shows results
- [ ] Batch "LOW-STOCK-001" shows 5 units
- [ ] Batch "EXPIRING-001" shows expiring soon

### Stock Alerts Testing
- [ ] Dashboard shows stock alerts
- [ ] Low stock drugs are highlighted
- [ ] Expiry warnings are visible
- [ ] Stock quantities are accurate

## 🩺 Visit Management Testing

### Visit Schedule
- [ ] View all visits (~160 total)
- [ ] Filter by status (pending, completed, cancelled)
- [ ] Filter by visit type (consultation, follow-up)
- [ ] Filter by doctor
- [ ] Today's visits are visible
- [ ] Upcoming visits show correctly

### Visit CRUD Operations
- [ ] Create new visit
- [ ] Patient selection works
- [ ] Doctor assignment works
- [ ] Visit type selection
- [ ] Consultation fee calculation
- [ ] Date picker functionality
- [ ] Status management
- [ ] Diagnosis and notes fields

### Visit Status Testing
- [ ] Pending visits for today/tomorrow
- [ ] Completed visits with diagnosis
- [ ] Cancelled visits (if any)
- [ ] Status update functionality

## 🛒 Drug Sales Testing

### Sales List
- [ ] View all drug sales (~85 total)
- [ ] Patient sales vs walk-in sales
- [ ] Search functionality
- [ ] Date range filtering
- [ ] Today's sales are visible

### Create New Sale
- [ ] Start new drug sale
- [ ] Select patient (try "Ma Thida")
- [ ] OR enter walk-in customer name
- [ ] Add drugs to sale
- [ ] **CRITICAL**: Try to oversell (more than available stock)
- [ ] **CRITICAL**: Try to sell expired drugs
- [ ] Verify stock validation prevents overselling
- [ ] Check quantity limits based on available stock
- [ ] Invoice generation works
- [ ] Total calculations are correct

### Stock Validation Tests
- [ ] Select "Paracetamol 500mg" batch "LOW-STOCK-001"
- [ ] Try to sell 10 units (should fail - only 5 available)
- [ ] Try to sell 3 units (should work)
- [ ] Verify stock reduces after successful sale
- [ ] Check if expired batches are excluded from selection

## 🧾 Invoice Management Testing

### Invoice List
- [ ] View all invoices (~80 total)
- [ ] Invoice numbers are unique
- [ ] Total amounts are correct
- [ ] Status indicators work
- [ ] Search by invoice number

### Invoice Details
- [ ] View invoice details
- [ ] Line items display correctly
- [ ] Service items show properly
- [ ] Drug items show batch information
- [ ] Quantity × Unit Price = Line Total
- [ ] Grand total is sum of all line items
- [ ] Invoice status management

## 💰 Financial Management Testing

### Expense Categories
- [ ] View 8 expense categories
- [ ] Categories are properly named
- [ ] Active/inactive status works

### Expense Records
- [ ] View all expenses (~100 total)
- [ ] Filter by category
- [ ] Filter by date range
- [ ] Monthly patterns visible
- [ ] Regular expenses (rent, utilities, salaries)

### Financial Calculations
- [ ] Revenue calculations on dashboard
- [ ] Expense totals are accurate
- [ ] Net profit calculations
- [ ] Monthly comparisons work

## 🔍 Search & Filter Testing

### Global Search
- [ ] Search for "Aung Kyaw" (patient)
- [ ] Search for "PAT-2025-000001" (patient ID)
- [ ] Search for "Paracetamol" (drug)
- [ ] Search for "Dr. Sarah" (doctor)
- [ ] Search results are relevant

### Advanced Filtering
- [ ] Date range filters work
- [ ] Status filters function
- [ ] Category filters work
- [ ] Multi-column sorting
- [ ] Filter combinations work

## 🧪 Business Logic Testing

### Critical Stock Scenarios
1. **Low Stock Test**:
   - [ ] Find "Paracetamol 500mg" in drug list
   - [ ] Verify it shows low stock warning
   - [ ] Check batch "LOW-STOCK-001" has 5 units
   - [ ] Try to sell 10 units (should fail)
   - [ ] Try to sell 3 units (should work)

2. **Expiry Test**:
   - [ ] Find batch "EXPIRING-001"
   - [ ] Verify it shows expiry warning
   - [ ] Check expiry date is within 30 days
   - [ ] Verify color coding (yellow/red)

3. **FIFO Test**:
   - [ ] In drug sales, select a drug with multiple batches
   - [ ] Verify system suggests earliest expiry batch first
   - [ ] Check batch ordering by expiry date

### Financial Accuracy
- [ ] Create a test invoice manually
- [ ] Add multiple items with different quantities
- [ ] Verify line total = quantity × unit price
- [ ] Verify grand total = sum of all line totals
- [ ] Check tax calculations (if applicable)

## 📱 User Experience Testing

### Navigation
- [ ] Menu structure is intuitive
- [ ] Breadcrumbs work correctly
- [ ] Back buttons function
- [ ] Page transitions are smooth

### Form Usability
- [ ] Required field indicators
- [ ] Error messages are clear
- [ ] Success notifications appear
- [ ] Form validation is immediate
- [ ] Help text is helpful

### Responsive Design
- [ ] Test on different screen sizes
- [ ] Mobile navigation works
- [ ] Tables are scrollable on mobile
- [ ] Forms are usable on tablets

## 🔒 Security Testing

### Authentication
- [ ] Logout functionality works
- [ ] Session timeout (if configured)
- [ ] Password requirements
- [ ] Login attempt limits (if configured)

### Data Access
- [ ] Users can only access appropriate data
- [ ] No unauthorized data exposure
- [ ] Proper error handling

## 🚨 Error Handling Testing

### Validation Errors
- [ ] Try to create patient without required fields
- [ ] Try to set negative age
- [ ] Try to create duplicate patient ID
- [ ] Try to schedule visit in the past
- [ ] Try to sell more drugs than available

### System Errors
- [ ] Test with invalid data
- [ ] Test with missing relationships
- [ ] Check error messages are user-friendly

## 📊 Performance Testing

### Page Load Times
- [ ] Dashboard loads in < 2 seconds
- [ ] Patient list loads in < 1 second
- [ ] Drug inventory loads in < 1 second
- [ ] Search results appear in < 500ms

### Data Handling
- [ ] Large lists paginate properly
- [ ] Search is responsive
- [ ] Filters apply quickly
- [ ] Forms submit promptly

## ✅ Final Verification

### Data Integrity
- [ ] Patient-visit relationships are correct
- [ ] Invoice-item relationships are accurate
- [ ] Stock levels update after sales
- [ ] Financial calculations are precise

### Business Workflow
- [ ] Complete patient journey (register → visit → invoice → payment)
- [ ] Complete sales workflow (select drugs → validate stock → generate invoice)
- [ ] Complete inventory workflow (add batch → track expiry → manage stock)

## 🐛 Issue Reporting

For any issues found, document:
1. **Steps to reproduce**
2. **Expected behavior**
3. **Actual behavior**
4. **Screenshots (if applicable)**
5. **Browser/device information**
6. **Error messages**

## 🎯 Success Criteria

The system passes manual testing if:
- [ ] All core CRUD operations work
- [ ] Business logic validates correctly
- [ ] Stock management prevents errors
- [ ] Financial calculations are accurate
- [ ] User experience is intuitive
- [ ] Data integrity is maintained
- [ ] Performance is acceptable
- [ ] No critical bugs found

---

## 🎉 Testing Complete!

Once you've completed this checklist, the clinic management system will be thoroughly validated and ready for production use.

**Remember**: Focus on the critical business scenarios first, then move to edge cases and user experience testing.

**Happy Testing! 🧪**