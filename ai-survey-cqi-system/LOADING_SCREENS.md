# Modern Loading Screens - Implementation Guide

## Overview

Your project now includes modern, animated loading screens with multiple styles and use cases. The loading screens automatically appear during:
- Page navigation
- Form submissions
- AJAX/Fetch requests
- Any async operations

## Features

### 1. **Full-Screen Loading Overlay**
- Gradient-animated spinner
- Animated ellipsis dots
- Progress bar animation
- Blurred background (iOS-style)
- Dark mode support
- Auto-hides when page loads

### 2. **Page Loader Bar**
- Thin progress bar at the top of the page
- Appears during navigation
- Quick, non-intrusive indicator
- Gradient animated effect

### 3. **Multiple Spinner Styles**
- **spinner** - Classic rotating spinner
- **spinner-gradient** - Modern gradient conic gradient
- **spinner-dots** - Pulsing dots effect
- **spinner-lg** - Large size variant

### 4. **Additional Components**
- Dots loader (bouncing dots)
- Progress bar with animation
- Shimmer skeleton loader
- Mini loading indicators for buttons
- Modal loading overlay

---

## Implementation Files

### SCSS Styling
```
resources/sass/components/_ai-loading.scss
```
Contains all loading screen styles with organized sections:
- Keyframe animations
- Loading screen overlay
- Spinner styles
- Progress indicators
- Button loading states
- Responsive adjustments

### Blade Component
```
resources/views/components/loading-screen.blade.php
```
The HTML structure for the loading screen - include this in your layouts.

### JavaScript Manager
```
resources/js/modules/loading-screen.js
```
Handles all loading screen interactions:
- Auto-show/hide logic
- Form submission handling
- Navigation detection
- AJAX/Fetch interception
- Global `loader` object

### Updated Layouts
All main layouts now include the loading screen:
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/guest.blade.php`
- `resources/views/layouts/login.blade.php`
- `resources/views/admin/layouts/app.blade.php`

---

## Usage

### 1. **Automatic Loading (Most Common)**

The loading screen automatically appears for:

#### Form Submissions
```html
<form action="/submit" method="POST">
    <!-- Your form fields -->
    <button type="submit">Submit</button>
</form>
```
The loader will appear automatically when submitted.

#### Link Navigation
```html
<a href="/dashboard">Go to Dashboard</a>
```
Page loader bar appears automatically on click.

#### AJAX Requests
```javascript
// Using Fetch API
fetch('/api/data')
    .then(response => response.json());

// Using Axios (if configured)
axios.get('/api/data');
```
Loader appears automatically for both.

### 2. **Manual Control**

Use the global `loader` object for manual control:

```javascript
// Show full loading screen
loader.show();

// Show with custom message
loader.show('Processing your request...');

// Show and auto-hide after 3 seconds
loader.show('Uploading file...', 3000);

// Hide the loading screen
loader.hide();

// Hide after a delay (ms)
loader.hide(500);

// Show page loader bar
loader.showPageLoader();

// Hide page loader bar
loader.hidePageLoader();

// Show temporary indicator (auto-hides)
loader.showTemporary('Success!', 2000);
```

### 3. **Disable Loading for Specific Elements**

Add `data-no-loading` attribute to skip loading indicators:

```html
<!-- Form that doesn't need loading screen -->
<form action="/submit" method="POST" data-no-loading>
    <button type="submit">Quick Action</button>
</form>

<!-- Link that doesn't need loading screen -->
<a href="/dashboard" data-no-loading>Dashboard</a>
```

### 4. **Using in JavaScript Code**

Example in a Vue/React component or module:

```javascript
// Start operation
loader.show('Analyzing survey results...');

try {
    const response = await fetch('/api/analyze');
    const data = await response.json();
    
    // Success
    loader.showTemporary('Analysis complete!', 2000);
} catch (error) {
    console.error(error);
    loader.hide();
}
```

### 5. **Button Loading State**

Add `.loading` class to disable button and show spinner:

```html
<button id="submitBtn" class="btn btn-primary">Submit</button>

<script>
document.getElementById('submitBtn').addEventListener('click', async () => {
    const btn = event.target;
    btn.classList.add('loading');
    
    try {
        // Do async operation
        await doSomething();
    } finally {
        btn.classList.remove('loading');
    }
});
</script>
```

---

## Styling Examples

### Using Different Spinner Styles

In your Blade view, you can modify the loading screen by editing the component:

```blade
{{-- Edit resources/views/components/loading-screen.blade.php --}}

{{-- Default spinner --}}
<div class="spinner spinner-gradient"></div>

{{-- Alternative: Dots spinner --}}
<div class="spinner spinner-dots"></div>

{{-- Alternative: Large spinner --}}
<div class="spinner spinner-lg"></div>

{{-- Alternative: Dots loader --}}
<div class="dots-loader">
    <div class="dot"></div>
    <div class="dot"></div>
    <div class="dot"></div>
</div>
```

### Custom Messages

Edit the loading screen component to customize messages:

```blade
<p class="loading-text">Custom Loading Text</p>
<p class="loading-message">Your custom message here</p>
```

---

## Customization

### Change Colors

Edit `resources/sass/components/_ai-loading.scss`:

```scss
// Change primary color from blue (#3b82f6) to your brand color
$primary-color: #ff6b35; // Your color

// Update all instances of #3b82f6 with your color
.spinner {
    border-top: 4px solid $primary-color;
}
```

### Adjust Animation Speed

```scss
// Slower animations
.spinner {
    animation: spin 1.2s linear infinite; // Change from 0.8s
}

.loading-progress .progress-bar {
    animation: shimmer 3s infinite; // Change from 2s
}
```

### Change Background Opacity

```scss
.loading-screen {
    background: rgba(255, 255, 255, 0.8); // Change opacity (0.95 → 0.8)
}
```

### Disable Auto-Hide

Modify `resources/js/modules/loading-screen.js`:

```javascript
// Comment out auto-hide on page load
// window.addEventListener('load', () => {
//     loader.hide(500);
// });
```

---

## API Reference

### LoadingScreenManager Methods

#### `show(message, timeout)`
Shows the full loading screen.
- `message` (string, optional): Custom message to display
- `timeout` (number): Auto-hide after ms (0 = manual hide only)

#### `hide(delay)`
Hides the loading screen.
- `delay` (number): Delay before hiding in ms (default = 300)

#### `showPageLoader()`
Shows the thin top progress bar.

#### `hidePageLoader()`
Hides the thin top progress bar.

#### `showTemporary(message, duration)`
Shows loading screen that auto-hides.
- `message` (string): Message to display
- `duration` (number): Duration in ms (default = 2000)

---

## Troubleshooting

### Loading screen doesn't appear
1. Ensure `resources/js/modules/loading-screen.js` is loaded
2. Check that your layout includes `@include('components.loading-screen')`
3. Verify SCSS import in `app.scss`

### Loading screen stays visible
```javascript
// Manually hide it
loader.hide();
```

### Change spinner appearance
Edit `resources/views/components/loading-screen.blade.php` and swap the spinner class.

### Dark mode not working
Ensure `data-bs-theme="light"` is set on your `<html>` tag. The loader auto-detects color scheme changes.

---

## Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- Mobile browsers (iOS Safari, Chrome Mobile)
- Dark mode support via `prefers-color-scheme`
- CSS Animations, Backdrop Filter, CSS Grid

---

## Performance Notes

- Minimal performance impact
- Uses CSS animations (hardware accelerated)
- Doesn't block rendering
- Automatically cleaned up after hide
- Lightweight JavaScript (< 5KB)

---

## Examples

### Upload with Progress
```javascript
document.getElementById('uploadForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    loader.show('Uploading file...');
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('/upload', {
            method: 'POST',
            body: formData
        });
        
        if (response.ok) {
            loader.showTemporary('Upload complete!', 2000);
            // Redirect or update UI
        }
    } finally {
        loader.hide();
    }
});
```

### API Call with Error Handling
```javascript
async function fetchSurveyData() {
    loader.show('Loading survey data...');
    
    try {
        const response = await fetch('/api/surveys');
        if (!response.ok) throw new Error('Failed to load');
        
        const data = await response.json();
        return data;
    } catch (error) {
        loader.showTemporary('Error loading data', 3000);
        console.error(error);
    } finally {
        loader.hide();
    }
}
```

---

## Notes

- The loading screens are fully integrated with Bootstrap 5
- All styles are SCSS/Sass compatible
- Responsive design for mobile and desktop
- Accessibility considerations included
- Dark mode support out of the box

Enjoy your modern loading screens! 🚀
