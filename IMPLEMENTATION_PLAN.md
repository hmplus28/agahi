# IMPLEMENTATION PLAN - سامانه جامع آگهی

## Phase 1: Project Bootstrap + Config + PostgreSQL + Custom User
- [x] Create virtual environment
- [x] Install Django 5.2.17 and dependencies
- [ ] Create project structure (config, core, accounts, ads, taxonomy, locations, billing, moderation, support, notifications, seo)
- [ ] Configure settings (base.py, development.py, production.py)
- [ ] Create .env.example
- [ ] Setup PostgreSQL configuration
- [ ] Create Custom User model with mobile-based authentication
- [ ] Create Profile model
- [ ] Run migrations and test

## Phase 2: Category + Location Models
- [ ] Create Country, Province, City models
- [ ] Create Category model with tree structure
- [ ] Admin configuration for locations and categories
- [ ] Seed data for locations (Iran provinces and cities)
- [ ] Tests for location and category models

## Phase 3: Ad Models + Workflow + Image Pipeline
- [ ] Create Ad model with all required fields
- [ ] Create AdStatusHistory model
- [ ] Create AdImage model with optimization pipeline
- [ ] Create AdLink model
- [ ] Create AdPermit model
- [ ] Implement status workflow
- [ ] Implement duplicate detection
- [ ] Implement forbidden words check
- [ ] Persian normalization utility
- [ ] Image upload and optimization pipeline
- [ ] Tests for ad models and workflows

## Phase 4: Public Registration/Authentication + Ad Submission
- [ ] User registration form (mobile + password)
- [ ] Login/Logout views
- [ ] Password reset functionality
- [ ] Ad submission form (multi-step or single page)
- [ ] Form validation with Persian normalization
- [ ] Duplicate detection on submit
- [ ] Forbidden words validation
- [ ] Image upload handling
- [ ] Tests for authentication and ad submission

## Phase 5: User Dashboard
- [ ] User dashboard view
- [ ] My Ads list with filters
- [ ] Ad edit functionality
- [ ] Ad renew functionality
- [ ] Ad delete (soft delete)
- [ ] Permit upload for user
- [ ] View counter display
- [ ] Services display
- [ ] Tests for user dashboard

## Phase 6: Admin Dashboard + Moderation
- [ ] Custom admin dashboard with statistics
- [ ] Ad management with tabs/filters
- [ ] Ad approval/rejection workflow
- [ ] Needs permit workflow
- [ ] Activate/deactivate actions
- [ ] Restore deleted ads
- [ ] Bulk actions
- [ ] Permission system (SuperAdmin, Moderator, Support, Accounting)
- [ ] Tests for admin functionality

## Phase 7: Tariffs + Payments + Invoices
- [ ] Tariff model
- [ ] AdService model
- [ ] Order/OrderItem models
- [ ] Invoice/InvoiceItem models
- [ ] Payment model
- [ ] Payment gateway abstraction (ZarinPal dev adapter)
- [ ] Invoice generation
- [ ] Factor view for users
- [ ] Tests for billing system

## Phase 8: Tickets + Reports + Forbidden Words + Permits
- [ ] Ticket model (user + admin)
- [ ] Ticket reply system
- [ ] Report abuse model
- [ ] ForbiddenWord model + admin
- [ ] Permit review workflow
- [ ] Tests for support features

## Phase 9: SMS + Cron + Expiration + Ladder
- [ ] SMS abstraction layer (dev stub)
- [ ] Management command for expired ads
- [ ] Management command for auto ladder
- [ ] Expiration logic based on tariff
- [ ] Ladder implementation (sort_at)
- [ ] Auto ladder cron setup
- [ ] Renewal notification system
- [ ] Tests for cron commands

## Phase 10: Public Homepage + Category + Search + Ad Detail
- [ ] Homepage with categories and latest ads
- [ ] Category listing page
- [ ] City/Location filtering
- [ ] Search functionality
- [ ] Ad detail page
- [ ] Contact info display (with masking for expired)
- [ ] Related ads
- [ ] Breadcrumb navigation
- [ ] Tests for public pages

## Phase 11: SEO Implementation
- [ ] Meta tags (title, description, canonical)
- [ ] Open Graph tags
- [ ] Structured Data (JSON-LD)
- [ ] Sitemap generation
- [ ] robots.txt
- [ ] Index policy (active vs expired vs deleted)
- [ ] URL structure optimization
- [ ] Internal linking strategy
- [ ] Tests for SEO features

## Phase 12: Performance Optimization
- [ ] Database query optimization (select_related, prefetch_related)
- [ ] Index creation on frequently queried fields
- [ ] Caching strategy for static data
- [ ] Image lazy loading
- [ ] CSS/JS minification
- [ ] Core Web Vitals optimization
- [ ] N+1 query elimination
- [ ] Tests for performance

## Phase 13: Security Hardening
- [ ] CSRF protection
- [ ] XSS prevention
- [ ] SQL injection prevention
- [ ] Rate limiting on forms
- [ ] Secure headers
- [ ] Input sanitization
- [ ] File upload security
- [ ] Production security settings
- [ ] Security tests

## Phase 14: Tests
- [ ] Unit tests for models
- [ ] Unit tests for services
- [ ] Integration tests for workflows
- [ ] View tests
- [ ] Form tests
- [ ] Template tests
- [ ] End-to-end critical path tests
- [ ] Coverage report

## Phase 15: Production Deployment Documentation
- [ ] DEPLOYMENT.md for Python Hosting/cPanel/Passenger
- [ ] passenger_wsgi.py configuration
- [ ] Static files collection guide
- [ ] Media files handling
- [ ] Environment variables setup
- [ ] Database migration on production
- [ ] Cron job setup guide
- [ ] Troubleshooting guide

---

## Current Status: Starting Phase 1
