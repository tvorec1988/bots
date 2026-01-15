# Changelog

All notable changes to the Premium Companies plugin for Joomla will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-01-15

### Added
- Initial release of Premium Companies plugin
- Integration between DJ-Classifieds and J-Business Directory
- Automatic display of premium companies when viewing classified ads
- Category-based matching between ads and companies
- Three display styles: Cards, List, and Compact
- Configurable maximum number of companies to display (1-20)
- Display position options: before content, after content, sidebar
- Company logo display with toggle option
- Company contact information display (phone, email, website)
- Premium badge highlighting
- Responsive design for mobile and desktop devices
- Custom CSS support for advanced styling
- Multilingual support (English and Russian)
- Installation script with dependency checks
- Automatic plugin enablement on installation
- Comprehensive configuration options in plugin settings
- Error handling and logging
- SEO-friendly company URLs using Joomla routing
- Active package expiration checking
- Featured companies prioritization

### Technical Features
- Compatible with Joomla 4.0+ and Joomla 5.x
- Minimum PHP version: 7.4
- Follows Joomla coding standards
- Uses Joomla database abstraction layer
- Implements content plugin events (onContentBeforeDisplay, onContentAfterDisplay)
- Automatic language file loading
- Asset management for CSS and JS files
- Exception handling for database errors
- SQL injection protection through prepared statements

### Security
- Proper input sanitization
- XSS protection using htmlspecialchars()
- SQL injection prevention using Joomla Query Builder
- Secure URL generation using Joomla Router

### Performance
- Optimized database queries with proper indexing
- Limited number of results to prevent performance issues
- Efficient company filtering and sorting
- Minimal JavaScript footprint

### Documentation
- Comprehensive README in English and Russian
- Inline code documentation
- Installation and configuration guide
- Troubleshooting section
- Usage examples

### Future Enhancements (Planned)
- Module position support for sidebar display
- AJAX loading for better performance
- Company rating display
- Distance-based sorting (if geographic data available)
- Advanced filtering options (by services, price range, etc.)
- Integration with Joomla Smart Search
- Support for custom fields from both components
- Email notification to companies when they're suggested
- Analytics dashboard for company impressions and clicks
- A/B testing for different display styles

## [Unreleased]

### Planned for 1.1.0
- [ ] Module version for more flexible placement
- [ ] Cache support for company queries
- [ ] Integration with Joomla Smart Search
- [ ] Additional language packs (German, French, Spanish)
- [ ] Enhanced mobile experience
- [ ] Dark mode support

---

For support, bug reports, or feature requests, please visit the project repository.
