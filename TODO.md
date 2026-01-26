# Remove OCR Features and Fix Display

## Files to Delete

- [ ] app/Models/OcrResult.php
- [ ] app/Http/Controllers/OcrController.php
- [ ] app/Jobs/ProcessOcr.php
- [ ] resources/views/ocr/index.blade.php
- [ ] resources/views/ocr/create.blade.php
- [ ] resources/views/ocr/show.blade.php
- [ ] resources/css/ocr.css
- [ ] resources/js/ocr.js
- [ ] storage/app/tesseract/patterns.txt
- [ ] database/migrations/2026_01_21_155618_create_ocr_results_table.php

## Models to Update

- [ ] app/Models/User.php - Remove ocrResults relationship
- [ ] app/Models/Dataset.php - Remove ocrResults relationship

## Controllers to Update

- [ ] app/Http/Controllers/DashboardController.php - Remove OCR stats and analytics
- [ ] app/Http/Controllers/AdminController.php - Remove OCR stats and user deletion
- [ ] app/Http/Controllers/DatasetController.php - Remove previewOcr method

## Routes to Update

- [ ] routes/web.php - Remove OCR routes

## Views to Update

- [ ] resources/views/partials/sidebar.blade.php - Remove OCR menu item
- [ ] resources/views/dashboard/index.blade.php - Remove OCR stat card and quick action
- [ ] resources/views/admin/dashboard.blade.php - Remove OCR stats

## Config/Dependencies to Update

- [ ] composer.json - Remove thiagoalessio/tesseract_ocr dependency
- [ ] vite.config.js - Remove OCR CSS and JS files

## Testing

- [ ] Test dashboard loads without errors
- [ ] Test sidebar displays correctly
- [ ] Test admin dashboard works
- [ ] Run composer install to remove OCR dependency
