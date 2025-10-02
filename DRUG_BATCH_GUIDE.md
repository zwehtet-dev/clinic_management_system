# Drug Batch Management Guide

## Auto-Generated Batch Numbers

The system now supports automatic batch number generation for drug batches. This feature helps maintain consistent batch numbering and reduces manual entry errors.

### How It Works

When creating a new drug batch:

1. **Manual Batch Number**: If you provide a batch number, it will be used as-is
2. **Auto-Generated**: If you leave the batch number field empty, the system will automatically generate one

### Batch Number Format

Auto-generated batch numbers follow this format:
```
BAT-{drug_id}-{sequential_number}
```

**Examples:**
- `BAT-1-000001` - First batch for drug ID 1
- `BAT-1-000002` - Second batch for drug ID 1
- `BAT-5-000001` - First batch for drug ID 5

### Import Features

When importing drug batches via Excel:

1. **batch_number column is optional**
2. **Leave empty for auto-generation**
3. **Provide custom batch numbers when needed**
4. **Flexible date formats supported**:
   - `9/25/2025` (M/d/Y - US format)
   - `12/31/2025` (M/d/Y - US format)
   - `2025-09-25` (Y-m-d - ISO format)
   - `25/09/2025` (d/m/Y - European format)
   - `09-25-2025` (m-d-Y - US with dashes)
   - `25-09-2025` (d-m-Y - European with dashes)

### Template Examples

The import template includes examples showing:
- Empty batch_number (will auto-generate)
- Custom batch_number (will use provided value)
- Multiple batches for same drug (sequential numbering)
- Various date formats (M/d/Y format like 9/25/2025)

### Date Format Support

The system automatically detects and parses various date formats:

**Supported Formats:**
- `9/25/2025` - Single digit month/day (recommended)
- `09/25/2025` - Double digit month/day
- `2025-09-25` - ISO format (Y-m-d)
- `25/09/2025` - European format (d/m/Y)
- `09-25-2025` - US format with dashes
- `25-09-2025` - European format with dashes

**Examples:**
- Expiry Date: `12/31/2025`
- Received Date: `1/15/2024`

### Benefits

- **Consistency**: All auto-generated batch numbers follow the same format
- **No Duplicates**: System ensures unique batch numbers per drug
- **Flexibility**: Can still use custom batch numbers when needed
- **Import Friendly**: Bulk imports work seamlessly with auto-generation

### Usage in Forms

In the Filament form:
- Batch number field shows "Auto-generated if left empty"
- Helper text explains the format
- Field is optional but will be populated on save

### Database Considerations

- Batch numbers are unique per drug
- Auto-generation happens during model creation
- Sequential numbering based on existing batches for the same drug