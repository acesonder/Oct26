# Welcome Tour System - Developer Guide

This document explains how the welcome tour system works and how to customize it.

## Overview

The welcome tour system provides an interactive guided experience for new users, highlighting key features with Post-it note style tooltips. The tour is role-based, showing different steps for clients, workers, service providers, and administrators.

## Components

### 1. Database Schema

**Table**: `user_tour_progress`

Tracks tour completion status for each user:
- `user_id`: Foreign key to users table
- `tour_completed`: Boolean flag
- `tour_skipped`: Boolean flag
- `current_step`: Last viewed step
- `completed_at`: Timestamp of completion

### 2. JavaScript Files

**`assets/js/welcome-tour.js`**
- Core WelcomeTour class
- Handles tour display, navigation, and persistence
- Creates overlay, tooltips, and skip button

**`assets/js/tour-init.js`**
- Tour configuration (steps for each role)
- Initialization logic
- Role-based step selection

### 3. API Endpoint

**`api/tour-progress.php`**
- Saves tour progress to database
- Retrieves tour status
- Handles reset functionality

### 4. User Interface

**Settings Page** (`modules/client/settings.php`)
- Allows users to restart the tour
- Accessible from user menu

**Help Page** (`modules/common/help.php`)
- Contains comprehensive manual
- Role-based documentation access

## How It Works

### Tour Initialization Flow

1. User logs in and lands on dashboard
2. `tour-init.js` loads and checks tour status via API
3. If tour not completed:
   - Selects appropriate steps based on user role
   - Creates WelcomeTour instance
   - Starts tour after 1 second delay
4. User navigates through steps or skips
5. Progress saved to database
6. On completion, tour marked as done

### Tour Display

1. **Overlay**: Semi-transparent dark background
2. **Highlight**: Target element highlighted with orange glow
3. **Tooltip**: Post-it note style tooltip with:
   - Title (feature name)
   - Content (feature description)
   - Navigation buttons (Back/Next)
   - Progress indicator (Step X of Y)
4. **Skip Button**: Top-right corner, always visible

### Data Flow

```
User Action → JavaScript → API → Database
     ↓
Tour State Updated → Local Storage + Database
     ↓
UI Updates Accordingly
```

## Customization

### Adding Tour Steps

Edit `assets/js/tour-init.js`:

```javascript
const clientTourSteps = [
    {
        element: '.feature-selector',    // CSS selector
        title: 'Feature Name',           // Title shown in tooltip
        content: 'Feature description',  // Description text
        position: 'bottom'               // Tooltip position: top/bottom/left/right
    },
    // ... more steps
];
```

### Creating Role-Specific Tours

```javascript
// For workers
const workerTourSteps = [
    { element: '.worker-feature', title: 'Title', content: 'Content', position: 'bottom' }
];

// For admins
const adminTourSteps = [
    { element: '.admin-feature', title: 'Title', content: 'Content', position: 'bottom' }
];
```

### Customizing Tour Appearance

Edit styles in `assets/js/welcome-tour.js`:

**Overlay**:
```javascript
this.overlay.style.cssText = `
    background: rgba(0, 0, 0, 0.5);  // Change opacity here
    ...
`;
```

**Tooltip**:
```javascript
this.tooltip.style.cssText = `
    background: #FFF9C4;             // Yellow Post-it color
    border: 2px solid #F9A825;       // Border color
    ...
`;
```

**Highlight**:
```css
.tour-highlight {
    box-shadow: 0 0 0 4px rgba(245, 124, 0, 0.5);  // Orange glow
}
```

### Modifying Tour Behavior

**Change tour delay**:
```javascript
// In tour-init.js
setTimeout(() => {
    tour.start();
}, 1000);  // Change delay here (milliseconds)
```

**Disable skip button**:
```javascript
const tour = new WelcomeTour(tourSteps, {
    showSkipButton: false  // No skip button
});
```

**Force tour to show**:
```javascript
const tour = new WelcomeTour(tourSteps, {
    forceStart: true  // Show even if completed
});
```

## Testing

### Manual Testing

1. **Start Tour**:
   - Create new user or reset tour
   - Login and navigate to dashboard
   - Tour should start automatically

2. **Test Navigation**:
   - Click "Next" through all steps
   - Click "Back" to review previous steps
   - Verify all tooltips display correctly

3. **Test Skip**:
   - Click "Skip Tour" button
   - Confirm dialog appears
   - Verify tour closes and marks as skipped

4. **Test Restart**:
   - Go to Settings
   - Click "Restart Welcome Tour"
   - Reload page and verify tour starts

### Automated Testing

A test HTML file can be created for manual browser testing. Create a file at `/tests/tour-test.html`:

```html
<!DOCTYPE html>
<!-- See tour test implementation in repository tests directory -->
```

Or test directly on the dashboard page after setup.

### API Testing

Test the API endpoint (replace `YOUR_DOMAIN` with your actual domain):

```bash
# Get tour status
curl -X GET https://YOUR_DOMAIN/api/tour-progress.php \
  -H "Cookie: PHPSESSID=your_session_id"

# Update progress
curl -X POST https://YOUR_DOMAIN/api/tour-progress.php \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=your_session_id" \
  -d '{"current_step": 2}'

# Reset tour
curl -X POST https://YOUR_DOMAIN/api/tour-progress.php \
  -H "Content-Type: application/json" \
  -H "Cookie: PHPSESSID=your_session_id" \
  -d '{"reset": true}'
```

## Troubleshooting

### Tour Not Starting

**Check**:
1. JavaScript files loaded: View page source, check for script tags
2. Console errors: Open browser DevTools (F12)
3. User data attributes: Verify `data-user-role` and `data-username` on body tag
4. Tour completion status: Check localStorage and database

**Fix**:
```javascript
// In browser console
localStorage.removeItem('tour_completed');
location.reload();
```

### Tooltip Not Positioning Correctly

**Check**:
1. Target element exists: Verify CSS selector is correct
2. Element is visible: Element must be in viewport
3. Position parameter: Try different positions (top/bottom/left/right)

**Fix**:
Update position in tour step configuration or adjust positioning logic in `positionTooltip()` function.

### Tour Progress Not Saving

**Check**:
1. API endpoint accessible: Check network tab in DevTools
2. User authenticated: Verify session active
3. Database table exists: Check if `user_tour_progress` table created

**Fix**:
```sql
-- Create table if missing
CREATE TABLE IF NOT EXISTS user_tour_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tour_completed BOOLEAN DEFAULT FALSE,
    tour_skipped BOOLEAN DEFAULT FALSE,
    current_step INT DEFAULT 0,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_tour (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Best Practices

### Content Writing

1. **Keep it concise**: Short, clear descriptions
2. **Be welcoming**: Friendly, supportive tone
3. **Highlight value**: Explain why feature is useful
4. **Use emojis**: Makes it friendly and visual
5. **Action-oriented**: Tell users what they can do

### Step Ordering

1. **Start with welcome**: Greet user, show username
2. **Most important first**: Core features before advanced
3. **Logical flow**: Related features together
4. **End with customization**: Settings, themes last

### Element Selection

1. **Use stable selectors**: Classes over IDs
2. **Avoid dynamic content**: Elements that always exist
3. **Visible elements**: Must be on initial page load
4. **Unique targets**: One element per step

### Performance

1. **Lazy load**: Only load tour scripts on dashboard
2. **Optimize images**: Compress screenshot placeholders
3. **Cache API calls**: Store tour status locally
4. **Debounce events**: Prevent excessive API calls

## Integration with Other Features

### With Onboarding

Combine tour with user onboarding:
1. User registers
2. Email confirmation
3. Profile setup
4. **Welcome tour** ← Shows after profile complete
5. First action prompts

### With Help System

Link tour to help documentation:
- Tour highlights features
- Help provides detailed guides
- Cross-reference in both directions

### With Gamification

Award achievements for tour completion:
```javascript
onComplete: () => {
    // Award "Tour Guide" badge
    awardAchievement('tour_completed');
    showAlert('Achievement unlocked: Tour Guide! 🎉');
}
```

## Future Enhancements

Potential improvements:

1. **Multi-page tours**: Continue across page navigations
2. **Interactive steps**: Require user action to proceed
3. **Video integration**: Embed videos in tooltips
4. **Analytics**: Track which steps users skip/complete
5. **A/B testing**: Test different tour flows
6. **Localization**: Multi-language support
7. **Accessibility**: Better screen reader support
8. **Mobile optimization**: Touch-friendly interactions

## API Reference

### WelcomeTour Class

**Constructor**:
```javascript
new WelcomeTour(steps, options)
```

**Parameters**:
- `steps` (Array): Tour step configurations
- `options` (Object):
  - `showSkipButton` (Boolean): Show skip button (default: true)
  - `storageKey` (String): LocalStorage key (default: 'tour_completed')
  - `onComplete` (Function): Callback on completion
  - `onSkip` (Function): Callback on skip

**Methods**:
- `start()`: Begin the tour
- `next()`: Go to next step
- `prev()`: Go to previous step
- `skip()`: Skip tour
- `complete()`: Mark tour complete
- `cleanup()`: Remove tour elements
- `WelcomeTour.reset()`: Static method to reset tour

## Support

For questions or issues:
- Check this guide first
- Review code comments in source files
- Check browser console for errors
- Test with provided test files
- Contact development team

---

**Version**: 1.0.0  
**Last Updated**: October 26, 2024  
**Maintainer**: OUTSINC Development Team
