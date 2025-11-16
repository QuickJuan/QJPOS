# Tenant Registration System

## Overview
This is a comprehensive, ADA-compliant tenant registration system that allows business owners to apply for a StorePos account. The system is user-friendly, intuitive, and accessible even for elderly users.

## Features

### Frontend (Vue 3 + TypeScript)
- **TenantRegistration.vue** - Main registration form with the following features:
  - Large, easy-to-read fonts (16px base size)
  - Clear labeling with required field indicators
  - Real-time validation feedback
  - Drag-and-drop logo upload with preview
  - Mobile-responsive design
  - ADA compliant with proper ARIA labels and descriptions
  - Clear color contrast ratios
  - Keyboard navigation support
  - Large clickable areas

- **TenantRegistrationSuccess.vue** - Success confirmation page with:
  - Clear success messaging
  - Next steps information
  - Contact information for support
  - Email confirmation notification

### Backend (Laravel)
- **TenantRegistrationController** - Handles:
  - Form display
  - Form submission and validation
  - File upload handling
  - Email notification sending
  - Success page display

- **TenantApplication Model** - Stores:
  - business_name
  - business_address
  - owner_name
  - owner_email
  - owner_phone
  - business_permit_number (optional)
  - logo_path (optional)
  - status (pending/approved/rejected)
  - notes (for admin use)

- **TenantApplicationReceived Mailable** - Sends confirmation email to applicant

- **Database Migration** - Creates `tenant_applications` table

## Form Fields

### Business Information
- **Business Name** (Required) - The name of the business
- **Business Address** (Required) - Full address of the business
- **Business Permit Number** (Optional) - For verification purposes

### Owner Information
- **Owner Name** (Required) - Full name of the business owner
- **Owner Email** (Required) - Email for account communications and application updates
- **Owner Phone** (Required) - Contact number for follow-up

### Business Logo
- **Logo Upload** (Optional) - Accepts JPG, PNG, GIF up to 5MB
- Drag-and-drop support
- Image preview before submission

## Validation

### Frontend Validation
- All required fields are validated before submission
- Email format validation
- File size and format validation for logo
- Real-time feedback on field focus

### Backend Validation
- Server-side validation of all inputs
- File mime-type validation
- Email format verification

## Email Notification

When a tenant applies, they receive an automated email with:
- Confirmation that we received their application
- Their submitted information summary
- Timeline for review (2-3 business days)
- Support contact information
- Privacy notice

## Accessibility Features (ADA Compliance)

✅ **Semantic HTML**
- Proper `<label>` elements for form inputs
- `<fieldset>` and `<legend>` for field grouping
- Proper heading hierarchy

✅ **ARIA Attributes**
- aria-labels for all inputs
- aria-describedby for error messages
- aria-invalid for validation states

✅ **Visual Design**
- Minimum 16px font size for readability
- Color contrast ratio exceeding WCAG AA standards
- Large interactive elements (buttons, inputs)
- Clear visual feedback for focus states

✅ **Keyboard Navigation**
- Full keyboard support for all interactive elements
- Tab order is logical and intuitive
- No keyboard traps

✅ **Error Handling**
- Clear error messages
- Easy to identify which fields have errors
- Errors are announced to screen readers

✅ **Responsive Design**
- Mobile-first approach
- Works on all screen sizes
- Readable on small devices
- Touch-friendly on mobile

## Usage

### Accessing the Registration Form
```
GET /tenant/register
```

### Submitting the Form
```
POST /tenant/register
```

**Request Format (multipart/form-data):**
```
business_name: string (required)
business_address: string (required)
owner_name: string (required)
owner_email: string, email (required)
owner_phone: string (required)
business_permit_number: string (optional)
logo: file, image, max:5120 (optional)
```

**Response:**
- On success: Redirects to `/tenant/registration-success`
- On validation error: Returns to form with error messages
- Email sent to owner_email

### Success Page
```
GET /tenant/registration-success
```

## Database

### tenant_applications table
```sql
- id (primary key)
- business_name (string)
- business_address (text)
- owner_name (string)
- owner_email (string, indexed)
- owner_phone (string)
- business_permit_number (string, nullable)
- logo_path (string, nullable)
- status (enum: pending, approved, rejected)
- notes (text, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

## File Structure

```
app/
├── Models/Central/
│   └── TenantApplication.php
├── Http/Controllers/
│   └── TenantRegistrationController.php
├── Mail/
│   └── TenantApplicationReceived.php
database/
├── migrations/
│   └── 2025_11_16_000000_create_tenant_applications_table.php
resources/
├── js/Pages/Auth/
│   ├── TenantRegistration.vue
│   └── TenantRegistrationSuccess.vue
├── views/emails/
│   └── tenant-application-received.blade.php
routes/
└── web.php (updated with registration routes)
```

## Configuration

### Storage
Logo files are stored in `storage/app/public/tenant-applications/logos/`

Make sure the public disk is properly configured:
```bash
php artisan storage:link
```

### Email
The confirmation email uses Laravel's Mail facade. Configure your email driver in `.env`:
```
MAIL_FROM_ADDRESS=noreply@storepos.online
MAIL_FROM_NAME="StorePos"
```

## Future Enhancements

- Admin dashboard to review and approve applications
- Automated tenant creation upon approval
- Payment processing for subscription selection
- Email verification
- Multi-language support
- Application status tracking for applicants
- SMS notifications

## Security Considerations

- CSRF protection on form submission
- File upload validation and scanning
- Input sanitization
- Rate limiting (can be added)
- Email verification (can be added)
- Captcha (can be added for spam prevention)

## Testing

Create a new application:
```bash
php artisan tinker
```

```php
App\Models\Central\TenantApplication::create([
    'business_name' => 'Test Business',
    'business_address' => '123 Main St',
    'owner_name' => 'John Doe',
    'owner_email' => 'john@example.com',
    'owner_phone' => '+63 9XX XXX XXXX',
    'business_permit_number' => 'BIR123456',
]);
```

## Support

For questions or issues, contact: support@storepos.online
