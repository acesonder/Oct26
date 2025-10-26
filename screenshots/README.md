# Screenshots Directory

This directory contains screenshots for the OUTSINC platform user manual and help documentation.

## Directory Structure

```
screenshots/
├── auth/               # Authentication pages (login, registration, password reset)
├── client/             # Client-specific features
├── worker/             # Outreach worker features
├── provider/           # Service provider features
├── admin/              # Administrator features
├── common/             # Common features (messaging, notifications, themes)
└── troubleshooting/    # Error states and troubleshooting guides
```

## Screenshot Guidelines

### Technical Requirements

- **Resolution**: 1920x1080 pixels minimum
- **Format**: PNG (for clarity and transparency support)
- **Quality**: High quality, no compression artifacts
- **Browser**: Use Chrome or Firefox with default zoom (100%)
- **Theme**: Capture in both light and dark themes where applicable

### Naming Convention

Use descriptive, kebab-case names:

```
[category]/[feature]-[description].png
```

Examples:
- `client/dashboard-overview.png`
- `auth/login-page.png`
- `admin/user-management-list.png`

### Content Guidelines

1. **Privacy**: 
   - Use dummy/test data only
   - No real user information
   - Anonymize all sensitive data

2. **Clarity**:
   - Ensure all text is readable
   - Highlight important features
   - Show complete workflows

3. **Consistency**:
   - Use consistent test data across screenshots
   - Same user profile where applicable
   - Maintain visual consistency

### Test Data Suggestions

**Test Users**:
- Client: Jane Doe (janedoe)
- Worker: John Smith (johnsmith)
- Admin: System Admin (admin)

**Sample Content**:
- Goals: "Find stable housing", "Complete job training"
- Journal entries: Generic, trauma-informed examples
- Messages: Professional, supportive communication

## Screenshot Checklist

See `MANUAL.md` for the complete list of required screenshots organized by role.

### Authentication Screenshots
- [ ] registration.png
- [ ] login.png
- [ ] password-reset.png

### Client Screenshots
- [ ] dashboard.png
- [ ] dashboard-stats.png
- [ ] mood-checkin.png
- [ ] recent-goals.png
- [ ] quick-actions.png
- [ ] goals.png
- [ ] create-goal.png
- [ ] goal-details.png
- [ ] journal.png
- [ ] journal-entry.png
- [ ] services.png
- [ ] services-search.png
- [ ] service-details.png
- [ ] settings.png
- [ ] settings-profile.png
- [ ] settings-password.png
- [ ] settings-tour.png
- [ ] settings-theme.png

### Worker Screenshots
- [ ] dashboard.png
- [ ] dashboard-stats.png
- [ ] worker-quick-actions.png
- [ ] case-management.png
- [ ] create-case.png
- [ ] case-notes.png
- [ ] case-workflow.png
- [ ] safety-checkin.png
- [ ] safety-before.png
- [ ] safety-after.png
- [ ] outreach-logs.png
- [ ] create-outreach-log.png
- [ ] client-overview.png
- [ ] client-list.png
- [ ] client-profile.png

### Service Provider Screenshots
- [ ] dashboard.png
- [ ] service-management.png
- [ ] add-service.png
- [ ] referrals.png
- [ ] referral-workflow.png

### Admin Screenshots
- [ ] dashboard.png
- [ ] system-overview.png
- [ ] user-management.png
- [ ] user-list.png
- [ ] user-details.png
- [ ] user-actions.png
- [ ] approvals.png
- [ ] approval-queue.png
- [ ] approval-review.png
- [ ] system-health.png
- [ ] health-checks.png
- [ ] performance-metrics.png
- [ ] maintenance-tools.png
- [ ] audit-logs.png
- [ ] audit-log-types.png
- [ ] audit-log-viewer.png
- [ ] security-monitoring.png

### Common Screenshots
- [ ] welcome-tour.png
- [ ] messenger.png
- [ ] messenger-features.png
- [ ] messenger-compose.png
- [ ] online-status.png
- [ ] notifications.png
- [ ] notification-types.png
- [ ] notification-settings.png
- [ ] theme-light.png
- [ ] theme-dark.png
- [ ] theme-toggle.png
- [ ] accessibility.png
- [ ] accessibility-features.png
- [ ] accessibility-settings.png
- [ ] help-section.png

### Troubleshooting Screenshots
- [ ] login-error.png

## Tools for Screenshots

### Recommended Tools

1. **Browser DevTools**:
   - Chrome DevTools (F12)
   - Firefox DevTools (F12)
   - Set device size to 1920x1080

2. **Screenshot Extensions**:
   - Awesome Screenshot
   - Nimbus Screenshot
   - Fireshot

3. **Image Editing**:
   - GIMP (free, open source)
   - Paint.NET (Windows)
   - Preview (macOS)

### Capturing Screenshots

#### Browser DevTools Method:
1. Open DevTools (F12)
2. Click device toolbar (Ctrl+Shift+M)
3. Set dimensions to 1920x1080
4. Use screenshot tool
5. Save as PNG

#### Extension Method:
1. Install screenshot extension
2. Navigate to page
3. Click extension icon
4. Select "Capture visible part" or "Full page"
5. Save as PNG

## Adding Screenshots to Manual

1. Capture screenshot according to guidelines
2. Save in appropriate directory
3. Reference in MANUAL.md using:
   ```markdown
   **Screenshot**: `screenshots/category/filename.png`
   ```

## Screenshot Annotations (Optional)

For tutorial purposes, you may add:
- Arrows pointing to key features
- Numbered steps
- Callout boxes with explanations

Use tools like:
- Skitch
- Snagit
- Annotate (macOS)

## Quality Checklist

Before adding a screenshot to the manual:

- [ ] Is the resolution at least 1920x1080?
- [ ] Is the image clear and readable?
- [ ] Are all UI elements visible?
- [ ] Is test/dummy data used (no real user info)?
- [ ] Is the filename descriptive and follows naming convention?
- [ ] Is it saved in the correct directory?
- [ ] Is it referenced correctly in MANUAL.md?

## Updating Screenshots

Screenshots should be updated when:
- UI design changes significantly
- New features are added
- Existing features are modified
- User feedback indicates confusion

## Contact

For questions about screenshots or to submit new ones:
- Create an issue in the repository
- Contact the documentation team
- Submit a pull request with updated screenshots

---

**Note**: This is a living document. Update this README as screenshot requirements change.
