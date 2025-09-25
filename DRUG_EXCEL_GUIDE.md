# Drug Import/Export Guide

This guide explains how to import and export drug data using Excel files in your pharmacy management system.

## Features

### 1. Export Drugs
- **Full Export**: Export all drugs with complete information including stock levels
- **Selected Export**: Export only selected drugs from the table
- **Template Download**: Download a template file to understand the import format

### 2. Import Drugs
- **Bulk Import**: Import multiple drugs from an Excel file
- **Validation**: Automatic validation of data with error reporting
- **Drug Form Creation**: Automatically creates drug forms if they don't exist

## How to Use

### Exporting Drugs

1. **Navigate to Drugs Page**: Go to the Drugs section in your Filament admin panel
2. **Export Options**:
   - **Export All**: Click "Export Drugs" button to download all drugs
   - **Export Selected**: Select specific drugs and use "Export Selected" bulk action
   - **Download Template**: Click "Download Template" to get the import format

### Importing Drugs

1. **Download Template**: First, download the template to understand the required format
2. **Prepare Your Data**: Fill the Excel file with your drug data following the template format
3. **Import Process**:
   - Click "Import Drugs" button
   - Select your Excel file (.xlsx or .xls)
   - Click "Import" to process the file
4. **Review Results**: Check for any validation errors or warnings

## Excel File Format

### Required Columns

| Column Name | Type | Required | Description |
|-------------|------|----------|-------------|
| name | Text | Yes | Drug name (e.g., "Paracetamol 500mg") |
| catelog | Text | No | Drug catalog/code (e.g., "PARA-500") |
| generic_name | Text | No | Generic name (e.g., "Paracetamol") |
| drug_form | Text | No | Form type (e.g., "Tablet", "Capsule") |
| strength | Text | No | Drug strength (e.g., "500mg") |
| unit | Text | No | Unit of measurement (e.g., "mg", "ml") |
| min_stock | Number | No | Minimum stock level (default: 0) |
| expire_alert | Number | No | Days before expiry to alert (default: 30) |
| description | Text | No | Drug description |
| is_active | Boolean | No | Active status (true/false, default: true) |

### Sample Data

```
name,catelog,generic_name,drug_form,strength,unit,min_stock,expire_alert,description,is_active
Paracetamol 500mg,PARA-500,Paracetamol,Tablet,500mg,mg,10,30,Pain reliever and fever reducer,true
Amoxicillin 250mg,AMOX-250,Amoxicillin,Capsule,250mg,mg,5,30,Antibiotic for bacterial infections,true
```

## Validation Rules

- **Name**: Required, maximum 255 characters
- **Catelog**: Optional, maximum 255 characters
- **Generic Name**: Optional, maximum 255 characters
- **Drug Form**: Optional, maximum 255 characters (will be created if doesn't exist)
- **Strength**: Optional, maximum 255 characters
- **Unit**: Optional, maximum 255 characters
- **Min Stock**: Optional, must be 0 or positive integer
- **Expire Alert**: Optional, must be 1 or greater
- **Description**: Optional, any length
- **Is Active**: Optional, true/false values

## Error Handling

### Import Errors
- **Validation Errors**: Shows which rows have validation issues
- **Duplicate Handling**: System will create new records (no duplicate checking by default)
- **Missing Drug Forms**: Automatically creates new drug forms if they don't exist

### Success Indicators
- **Green Notification**: All drugs imported successfully
- **Warning Notification**: Import completed but with some issues
- **Error Notification**: Import failed completely

## Tips for Successful Import

1. **Use the Template**: Always start with the downloaded template
2. **Check Data Types**: Ensure numbers are in number format, not text
3. **Boolean Values**: Use true/false, 1/0, or yes/no for is_active column
4. **Empty Cells**: Leave cells empty for optional fields rather than using "N/A" or "-"
5. **Test Small Batches**: Import a few records first to test the format

## API Endpoints (Optional)

If you need to integrate with external systems:

- **Export**: `GET /drugs/export`
- **Template**: `GET /drugs/template`  
- **Import**: `POST /drugs/import` (with file upload)

## Troubleshooting

### Common Issues

1. **File Format Error**: Ensure you're uploading .xlsx or .xls files
2. **Required Field Missing**: Name field must be filled for all rows
3. **Invalid Data Types**: Check that numbers are properly formatted
4. **Large Files**: For very large files, consider splitting into smaller batches

### Getting Help

If you encounter issues:
1. Check the error messages in the notification
2. Verify your data against the template format
3. Try importing a smaller sample first
4. Contact your system administrator if problems persist

## Security Notes

- Only authenticated users can import/export drugs
- File uploads are validated for type and content
- All imports are logged for audit purposes
- Consider backing up your data before large imports