import './bootstrap';

import '@hotwired/turbo';

// Icon styles live in resources/css/app.css so the icon font is part of the
// main stylesheet and renders even if this module graph fails to execute.

// Behaviour modules — each one no-ops gracefully when its page markup
// is not present, so the single entry point can be loaded everywhere.
import './modules/theme';
import './modules/toasts';
import './modules/confirm';
import './modules/tabs';
import './modules/music-player';
import './modules/calculator';
import './modules/calendar';
import './modules/todo-items';
import './modules/weather-location';
import './modules/clock';
import './modules/converter';
