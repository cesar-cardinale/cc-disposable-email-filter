# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024-12-16

### Added
- Initial release of PrestaShop 9 Disposable Email Filter module
- Block customer registration with disposable email addresses
- Automatic fetching of blocklist from GitHub (4900+ domains)
- Local caching of blocklist (24-hour cache duration)
- Logging of all blocked registration attempts
- Admin configuration panel with enable/disable option
- Statistics dashboard showing:
  - Total blocked attempts
  - Number of domains in blocklist
  - Cache age
- Recent blocked attempts table with:
  - Email address
  - IP address
  - Date and time
- Multi-language support (English and French)
- Auto-update blocklist option
- Manual cache refresh button
- Database table for storing blocked attempts
- Proper security measures (input sanitization, SQL injection prevention)
- PrestaShop 9.x compatibility

### Technical Details
- Uses `actionObjectCustomerAddBefore` hook to intercept registration
- Implements efficient domain extraction and validation
- Stores logs with email, IP address, user agent, and timestamp
- Follows PrestaShop coding standards
- Includes proper index.php security files
- Provides translation files for internationalization

[1.0.0]: https://github.com/cesar-cardinale/cc-disposable-email-filter/releases/tag/v1.0.0
