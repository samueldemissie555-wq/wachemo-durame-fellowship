# Durame Campus Fellowship — Advanced Bilingual Website

This build follows the supplied reference screenshot: compact white navigation, dark navy/gold page heroes, quick-access cards, event/sermon/resource cards, social cards, location section and a professional admin dashboard.

## Main features
- English + Amharic on every public page
- Direct database-backed Service Registration with Amharic/English form
- Admin service registration management, ministry/team filters, edit/archive/delete and Print/Save-as-PDF export
- InfinityFree database configuration for the supplied database
- - Admin credentials are configured privately on the hosting server.
- Responsive mobile navigation
- Advanced CSS hover, card, hero and accessibility states
- Relative CSS/JS URLs to avoid hard-coded-domain failures
- Prayer request storage
- Dynamic events, sermons, resources, albums and announcements when database data exists


## Database

The website uses MySQL for dynamic content, registrations, forms, and administration.

Database connection credentials are configured privately on the hosting server and are not stored in this public repository.

Never publish database passwords or other private credentials in GitHub.

## Registration
`register-service.php` stores Full Name, Department, Phone Number, Year, Services (Team), and Services (Mobilizations) directly in MySQL. Phone number is unique to prevent duplicate registrations.

In Admin → Service Registrations, filter by a ministry/team and choose **Download / Print PDF**. The print view is A4 landscape and can be saved as PDF from Chrome/Edge/Firefox.

## Upload
Upload the **contents of `htdocs`** into the InfinityFree `htdocs` directory. Do not create `htdocs/htdocs`.


Visual upgrade: September 2026 premium responsive design applied to the existing WCUDC PHP/MySQL site. All existing pages, admin panel, registration and database logic are preserved.
