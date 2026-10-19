/**
 * Unit converter — categorised, factor-based conversion engine.
 *
 * Every unit is defined as a factor towards the category's base unit;
 * temperature uses offset transforms instead. Precision comes from
 * settings (body[data-converter-precision], 0-6 decimals).
 *
 * Contract with the Blade view (#converter-root):
 *   [data-category] pills, #conv-from / #conv-to selects,
 *   #conv-input / #conv-output fields, #conv-swap button.
 */

import { onReady } from '../support/ready';

/** Length — base: metre */
const LENGTH = {
    nm: ['Nanometre', 1e-9], µm: ['Micrometre', 1e-6], mm: ['Millimetre', 0.001],
    cm: ['Centimetre', 0.01], dm: ['Decimetre', 0.1], m: ['Metre', 1],
    dam: ['Decametre', 10], hm: ['Hectometre', 100], km: ['Kilometre', 1000],
    in: ['Inch', 0.0254], ft: ['Foot', 0.3048], yd: ['Yard', 0.9144],
    mi: ['Mile', 1609.344], nmi: ['Nautical mile', 1852],
    furlong: ['Furlong', 201.168], ly: ['Light-year', 9.4607304725808e15],
    au: ['Astronomical unit', 1.495978707e11], pc: ['Parsec', 3.0856775814913673e16],
};

/** Mass — base: kilogram */
const MASS = {
    mcg: ['Microgram', 1e-9], mg: ['Milligram', 1e-6], g: ['Gram', 0.001],
    kg: ['Kilogram', 1], t: ['Tonne', 1000], ct: ['Carat', 0.0002],
    oz: ['Ounce', 0.028349523125], lb: ['Pound', 0.45359237],
    st: ['Stone', 6.35029318], ton_us: ['Short ton (US)', 907.18474],
    ton_uk: ['Long ton (UK)', 1016.0469088],
};

/** Area — base: square metre */
const AREA = {
    mm2: ['Square millimetre', 1e-6], cm2: ['Square centimetre', 1e-4],
    m2: ['Square metre', 1], ha: ['Hectare', 1e4], km2: ['Square kilometre', 1e6],
    in2: ['Square inch', 0.00064516], ft2: ['Square foot', 0.09290304],
    yd2: ['Square yard', 0.83612736], acre: ['Acre', 4046.8564224],
    mi2: ['Square mile', 2589988.110336],
};

/** Volume — base: litre */
const VOLUME = {
    ml: ['Millilitre', 0.001], cl: ['Centilitre', 0.01], l: ['Litre', 1],
    kl: ['Kilolitre', 1000], mm3: ['Cubic millimetre', 1e-6],
    cm3: ['Cubic centimetre', 0.001], m3: ['Cubic metre', 1000],
    tsp_us: ['Teaspoon (US)', 0.00492892159375], tbsp_us: ['Tablespoon (US)', 0.01478676478125],
    floz_us: ['Fluid ounce (US)', 0.0295735295625], cup_us: ['Cup (US)', 0.2365882365],
    pt_us: ['Pint (US)', 0.473176473], qt_us: ['Quart (US)', 0.946352946],
    gal_us: ['Gallon (US)', 3.785411784], gal_uk: ['Gallon (Imperial)', 4.54609],
    floz_uk: ['Fluid ounce (Imperial)', 0.0284130625], bbl_oil: ['Barrel (oil)', 158.987294928],
};

/** Speed — base: metre/second */
const SPEED = {
    mps: ['Metre/second', 1], kmps: ['Kilometre/second', 1000],
    kmh: ['Kilometre/hour', 1 / 3.6], mph: ['Mile/hour', 0.44704],
    fps: ['Foot/second', 0.3048], kn: ['Knot', 0.5144444444444445],
    mach: ['Mach (sea level)', 340.29], c: ['Speed of light', 299792458],
};

/** Time — base: second */
const TIME = {
    ms: ['Millisecond', 0.001], s: ['Second', 1], min: ['Minute', 60],
    h: ['Hour', 3600], d: ['Day', 86400], wk: ['Week', 604800],
    mo: ['Month (30d)', 2592000], yr: ['Year (365d)', 31536000],
    decade: ['Decade', 315360000], century: ['Century', 3153600000],
};

/** Digital storage — base: byte (decimal) */
const DATA = {
    bit: ['Bit', 0.125], B: ['Byte', 1], kB: ['Kilobyte', 1e3], MB: ['Megabyte', 1e6],
    GB: ['Gigabyte', 1e9], TB: ['Terabyte', 1e12], PB: ['Petabyte', 1e15],
    KiB: ['Kibibyte', 1024], MiB: ['Mebibyte', 1048576], GiB: ['Gibibyte', 1073741824],
    TiB: ['Tebibyte', 1099511627776],
};

/** Pressure — base: pascal */
const PRESSURE = {
    pa: ['Pascal', 1], hpa: ['Hectopascal', 100], kpa: ['Kilopascal', 1000],
    mpa: ['Megapascal', 1e6], bar: ['Bar', 1e5], atm: ['Atmosphere', 101325],
    psi: ['PSI', 6894.757293168], torr: ['Torr', 133.32236842105263],
    mmhg: ['mmHg', 133.322387415],
};

/** Energy — base: joule */
const ENERGY = {
    j: ['Joule', 1], kj: ['Kilojoule', 1000], cal: ['Calorie', 4.184],
    kcal: ['Kilocalorie', 4184], wh: ['Watt-hour', 3600], kwh: ['Kilowatt-hour', 3.6e6],
    ev: ['Electronvolt', 1.602176634e-19], btu: ['BTU', 1055.05585262],
    ftlb: ['Foot-pound', 1.3558179483314004],
};

/** Angle — base: degree */
const ANGLE = {
    deg: ['Degree', 1], rad: ['Radian', 57.29577951308232],
    grad: ['Gradian', 0.9], turn: ['Turn', 360], arcmin: ['Arcminute', 1 / 60],
    arcsec: ['Arcsecond', 1 / 3600],
};

/** Categories shown as tabs; temperature is converted via transforms. */
const CATEGORIES = {
    length: { label: 'Length', icon: 'bi-rulers', units: LENGTH },
    mass: { label: 'Mass', icon: 'bi-box-seam', units: MASS },
    temperature: {
        label: 'Temperature', icon: 'bi-thermometer-half',
        units: { c: ['Celsius'], f: ['Fahrenheit'], k: ['Kelvin'], r: ['Rankine'] },
    },
    area: { label: 'Area', icon: 'bi-bounding-box', units: AREA },
    volume: { label: 'Volume', icon: 'bi-water', units: VOLUME },
    speed: { label: 'Speed', icon: 'bi-speedometer2', units: SPEED },
    time: { label: 'Time', icon: 'bi-clock-history', units: TIME },
    data: { label: 'Data', icon: 'bi-device-hdd', units: DATA },
    pressure: { label: 'Pressure', icon: 'bi-speedometer', units: PRESSURE },
    energy: { label: 'Energy', icon: 'bi-lightning-charge', units: ENERGY },
    angle: { label: 'Angle', icon: 'bi-compass', units: ANGLE },
};

/** Temperature conversions route through Celsius. */
function convertTemperature(value, from, to) {
    const toCelsius = { c: v => v, f: v => (v - 32) * (5 / 9), k: v => v - 273.15, r: v => (v - 491.67) * (5 / 9) };
    const fromCelsius = { c: v => v, f: v => v * (9 / 5) + 32, k: v => v + 273.15, r: v => (v + 273.15) * (9 / 5) };

    return fromCelsius[to](toCelsius[from](value));
}

let activeCategory = 'length';
let precision = 2;

const els = {};

function populateSelects() {
    const units = CATEGORIES[activeCategory].units;

    for (const select of [els.from, els.to]) {
        select.innerHTML = '';

        Object.entries(units).forEach(([code, definition]) => {
            const option = document.createElement('option');

            option.value = code;
            option.textContent = `${definition[0]} (${code.replace('_', ' ')})`;
            select.appendChild(option);
        });
    }

    const codes = Object.keys(units);

    // Keep sensible defaults per category; preserve previous picks when possible.
    els.from.value = codes.includes(els.from.dataset.last)
        ? els.from.dataset.last : codes[0];
    els.to.value = codes.includes(els.to.dataset.last)
        ? els.to.dataset.last : codes[1] ?? codes[0];

    els.from.dispatchEvent(new Event('change', { bubbles: true }));
    els.to.dispatchEvent(new Event('change', { bubbles: true }));

    els.from.dataset.last = els.from.value;
    els.to.dataset.last = els.to.value;
}

function formatResult(value) {
    if (!Number.isFinite(value)) return '—';

    return value.toLocaleString(undefined, {
        minimumFractionDigits: 0,
        maximumFractionDigits: precision,
    });
}

function convert() {
    const value = Number.parseFloat(els.input.value);

    if (Number.isNaN(value)) {
        els.output.value = '';

        return;
    }

    let result;

    if (activeCategory === 'temperature') {
        result = convertTemperature(value, els.from.value, els.to.value);
    } else {
        const units = CATEGORIES[activeCategory].units;
        const baseValue = value * units[els.from.value][1];
        result = baseValue / units[els.to.value][1];
    }

    els.output.value = formatResult(result);
}

/** Build the category pill strip from the CATEGORIES definition. */
function buildCategoryTabs(container) {
    Object.entries(CATEGORIES).forEach(([key, category]) => {
        const pill = document.createElement('button');

        pill.type = 'button';
        pill.dataset.category = key;
        // gap-3.5 widens the icon↔text gap for converter pills only; the
        // shared .tab-pill component keeps its own gap-2 everywhere else.
        pill.className = 'tab-pill gap-3.5';
        pill.innerHTML = `<i class="bi ${category.icon}"></i>${category.label}`;

        container.appendChild(pill);
    });
}

function renderCategoryTabs() {
    document.querySelectorAll('[data-category]').forEach((pill) => {
        pill.classList.toggle('is-active', pill.dataset.category === activeCategory);
    });
}

/** Attach a listener only once per node, surviving Turbo DOM morphs. */
function bindOnce(el, event, handler) {
    const flag = `bound${event}`;

    if (!el || el.dataset[flag]) return;

    el.dataset[flag] = '1';
    el.addEventListener(event, handler);
}

onReady(() => {
    const root = document.getElementById('converter-root');

    if (!root) return;

    precision = Math.min(6, Math.max(0, Number(root.dataset.precision ?? 2)));

    const catContainer = document.getElementById('conv-cats');

    if (catContainer && catContainer.children.length === 0) {
        buildCategoryTabs(catContainer);
    }

    els.input = document.getElementById('conv-input');
    els.output = document.getElementById('conv-output');
    els.from = document.getElementById('conv-from');
    els.to = document.getElementById('conv-to');

    renderCategoryTabs();
    populateSelects();
    convert();

    bindOnce(root, 'click', (event) => {
        const pill = event.target.closest('[data-category]');

        if (!pill || pill.dataset.category === activeCategory) return;

        activeCategory = pill.dataset.category;
        els.from.dataset.last = '';
        els.to.dataset.last = '';
        renderCategoryTabs();
        populateSelects();
        convert();
    });

    bindOnce(els.input, 'input', convert);
    bindOnce(els.from, 'change', () => {
        els.from.dataset.last = els.from.value;
        convert();
    });
    bindOnce(els.to, 'change', () => {
        els.to.dataset.last = els.to.value;
        convert();
    });

    bindOnce(document.getElementById('conv-swap'), 'click', () => {
        [els.from.value, els.to.value] = [els.to.value, els.from.value];
        els.from.dataset.last = els.from.value;
        els.to.dataset.last = els.to.value;
        els.from.dispatchEvent(new Event('change', { bubbles: true }));
        els.to.dispatchEvent(new Event('change', { bubbles: true }));
        convert();
    });
});
