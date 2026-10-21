import './bootstrap';

import '@hotwired/turbo';

// Icon styles live in resources/css/app.css so the icon font is part of the
// main stylesheet and renders even if this module graph fails to execute.

// Behaviour modules — each one no-ops gracefully when its page markup
// is not present, so the single entry point can be loaded everywhere.
import './modules/theme';
import './modules/toasts';
import './modules/dropdown';
import './modules/confirm';
import './modules/tabs';
import './modules/changelog';
import './modules/music-player';
import './modules/calculator';
import './modules/calendar';
import './modules/todo-items';
import './modules/todo-editor';
import './modules/weather-location';
import './modules/clock';
import './modules/converter';
import './modules/slider';
import './modules/note-editor';
import './modules/event-map';
import './modules/reorder';
import './modules/dashboard-widgets';
