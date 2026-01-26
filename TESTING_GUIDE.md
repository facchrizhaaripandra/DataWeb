# Thorough Testing Guide for Railway Deployment

## Prerequisites

- App is successfully deployed on Railway without "Application failed to respond" error
- PostgreSQL database is connected and migrations have run
- You can access the app URL

## Testing Checklist

### 1. Basic Application Access

- [ ] App loads without errors (no 500/502/503 errors)
- [ ] Homepage (`/`) loads correctly
- [ ] Public health check (`/health`) returns success
- [ ] Public routes work (e.g., `/public/health`)

### 2. Authentication System

- [ ] Register page (`/register`) loads
- [ ] User registration works (create account)
- [ ] Login page (`/login`) loads
- [ ] User login works
- [ ] Logout functionality works
- [ ] Protected routes redirect to login when not authenticated

### 3. Dashboard and Analytics

- [ ] Dashboard (`/dashboard`) loads for authenticated users
- [ ] Analytics page (`/analytics`) loads
- [ ] API endpoints work:
    - [ ] `/api/analytics/datasets` returns data
    - [ ] `/api/analytics/imports` returns data

### 4. Dataset Management (CRUD Operations)

- [ ] Datasets index (`/datasets`) loads
- [ ] Create dataset (`/datasets/create`) works
- [ ] Dataset creation saves to database
- [ ] Dataset show page (`/datasets/{id}`) displays data
- [ ] Dataset edit (`/datasets/{id}/edit`) works
- [ ] Dataset update saves changes
- [ ] Dataset deletion works
- [ ] Dataset export functionality works (CSV/Excel)

### 5. Dataset Row Operations

- [ ] Add row to dataset works
- [ ] Edit row functionality works
- [ ] Delete row works
- [ ] Bulk row operations work
- [ ] Row search functionality works
- [ ] Column operations (add/rename/delete) work
- [ ] Column type changes work

### 6. Dataset Sharing

- [ ] Share dataset with users works
- [ ] Shared users list displays correctly
- [ ] Access permissions work for shared datasets
- [ ] Bulk sharing operations work

### 7. Import Functionality

- [ ] Import index (`/imports`) loads
- [ ] Create import (`/imports/create`) works
- [ ] File upload works
- [ ] Import preview works
- [ ] Import processing works (check status via `/api/imports/status/{id}`)
- [ ] Import retry functionality works

### 8. Admin Panel

- [ ] Admin dashboard (`/admin/dashboard`) loads (admin user required)
- [ ] User management works
- [ ] System settings work
- [ ] Log viewing works
- [ ] Backup functionality works
- [ ] Cache clearing works

### 9. Profile Management

- [ ] Profile edit (`/profile`) works
- [ ] Password change works
- [ ] Profile updates save correctly

### 10. Public Dataset Access

- [ ] Public dataset URLs (`/public/dataset/{id}`) work for public datasets
- [ ] Public access respects privacy settings

### 11. API Endpoints

Test these endpoints with curl or browser:

- [ ] `GET /api/datasets/{id}/rows` - returns dataset rows
- [ ] `POST /api/datasets/{id}/search` - search functionality
- [ ] `GET /api/datasets/{id}/stats` - dataset statistics
- [ ] `GET /api/admin/stats` - admin statistics
- [ ] `GET /api/admin/backups` - backup list

### 12. Error Handling

- [ ] Invalid URLs return 404
- [ ] Unauthorized access returns 403/redirect to login
- [ ] Database errors are handled gracefully
- [ ] File upload errors are handled

### 13. Performance and Security

- [ ] Page load times are reasonable (< 3 seconds)
- [ ] No sensitive data exposed in responses
- [ ] CSRF protection works
- [ ] Session management works
- [ ] File uploads are secure

### 14. Mobile/Responsive Design

- [ ] App works on mobile devices
- [ ] Tables are responsive
- [ ] Navigation works on small screens

## Testing Commands (Run in Railway CLI or local)

```bash
# Check Laravel status
php artisan tinker --execute="echo 'Laravel is working';"

# Check database connection
php artisan tinker --execute="DB::connection()->getPdo(); echo 'DB connected';"

# Run migrations status
php artisan migrate:status

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Run tests (if any)
php artisan test
```

## Reporting Issues

If any test fails, note:

- URL where error occurred
- Error message/code
- Steps to reproduce
- Expected vs actual behavior
- Browser/console logs

## Completion Criteria

- [ ] All critical functionality works
- [ ] No 5xx errors
- [ ] Database operations work
- [ ] User authentication/authorization works
- [ ] All major features functional
- [ ] Performance is acceptable
