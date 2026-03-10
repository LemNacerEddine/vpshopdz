/**
 * Arabic PDF Support for jsPDF
 *
 * Loads Amiri font and reshapes Arabic text for proper rendering.
 * jsPDF doesn't do OpenType text shaping, so we manually map Arabic
 * characters to their presentation forms (connected shapes).
 */
import { jsPDF } from 'jspdf';

// ============================================================
// Arabic Character Reshaping Map
// Maps Unicode Arabic letters to [isolated, initial, medial, final]
// Using Unicode Presentation Forms-B (FE70-FEFF)
// ============================================================
const ARABIC_FORMS = {
  '\u0621': ['\uFE80', '\uFE80', '\uFE80', '\uFE80'], // HAMZA
  '\u0622': ['\uFE81', '\uFE81', '\uFE82', '\uFE82'], // ALEF MADDA
  '\u0623': ['\uFE83', '\uFE83', '\uFE84', '\uFE84'], // ALEF HAMZA ABOVE
  '\u0624': ['\uFE85', '\uFE85', '\uFE86', '\uFE86'], // WAW HAMZA
  '\u0625': ['\uFE87', '\uFE87', '\uFE88', '\uFE88'], // ALEF HAMZA BELOW
  '\u0626': ['\uFE89', '\uFE8B', '\uFE8C', '\uFE8A'], // YEH HAMZA
  '\u0627': ['\uFE8D', '\uFE8D', '\uFE8E', '\uFE8E'], // ALEF
  '\u0628': ['\uFE8F', '\uFE91', '\uFE92', '\uFE90'], // BA
  '\u0629': ['\uFE93', '\uFE93', '\uFE94', '\uFE94'], // TEH MARBUTA
  '\u062A': ['\uFE95', '\uFE97', '\uFE98', '\uFE96'], // TEH
  '\u062B': ['\uFE99', '\uFE9B', '\uFE9C', '\uFE9A'], // THEH
  '\u062C': ['\uFE9D', '\uFE9F', '\uFEA0', '\uFE9E'], // JEEM
  '\u062D': ['\uFEA1', '\uFEA3', '\uFEA4', '\uFEA2'], // HAH
  '\u062E': ['\uFEA5', '\uFEA7', '\uFEA8', '\uFEA6'], // KHAH
  '\u062F': ['\uFEA9', '\uFEA9', '\uFEAA', '\uFEAA'], // DAL
  '\u0630': ['\uFEAB', '\uFEAB', '\uFEAC', '\uFEAC'], // THAL
  '\u0631': ['\uFEAD', '\uFEAD', '\uFEAE', '\uFEAE'], // REH
  '\u0632': ['\uFEAF', '\uFEAF', '\uFEB0', '\uFEB0'], // ZAIN
  '\u0633': ['\uFEB1', '\uFEB3', '\uFEB4', '\uFEB2'], // SEEN
  '\u0634': ['\uFEB5', '\uFEB7', '\uFEB8', '\uFEB6'], // SHEEN
  '\u0635': ['\uFEB9', '\uFEBB', '\uFEBC', '\uFEBA'], // SAD
  '\u0636': ['\uFEBD', '\uFEBF', '\uFEC0', '\uFEBE'], // DAD
  '\u0637': ['\uFEC1', '\uFEC3', '\uFEC4', '\uFEC2'], // TAH
  '\u0638': ['\uFEC5', '\uFEC7', '\uFEC8', '\uFEC6'], // ZAH
  '\u0639': ['\uFEC9', '\uFECB', '\uFECC', '\uFECA'], // AIN
  '\u063A': ['\uFECD', '\uFECF', '\uFED0', '\uFECE'], // GHAIN
  '\u0640': ['\u0640', '\u0640', '\u0640', '\u0640'],   // TATWEEL
  '\u0641': ['\uFED1', '\uFED3', '\uFED4', '\uFED2'], // FA
  '\u0642': ['\uFED5', '\uFED7', '\uFED8', '\uFED6'], // QAF
  '\u0643': ['\uFED9', '\uFEDB', '\uFEDC', '\uFEDA'], // KAF
  '\u0644': ['\uFEDD', '\uFEDF', '\uFEE0', '\uFEDE'], // LAM
  '\u0645': ['\uFEE1', '\uFEE3', '\uFEE4', '\uFEE2'], // MEEM
  '\u0646': ['\uFEE5', '\uFEE7', '\uFEE8', '\uFEE6'], // NOON
  '\u0647': ['\uFEE9', '\uFEEB', '\uFEEC', '\uFEEA'], // HEH
  '\u0648': ['\uFEED', '\uFEED', '\uFEEE', '\uFEEE'], // WAW
  '\u0649': ['\uFEEF', '\uFEEF', '\uFEF0', '\uFEF0'], // ALEF MAKSURA
  '\u064A': ['\uFEF1', '\uFEF3', '\uFEF4', '\uFEF2'], // YEH
};

// Letters that DON'T connect to the next letter (right-joining only)
const RIGHT_JOIN_ONLY = new Set([
  '\u0622', '\u0623', '\u0624', '\u0625', '\u0627', // ALEF variants + WAW HAMZA
  '\u062F', '\u0630', '\u0631', '\u0632',             // DAL, THAL, REH, ZAIN
  '\u0648', '\u0629',                                  // WAW, TEH MARBUTA
]);

// Arabic diacritics (tashkeel) - should be kept but not affect shaping
const DIACRITICS = new Set([
  '\u064B', '\u064C', '\u064D', '\u064E', '\u064F',
  '\u0650', '\u0651', '\u0652', '\u0670',
]);

/**
 * Check if a character is an Arabic letter (not diacritic, not space)
 */
function isArabicLetter(ch) {
  return ARABIC_FORMS.hasOwnProperty(ch) || ch === '\u0640';
}

/**
 * Reshape Arabic text by replacing characters with their
 * proper presentation forms based on position in word.
 *
 * Form indices: 0=isolated, 1=initial, 2=medial, 3=final
 */
function reshapeArabic(text) {
  if (!text) return '';

  // Strip diacritics for simpler shaping (they cause issues in jsPDF)
  let chars = [];
  for (const ch of text) {
    if (!DIACRITICS.has(ch)) {
      chars.push(ch);
    }
  }

  const result = [];

  for (let i = 0; i < chars.length; i++) {
    const ch = chars[i];
    const forms = ARABIC_FORMS[ch];

    if (!forms) {
      // Not an Arabic letter, keep as-is
      result.push(ch);
      continue;
    }

    // Find previous and next Arabic letters (skip non-Arabic)
    let prevArabic = null;
    for (let j = i - 1; j >= 0; j--) {
      if (isArabicLetter(chars[j])) { prevArabic = chars[j]; break; }
      if (chars[j] !== '\u200C' && chars[j] !== '\u200D') break; // stop at non-joiner chars
    }

    let nextArabic = null;
    for (let j = i + 1; j < chars.length; j++) {
      if (isArabicLetter(chars[j])) { nextArabic = chars[j]; break; }
      if (chars[j] !== '\u200C' && chars[j] !== '\u200D') break;
    }

    // Determine if previous letter connects forward (to this letter)
    const prevConnects = prevArabic !== null && !RIGHT_JOIN_ONLY.has(prevArabic);

    // Determine if this letter can connect to next
    const canConnectNext = nextArabic !== null && !RIGHT_JOIN_ONLY.has(ch);

    // Determine form: 0=isolated, 1=initial, 2=medial, 3=final
    let formIndex;
    if (prevConnects && canConnectNext) {
      formIndex = 2; // medial
    } else if (prevConnects) {
      formIndex = 3; // final
    } else if (canConnectNext) {
      formIndex = 1; // initial
    } else {
      formIndex = 0; // isolated
    }

    result.push(forms[formIndex]);
  }

  return result.join('');
}

/**
 * Process text for Arabic PDF rendering.
 * - Reshapes Arabic characters (proper ligatures)
 * - Reverses text for RTL display
 * - Handles mixed Arabic/Latin text
 */
export function processArabicText(text) {
  if (!text || typeof text !== 'string') return text || '';

  const hasArabic = /[\u0600-\u06FF]/.test(text);
  if (!hasArabic) return text;

  // Handle Lam-Alef ligatures first
  let processed = text;
  processed = processed.replace(/\u0644\u0622/g, '\uFEF5'); // LAM + ALEF MADDA
  processed = processed.replace(/\u0644\u0623/g, '\uFEF7'); // LAM + ALEF HAMZA ABOVE
  processed = processed.replace(/\u0644\u0625/g, '\uFEF9'); // LAM + ALEF HAMZA BELOW
  processed = processed.replace(/\u0644\u0627/g, '\uFEFB'); // LAM + ALEF

  // Reshape Arabic characters
  processed = reshapeArabic(processed);

  // Reverse the entire string for RTL display in jsPDF
  // But handle mixed content: Arabic segments reversed, Latin/number segments kept
  const segments = [];
  let seg = '';
  let segIsArabic = null;

  for (const ch of processed) {
    const isAr = /[\uFE70-\uFEFF\uFB50-\uFDFF\u0600-\u06FF]/.test(ch);
    const isSpace = ch === ' ';

    if (segIsArabic === null) {
      segIsArabic = isAr;
      seg = ch;
    } else if (isSpace) {
      seg += ch;
    } else if (isAr === segIsArabic) {
      seg += ch;
    } else {
      segments.push({ text: seg, isArabic: segIsArabic });
      seg = ch;
      segIsArabic = isAr;
    }
  }
  if (seg) segments.push({ text: seg, isArabic: segIsArabic });

  // Reverse segment order (RTL) and reverse Arabic segments internally
  return segments.reverse().map(s => {
    if (s.isArabic) {
      return s.text.split('').reverse().join('');
    }
    return s.text.trim();
  }).join(' ');
}

// ============================================================
// Font Loading
// ============================================================
let cachedFontBase64 = null;
let cachedBoldBase64 = null;
let fontLoadPromise = null;

const FONT_URL = 'https://cdn.jsdelivr.net/gh/google/fonts@main/ofl/amiri/Amiri-Regular.ttf';
const FONT_BOLD_URL = 'https://cdn.jsdelivr.net/gh/google/fonts@main/ofl/amiri/Amiri-Bold.ttf';

async function fetchFontBase64(url) {
  const resp = await fetch(url);
  const buf = await resp.arrayBuffer();
  const bytes = new Uint8Array(buf);
  let binary = '';
  for (let i = 0; i < bytes.byteLength; i++) {
    binary += String.fromCharCode(bytes[i]);
  }
  return btoa(binary);
}

async function loadFonts() {
  if (cachedFontBase64 && cachedBoldBase64) {
    return { regular: cachedFontBase64, bold: cachedBoldBase64 };
  }
  if (!fontLoadPromise) {
    fontLoadPromise = Promise.all([
      fetchFontBase64(FONT_URL),
      fetchFontBase64(FONT_BOLD_URL),
    ]).then(([regular, bold]) => {
      cachedFontBase64 = regular;
      cachedBoldBase64 = bold;
      return { regular, bold };
    });
  }
  return fontLoadPromise;
}

/**
 * Create a jsPDF instance with Arabic Amiri font loaded.
 * @returns {Promise<jsPDF>}
 */
export async function createArabicPdf() {
  const fonts = await loadFonts();
  const doc = new jsPDF();

  doc.addFileToVFS('Amiri-Regular.ttf', fonts.regular);
  doc.addFont('Amiri-Regular.ttf', 'Amiri', 'normal');
  doc.addFileToVFS('Amiri-Bold.ttf', fonts.bold);
  doc.addFont('Amiri-Bold.ttf', 'Amiri', 'bold');

  doc.setFont('Amiri', 'normal');
  return doc;
}
