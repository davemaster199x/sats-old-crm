# Property Management CRM System

## Overview

A comprehensive Customer Relationship Management (CRM) system built in native PHP, specifically designed for property and agency management. This system integrates booking management, task tracking, and agency operations into a unified platform.

## System Structure

### Core Directories

```
├── ajax/                 # AJAX request handlers
├── BitlyPHP/            # URL shortener integration
├── cronjobs/            # Scheduled tasks
├── css/                 # Stylesheet files
├── fancybox/            # Modal window functionality
├── images/              # Image assets
├── inc/                 # Include files
├── ion_sound/           # Audio notifications
├── js/                  # JavaScript files
├── signature/           # Digital signature handling
├── logs/                # System logs
└── vendor/              # Third-party dependencies
```

### Core Functionality

#### Agency Management

- `add_agency.php` - Agency creation and management
- `add_agency_audit.php` - Agency audit trail
- `add_agency_booking_notes.php` - Booking notes management
- `add_agency_target_static.php` - Agency target handling
- `add_agency_static.php` - Static agency information

#### Property Management

- `active_properties.php` - Active property listings
- `accommodation_process.php` - Accommodation processing
- `add_alarm_pricing.php` - Pricing alerts
- `add_alarm_script.php` - Alarm system scripts

#### Calendar and Scheduling

- `add_calendar_entry.php` - Calendar entry management
- `add_calendar_entry_popup.php` - Pop-up calendar entries
- `add_calendar_entry_static.php` - Static calendar data

#### CRM Core Features

- `add_crm_page.php` - CRM page management
- `add_crm_task.php` - Task management system
- `add_crm_member.php` - Member management
- `accounts_logs.php` - Account activity logging

#### Financial Management

- `add_expense.php` - Expense tracking
- `add_agency_target.php` - Target management

## Installation Requirements

### Server Requirements

- PHP 7.4 or higher
- MySQL 5.7+
- Apache 2.4+
- mod_rewrite enabled

### Required PHP Extensions

- PDO
- MySQLi
- curl
- json
- session

## Installation Steps

1. Clone the repository to your web server

```bash
git clone [repository-url]
```

2. Configure database connection

```bash
cp inc/config.example.php inc/config.php
# Edit config.php with your database credentials
```

3. Set directory permissions

```bash
chmod 755 -R ./
chmod 777 -R logs/
chmod 777 -R uploads/
```

4. Import database schema

```bash
mysql -u [username] -p [database] < database/schema.sql
```

## Key Features

### Agency Management

- Complete agency profile management
- Booking and notes system
- Audit trail tracking
- Target setting and monitoring

### Property Handling

- Active property listing management
- Accommodation processing
- Pricing alerts and notifications
- Property status tracking

### Scheduling System

- Integrated calendar system
- Pop-up notifications
- Event management
- Booking coordination

### Financial Features

- Expense tracking
- Agency target monitoring
- Financial reporting
- Account logging

### Additional Features

- Digital signature integration
- URL shortening via Bitly
- Sound notifications
- AJAX-powered interface
- Modal windows for enhanced UX

## Security Features

- Session management
- Audit logging
- User authentication
- Access control
- Input validation

## Maintenance

### Regular Tasks

- Check log files in `/logs`
- Monitor cronjob execution
- Review audit trails
- Backup database regularly
- Update third-party dependencies

### Troubleshooting

- Check `/logs` for error messages
- Verify file permissions
- Monitor server error logs
- Check database connectivity

## Development Guidelines

### Coding Standards

- Follow PHP PSR-12 standards
- Document all functions
- Use prepared statements for queries
- Implement error handling
- Follow naming conventions

### File Organization

- Keep related files in appropriate directories
- Maintain separation of concerns
- Use include files for common functions
- Keep configuration in dedicated files

## Support and Maintenance

- Internal documentation in `/inc/docs`
- System logs in `/logs`
- Error tracking in error logs
- Regular backup procedures

## Version Control

- Use Git for version control
- Follow branching strategy
- Review `.gitignore` for excluded files
- Maintain clean commit history

---

Last Updated: November 2024
Version: 1.0.0
