# Tenant Registration System - Setup Guide

## Quick Start

The tenant registration system is now fully implemented and ready to use!

### What's Been Created

#### 1. **Frontend Components**
- `resources/js/Pages/Auth/TenantRegistration.vue` - Registration form
- `resources/js/Pages/Auth/TenantRegistrationSuccess.vue` - Success page

#### 2. **Backend Components**
- `app/Http/Controllers/TenantRegistrationController.php` - Handles form requests
- `app/Models/Central/TenantApplication.php` - Database model
- `app/Mail/TenantApplicationReceived.php` - Email notification

#### 3. **Database**
- `database/migrations/2025_11_16_000000_create_tenant_applications_table.php` - Schema
- Migration already run ✅

#### 4. **Admin Panel (Filament)**
- `app/Filament/Resources/TenantApplicationResource.php` - Admin CRUD interface
- Full set of page classes for viewing, editing, and creating applications

#### 5. **Routes**
- `GET /tenant/register` - Display registration form
- `POST /tenant/register` - Submit registration
- `GET /tenant/registration-success` - Success confirmation

#### 6. **Email Template**
- `resources/views/emails/tenant-application-received.blade.php` - Confirmation email

---

## Installation Steps

### 1. Configure Mail (if not already done)
Update your `.env` file:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@storepos.online
MAIL_FROM_NAME="StorePos"
```

### 2. Link Storage (for logo uploads)
```bash
php artisan storage:link
```

### 3. Access the Registration Page
```
http://quickjuan.site/tenant/register
```

### 4. Admin Dashboard
Access the admin panel at:
```
http://quickjuan.site/filament
```
Then navigate to **Tenant Applications** in the sidebar.

---

## User Experience Features

### ✅ ADA Compliance
- Large, readable fonts (16px minimum)
- High contrast colors
- Proper ARIA labels and descriptions
- Full keyboard navigation
- Screen reader optimized

### ✅ User-Friendly
- Clear, step-by-step form sections
- Real-time validation feedback
- Large, easy-to-click buttons
- Drag-and-drop logo upload
- Mobile-responsive design
- Support text for each field

### ✅ Intuitive for Elders
- Simple language (no jargon)
- Large text and buttons
- Clear visual feedback
- Optional fields clearly marked
- Clear success messaging
- Easy error recovery

---

## How It Works

### User Registration Flow
1. User visits `/tenant/register`
2. Fills in business and owner information
3. Optionally uploads business logo (drag-and-drop supported)
4. Clicks "Submit Application"
5. Application is saved to database
6. **Confirmation email sent to owner email**
7. User redirected to success page

### Admin Workflow
1. Admin logs into Filament dashboard
2. Clicks "Tenant Applications" in sidebar
3. Views list of all applications (pending/approved/rejected)
4. Can:
   - View application details
   - Edit status (pending → approved → rejected)
   - Add notes
   - Download/view submitted logo
5. Once approved, manually create tenant through:
   - Artisan command: `php artisan tenant:create`
   - Or programmatically using the Tenant model

---

## File Locations

```
app/
├── Http/Controllers/
│   └── TenantRegistrationController.php
├── Mail/
│   └── TenantApplicationReceived.php
├── Models/Central/
│   └── TenantApplication.php
└── Filament/Resources/
    ├── TenantApplicationResource.php
    └── TenantApplicationResource/Pages/
        ├── ListTenantApplications.php
        ├── CreateTenantApplication.php
        ├── ViewTenantApplication.php
        └── EditTenantApplication.php

database/
└── migrations/
    └── 2025_11_16_000000_create_tenant_applications_table.php

resources/
├── js/Pages/Auth/
│   ├── TenantRegistration.vue
│   └── TenantRegistrationSuccess.vue
└── views/emails/
    └── tenant-application-received.blade.php

routes/
└── web.php (updated)
```

---

## Database Schema

### tenant_applications Table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| business_name | string | Name of the business |
| business_address | text | Full address |
| owner_name | string | Owner's full name |
| owner_email | string | Owner's email (indexed) |
| owner_phone | string | Owner's phone |
| business_permit_number | string | Optional permit number |
| logo_path | string | Path to uploaded logo |
| status | enum | pending/approved/rejected |
| notes | text | Admin notes |
| created_at | timestamp | Application timestamp |
| updated_at | timestamp | Last update |

---

## Validation Rules

### Frontend
- All required fields validated before submission
- Email format verified
- Logo file size max 5MB
- Logo must be image type (JPG, PNG, GIF)

### Backend
```php
'business_name' => 'required|string|max:255'
'business_address' => 'required|string|max:500'
'owner_name' => 'required|string|max:255'
'owner_email' => 'required|email|max:255'
'owner_phone' => 'required|string|max:20'
'business_permit_number' => 'nullable|string|max:255'
'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120'
```

---

## Email Configuration

### Confirmation Email
Sent automatically when application submitted to:
- **To:** owner_email (from form)
- **Subject:** "We Received Your StorePos Application - Thank You!"
- **Template:** `resources/views/emails/tenant-application-received.blade.php`

**Email Contents:**
- Acknowledgment of application received
- Summary of submitted information
- Timeline for review (2-3 business days)
- Support contact information
- Privacy notice

---

## Testing

### Quick Test in Tinker
```bash
php artisan tinker
```

```php
App\Models\Central\TenantApplication::create([
    'business_name' => 'Test Cafe',
    'business_address' => '123 Main Street, City',
    'owner_name' => 'John Doe',
    'owner_email' => 'john@example.com',
    'owner_phone' => '+63 912 345 6789',
    'business_permit_number' => 'BIR-2025-001',
]);
```

### Manual Browser Test
1. Navigate to `http://quickjuan.site/tenant/register`
2. Fill in all required fields
3. Optionally upload a logo
4. Submit form
5. Should see success page
6. Check email inbox for confirmation

---

## Future Enhancements

### Planned Features
- [ ] Email verification
- [ ] Application status tracking for applicants
- [ ] SMS notifications
- [ ] Multi-language support
- [ ] Payment processing integration
- [ ] Automated tenant creation
- [ ] Captcha for spam prevention
- [ ] Rate limiting
- [ ] Admin approval email notifications

### Extension Points
- Add more fields to application
- Integrate with payment providers
- Add document upload (business license, ID)
- Connect to tenant auto-provisioning
- Add real-time SMS updates
- Create webhook for external integrations

---

## Troubleshooting

### Logo Not Uploading
- Check storage is linked: `php artisan storage:link`
- Verify `storage/app/public` is writable: `chmod -R 755 storage`
- Check file size (max 5MB)
- Verify MIME type is image

### Email Not Sending
- Check `.env` mail configuration
- Verify email service is running
- Check application logs: `tail -f storage/logs/laravel.log`
- Test mail config: `php artisan tinker` → `Mail::raw('Test', fn($m) => $m->to('test@example.com'))`

### Validation Errors
- All error messages displayed on form
- Check browser console for JavaScript errors
- Verify all required fields filled
- Check email format if email validation fails

---

## Support

For questions or issues:
- Email: support@storepos.online
- Check logs: `storage/logs/laravel.log`
- Admin panel: `/filament` (Tenant Applications section)

---

## Summary

✅ **Production Ready**
- ADA compliant
- Mobile responsive
- User-friendly interface
- Secure form handling
- Email notifications
- Admin management dashboard
- Full validation

The system is ready to accept business owner applications!
