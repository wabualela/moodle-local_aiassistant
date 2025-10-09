# AI Assistant Plugin for Moodle

A floating AI assistant for Moodle that provides contextual help and support to users.

## Features

- **Floating Action Button (FAB)**: A persistent button in the bottom-left corner that users can click to access the assistant
- **Chat Interface**: Modern chat UI with animations and proper accessibility
- **Responsive Design**: Works on desktop and mobile devices
- **RTL Support**: Full support for right-to-left languages
- **Typing Indicators**: Visual feedback when the AI is "thinking"
- **Core AI Integration**: Uses Moodle's AI subsystem to talk to whichever provider you enable
- **Keyboard Navigation**: Full keyboard accessibility support

## Installation

1. Copy the plugin to `local/aiassistant/`
2. Visit Site administration > Notifications to install the plugin
3. Enable the plugin in Site administration > Plugins > Local plugins > AI Assistant

## Configuration

Navigate to **Site administration > Plugins > Local plugins > AI Assistant**

- **Enable AI Assistant**: Turn the assistant on/off for all users
- **Assistant appearance**: Choose the label, colour, and icon shown in the FAB
- **System prompt**: Outline how the assistant should behave in responses

AI credentials, models, and rate limits are managed in **Site administration > AI**. Enable and configure an AI provider (for example the OpenAI provider) that supports the *Generate text* action; the assistant will automatically use whichever enabled provider is available.

## Current Status

### ✅ Completed
- FAB button with hover effects
- Chat interface UI with templates
- JavaScript module for opening/closing chat
- Message display with user/AI differentiation
- Live responses via Moodle's core AI subsystem (*Generate text* action)
- Typing indicator animation
- Proper CSS styling with animations
- Accessibility features (ARIA labels, keyboard navigation)
- RTL language support

### 🚧 In Progress
- Message history persistence
- File attachment support

## File Structure

```
local/aiassistant/
├── amd/
│   ├── src/
│   │   ├── chat.js          # Main chat functionality
│   │   ├── fab.js           # Legacy FAB (to be removed)
│   │   └── assistant.js     # Legacy (to be removed)
│   └── build/               # Compiled AMD modules
├── classes/
│   ├── hook_callbacks.php   # Hook for injecting UI
│   └── output/
│       └── renderer.php     # Custom renderer (if needed)
├── db/
│   ├── access.php           # Capabilities
│   └── hooks.php            # Hook registrations
├── lang/
│   ├── en/
│   │   └── local_aiassistant.php  # English strings
│   └── ar/
│       └── local_aiassistant.php  # Arabic strings
├── pix/
│   └── sheikh.svg           # Assistant icon
├── templates/
│   ├── fab.mustache         # FAB button template
│   └── chat.mustache        # Chat interface template
├── settings.php             # Plugin settings
├── styles.css               # All CSS styling
└── version.php              # Plugin version info
```

## Testing

### Manual Testing

1. **Enable the plugin**:
   ```bash
   php admin/cli/purge_caches.php
   ```

2. **Visit any Moodle page**:
   - You should see a floating button in the bottom-left corner
   - Hover over it to see the "Need help?" label
   - Click it to open the chat interface

3. **Test the chat**:
   - Click the FAB to open chat
   - Type a message and press Enter or click send
   - Moodle will call the configured AI provider via the core AI subsystem; you should receive the generated reply or an administrator-friendly error if no provider/action is available
   - Click the X to close the chat
   - Click outside the chat to close it

4. **Test keyboard navigation**:
   - Tab to the FAB button
   - Press Enter or Space to open chat
   - Type in the input field
   - Press Enter to send
   - Tab to close button and press Enter

### Build AMD Modules

**Note**: Currently requires Node.js 22.11.0 <23. If you have Node 24+, you'll need to use nvm:

```bash
# Install correct Node version
nvm install 22.11.0
nvm use 22.11.0

# Build AMD modules
cd /path/to/moodle
grunt amd --root=local/aiassistant

# Or build all grunt tasks
grunt
```

## Development

### Adding Language Strings

Edit `lang/en/local_aiassistant.php`:

```php
$string['your_string_key'] = 'Your string value';
```

Then purge caches:

```bash
php admin/cli/purge_caches.php
```

### Modifying Templates

1. Edit templates in `templates/*.mustache`
2. Purge caches to see changes
3. No build step needed for templates

### Modifying JavaScript

1. Edit source files in `amd/src/*.js`
2. Build with Grunt: `grunt amd --root=local/aiassistant`
3. Purge caches

### Modifying CSS

1. Edit `styles.css`
2. Purge caches to see changes
3. No build step needed

## Next Steps

### Short Term
1. Implement message persistence so conversations survive reloads.
2. Pass course and page context into the AI request for more relevant answers.
3. Surface AI policy/usage warnings in the UI and allow quick retry/resubmit flows.
4. Add file attachment support (forward to providers that permit it).

### Long Term
1. Voice input support
2. Multi-language AI responses
3. Analytics and usage tracking
4. Admin dashboard for monitoring
5. Optional streaming responses

## Architecture

### How It Works

1. **Injection**: The `hook_callbacks.php` uses Moodle's hook system to inject the FAB and chat HTML after the main content region
2. **Initialization**: The `chat.js` AMD module initializes when the page loads
3. **Interaction**: Users click the FAB to toggle the chat interface
4. **Communication**: Messages are sent via `local_aiassistant_get_completion`, which delegates to Moodle's core AI manager (Generate text action)

### Hook System

The plugin uses Moodle's modern hook system (`\core\hook\output\after_standard_main_region_html_generation`) to inject the UI without modifying core files.

### AI Provider Integration

The assistant relies on Moodle's AI subsystem (Moodle 4.5+) for credentials and HTTP calls. Any enabled AI provider plugin that supports the *Generate text* action—such as the core OpenAI provider—will automatically handle requests. Additional providers can be installed to target different models or vendors.

## Troubleshooting

### FAB Not Showing
- Check that the plugin is enabled in settings
- Purge all caches
- Check browser console for JavaScript errors
- Verify templates are rendering (view page source)

### JavaScript Not Working
- Rebuild AMD modules: `grunt amd --root=local/aiassistant`
- Purge caches
- Check browser console for errors
- Verify JavaScript file is loaded in network tab

### Styling Issues
- Purge caches
- Check for CSS conflicts with theme
- Verify `styles.css` is being loaded
- Check browser dev tools for CSS errors

## Contributing

1. Follow Moodle coding standards
2. Add PHPDoc comments to all functions
3. Use language strings (no hardcoded text)
4. Test on multiple themes
5. Test RTL languages
6. Ensure accessibility (WCAG 2.1 AA)

## License

GPL v3 or later

## Credits

- **Author**: Wail Abualela (wailabualela@alborhan.sa)
- **Copyright**: 2025
