# Database Seeders

This directory contains database seeders for populating the application with sample data.

## Available Seeders

### CompanySeeder

Seeds the database with sample company data including:

-   5 predefined companies with realistic data
-   15 additional random companies generated using the factory
-   **Related Data**: Addresses, contacts, and notes for each company

**Companies include:**

-   TechCorp Solutions (Technology)
-   HealthFirst Medical (Healthcare)
-   Global Finance Group (Finance)
-   EduTech Innovations (Education)
-   Manufacturing Plus (Manufacturing)

### AreaSeeder

Seeds the database with sample area data including:

-   8 predefined areas with detailed descriptions
-   12 additional random areas generated using the factory
-   **Related Data**: Locations for each area

**Areas include:**

-   North Business District
-   South Industrial Zone
-   East Residential Quarter
-   West Technology Hub
-   Central Downtown
-   Metropolitan Financial District
-   Suburban Shopping Center
-   Urban Arts District

## Usage

### Run All Seeders

```bash
php artisan db:seed
```

### Run Specific Seeders

```bash
# Run only company seeder
php artisan db:seed --class=CompanySeeder

# Run only area seeder
php artisan db:seed --class=AreaSeeder
```

### Run with Fresh Database

```bash
# Fresh migration and seed
php artisan migrate:fresh --seed

# Fresh migration and seed specific seeder
php artisan migrate:fresh --seed --seeder=CompanySeeder
```

## Testing

Run the seeder tests to verify functionality:

```bash
php artisan test tests/Feature/SeederTest.php
```

## Factory Methods

All models have factory methods available:

### Company Factory

```php
// Create a single company
Company::factory()->create();

// Create multiple companies
Company::factory(5)->create();

// Create active companies only
Company::factory()->active()->create();

// Create inactive companies only
Company::factory()->inactive()->create();
```

### Area Factory

```php
// Create a single area
Area::factory()->create();

// Create multiple areas
Area::factory(5)->create();

// Create active areas only
Area::factory()->active()->create();

// Create inactive areas only
Area::factory()->inactive()->create();
```

### CompanyAddress Factory

```php
// Create a single address
CompanyAddress::factory()->create();

// Create specific type addresses
CompanyAddress::factory()->billing()->create();
CompanyAddress::factory()->shipping()->create();
CompanyAddress::factory()->office()->create();
```

### Contact Factory

```php
// Create a single contact
Contact::factory()->create();

// Create primary contact
Contact::factory()->primary()->create();

// Create secondary contact
Contact::factory()->secondary()->create();
```

### CompanyNote Factory

```php
// Create a single note
CompanyNote::factory()->create();

// Create active note
CompanyNote::factory()->active()->create();

// Create inactive note
CompanyNote::factory()->inactive()->create();
```

## Data Structure

### Company Fields

-   `name`: Company name
-   `industry`: Business industry type
-   `website`: Company website URL
-   `email`: Company email address
-   `phone`: Company phone number
-   `status`: Active/Inactive status
-   `created_by`: User ID who created the record
-   `updated_by`: User ID who last updated the record

### Area Fields

-   `name`: Area name
-   `description`: Detailed area description
-   `code`: Unique area code
-   `status`: Active/Inactive status
-   `created_by`: User ID who created the record
-   `updated_by`: User ID who last updated the record

### CompanyAddress Fields

-   `company_id`: ID of the company this address belongs to
-   `type`: Address type (billing, shipping, office, warehouse)
-   `address`: Street address
-   `city`: City name
-   `state`: State/province name
-   `country`: Country name
-   `zip_code`: Postal/ZIP code

### Contact Fields

-   `company_id`: ID of the company this contact belongs to
-   `name`: Contact person's name
-   `email`: Contact's email address
-   `phone`: Contact's phone number
-   `designation`: Contact's job title/position
-   `is_primary`: Whether this is the primary contact

### CompanyNote Fields

-   `company_id`: ID of the company this note belongs to
-   `note`: Note content/text
-   `created_by`: User ID who created the note
-   `status`: Note status (active/inactive)

## Notes

-   All seeded records are created with `created_by` and `updated_by` set to user ID 1 (admin user)
-   The seeders use realistic data that makes sense for a CRM system
-   Random data is generated using Laravel's Faker library for variety
-   Status fields default to active (true) for most records
-   **Enhanced Seeding**: Companies automatically get addresses, contacts, and notes
-   **Enhanced Seeding**: Areas automatically get associated locations
-   **Relationship Management**: All related data is properly linked using foreign keys
-   **Factory Support**: All models have comprehensive factory methods for testing and development
