# PrestaShop 9 - Disposable Email Filter

A PrestaShop 9 module that blocks customer registration with disposable email addresses and logs all blocked attempts.

## Features

- ✅ Blocks registration from disposable email domains
- 📋 Automatically fetches and caches the latest blocklist from [disposable-email-domains](https://github.com/disposable-email-domains/disposable-email-domains)
- 📊 Logs all blocked registration attempts with email, IP, and timestamp
- ⚙️ Configurable admin panel with statistics
- 🔄 Auto-update blocklist (cached for 24 hours)
- 🌍 Multi-language support (English and French included)

## Installation

1. Download or clone this repository
2. Copy the `ps_disposable_email_filter` folder to your PrestaShop `modules` directory
3. Go to your PrestaShop admin panel → Modules → Module Manager
4. Search for "Disposable Email Filter"
5. Click "Install"
6. Configure the module settings as needed

## Configuration

After installation, go to the module configuration page:

- **Enable filter**: Toggle the email filtering on/off
- **Auto-update blocklist**: Enable automatic daily updates of the blocklist
- **Clear Cache**: Manually refresh the blocklist from the remote source

## Statistics Dashboard

The module provides a dashboard showing:
- Total number of blocked registration attempts
- Number of domains in the blocklist
- Cache age
- Recent blocked attempts with details (email, IP, date)

## How It Works

1. The module hooks into PrestaShop's customer registration process
2. When a user attempts to register, the module checks if the email domain is in the disposable email blocklist
3. If the domain is found in the blocklist:
   - The registration is blocked
   - The attempt is logged in the database
   - An error message is shown to the user
4. The blocklist is cached locally for 24 hours to improve performance

## Blocklist Source

The module uses the community-maintained blocklist from:
https://github.com/disposable-email-domains/disposable-email-domains

## Requirements

- PrestaShop 9.0.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## License

MIT License

## Author

Cesar Cardinale

## Support

For issues, questions, or contributions, please visit the [GitHub repository](https://github.com/cesar-cardinale/ps-disposable-email-filter).
