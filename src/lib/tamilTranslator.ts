/**
 * Tamil → English Translation Engine  (v2 — fixed)
 * ─────────────────────────────────────────────────────────────────────────────
 * Root cause of previous failure:
 *   Browser returns Tamil Unicode (e.g. "என்னால் லாகின் பண்ண முடியவில்லை")
 *   when lang = 'ta-IN'. The old char-by-char phonetic map produced garbage
 *   Tanglish that never matched the phrase/word dictionaries.
 *
 * Fix strategy (fastest → most accurate):
 *  1. Google Translate free endpoint  — works without API key, handles Tamil
 *     Unicode, Tanglish, and mixed text perfectly.
 *  2. MyMemory free API               — backup if Google is blocked.
 *  3. Gemini API                      — if VITE_GEMINI_API_KEY is set.
 *  4. Local phrase + word dictionary  — offline fallback.
 * ─────────────────────────────────────────────────────────────────────────────
 */

// ── Tanglish / Tamil phrase dictionary (offline fallback) ─────────────────────
const PHRASE_MAP: Record<string, string> = {
  // Login / Account
  "enaku login panna mudiyala":                          "I am unable to log in",
  "enaku login panna mudiyala account open agala":       "I am unable to log into my account",
  "account open agala":                                  "My account is not opening",
  "password maranthuten":                                "I have forgotten my password",
  "password reset panna mudiyala":                       "I am unable to reset my password",
  "account lock aagiduchi":                              "My account has been locked",
  "account lock aachi":                                  "My account has been locked",
  "login error varuthu":                                 "I am getting a login error",
  "otp varala":                                          "I am not receiving the OTP",
  "otp varalai":                                         "I am not receiving the OTP",
  // Ticket
  "ticket create pannumbothu error varuthu":             "I am getting an error while creating a ticket",
  "ticket create panna mudiyala":                        "I am unable to create a ticket",
  "ticket submit agala":                                 "The ticket is not getting submitted",
  "ticket close panna mudiyala":                         "I am unable to close the ticket",
  "ticket update agala":                                 "The ticket is not getting updated",
  "ticket status change agala":                          "The ticket status is not changing",
  // Network
  "internet slow ah iruku":                              "The internet connection is very slow",
  "internet varala":                                     "There is no internet connection",
  "network problem iruku":                               "There is a network issue",
  "wifi connect agala":                                  "I am unable to connect to WiFi",
  "wifi disconnect aaguthu":                             "The WiFi keeps disconnecting",
  "vpn connect agala":                                   "I am unable to connect to the VPN",
  "vpn work agala":                                      "The VPN is not working",
  // Software
  "software install agala":                              "The software is not getting installed",
  "application open agala":                              "The application is not opening",
  "app crash aaguthu":                                   "The application keeps crashing",
  "app work agala":                                      "The application is not working",
  "system slow ah iruku":                                "The system is running very slowly",
  "computer hang aaguthu":                               "The computer is hanging",
  "blue screen varuthu":                                 "I am getting a blue screen error",
  "error message varuthu":                               "I am getting an error message",
  // Email
  "email send agala":                                    "I am unable to send emails",
  "email receive agala":                                 "I am not receiving emails",
  "outlook work agala":                                  "Outlook is not working",
  // Printer
  "printer work agala":                                  "The printer is not working",
  "print agala":                                         "I am unable to print",
  "printer offline ah iruku":                            "The printer is showing as offline",
  // Hardware
  "keyboard work agala":                                 "The keyboard is not working",
  "mouse work agala":                                    "The mouse is not working",
  "screen black ah iruku":                               "The screen has gone black",
  "laptop charge agala":                                 "The laptop is not charging",
  // Access
  "access illai":                                        "I do not have access",
  "permission illai":                                    "I do not have the required permissions",
  "folder open agala":                                   "I am unable to open the folder",
  "file access agala":                                   "I am unable to access the file",
  // General
  "help panunga":                                        "Please help me",
  "urgent ah solve panunga":                             "Please resolve this urgently",
  "ippo solve panunga":                                  "Please resolve this immediately",
  "enna problem nu theriyala":                           "I am not sure what the problem is",
  "suddenly work agala":                                 "It suddenly stopped working",
  "yesterday work achu today agala":                     "It was working yesterday but not today",
  "before work achu ippo agala":                         "It was working before but it is not working now",
};

// ── Word map (offline fallback) ───────────────────────────────────────────────
const WORD_MAP: Record<string, string> = {
  "panna": "", "pannumbothu": "while", "pannunga": "please",
  "panunga": "please", "agala": "not working", "aagala": "not happening",
  "varuthu": "occurring", "varala": "not coming", "iruku": "is",
  "illai": "not available", "achu": "happened", "aaguthu": "happening",
  "aagiduchi": "has happened", "aachi": "has happened",
  "mudiyala": "unable to", "theriyala": "don't know",
  "maranthuten": "forgot", "maranthen": "forgot",
  "enaku": "I", "naan": "I", "naanga": "we",
  "ippo": "now", "suddenly": "suddenly", "before": "before",
  "yesterday": "yesterday", "today": "today",
  "ah": "", "nu": "", "la": "", "le": "", "um": "", "uh": "",
  "aana": "but", "enna": "what", "yaraavathu": "someone",
};

// ── Language detection ────────────────────────────────────────────────────────

export function hasTamilScript(text: string): boolean {
  return /[\u0B80-\u0BFF]/.test(text);
}

export function isTanglish(text: string): boolean {
  const lower = text.toLowerCase();
  return /\b(panna|pannunga|agala|aagala|varuthu|varala|iruku|illai|mudiyala|achu|aaguthu|aachi|enaku|naan|ippo|theriyala|maranthuten|maranthen|panunga|pannumbothu|aagiduchi|mudiyum|theriyum)\b/.test(lower);
}

export function detectLanguage(text: string): 'tamil' | 'tanglish' | 'english' {
  if (hasTamilScript(text)) return 'tamil';
  if (isTanglish(text)) return 'tanglish';
  return 'english';
}

// ── Translation result type ───────────────────────────────────────────────────

export interface TranslationResult {
  original: string;
  translated: string;
  detectedLanguage: 'tamil' | 'tanglish' | 'english';
  wasTranslated: boolean;
  method: string;
}

// ── Google Translate (free, no API key) ───────────────────────────────────────
/**
 * Uses the unofficial Google Translate endpoint.
 * Works for Tamil Unicode → English and Tanglish → English.
 * No API key required.
 */
async function googleTranslate(text: string, sourceLang: string): Promise<string | null> {
  try {
    // sl=ta for Tamil script, sl=auto for Tanglish (auto-detects)
    const sl = sourceLang === 'tamil' ? 'ta' : 'auto';
    const url = `https://translate.googleapis.com/translate_a/single?client=gtx&sl=${sl}&tl=en&dt=t&q=${encodeURIComponent(text)}`;

    const res = await fetch(url, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' },
    });

    if (!res.ok) return null;

    const data = await res.json();
    // Response format: [[["translated","original",null,null,10],...],...]
    if (Array.isArray(data) && Array.isArray(data[0])) {
      const parts: string[] = data[0]
        .filter((item: any) => Array.isArray(item) && typeof item[0] === 'string')
        .map((item: any) => item[0]);
      const result = parts.join('').trim();
      return result || null;
    }
    return null;
  } catch {
    return null;
  }
}

// ── MyMemory free API (backup) ────────────────────────────────────────────────
async function myMemoryTranslate(text: string, sourceLang: string): Promise<string | null> {
  try {
    const langPair = sourceLang === 'tamil' ? 'ta|en' : 'auto|en';
    const url = `https://api.mymemory.translated.net/get?q=${encodeURIComponent(text)}&langpair=${langPair}`;
    const res = await fetch(url);
    if (!res.ok) return null;
    const data = await res.json();
    const result = data?.responseData?.translatedText?.trim();
    // MyMemory returns the original if it can't translate
    if (result && result.toLowerCase() !== text.toLowerCase()) return result;
    return null;
  } catch {
    return null;
  }
}

// ── Gemini API (optional, if key provided) ────────────────────────────────────
async function geminiTranslate(text: string, lang: string, apiKey: string): Promise<string | null> {
  try {
    const langLabel = lang === 'tamil' ? 'Tamil (Unicode script)' : 'Tanglish (Tamil words in English letters)';
    const prompt = `Translate this ${langLabel} IT support message to professional English. Output ONLY the English translation, nothing else.\n\nInput: "${text}"\n\nEnglish:`;

    const res = await fetch(
      `https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=${apiKey}`,
      {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          contents: [{ parts: [{ text: prompt }] }],
          generationConfig: { temperature: 0.1, maxOutputTokens: 200 },
        }),
      }
    );
    if (!res.ok) return null;
    const data = await res.json();
    return data?.candidates?.[0]?.content?.parts?.[0]?.text?.trim() || null;
  } catch {
    return null;
  }
}

// ── Local dictionary fallback ─────────────────────────────────────────────────
function localTranslate(text: string): string {
  const lower = text.toLowerCase().trim();

  // Exact phrase match
  if (PHRASE_MAP[lower]) return capitalizeFirst(PHRASE_MAP[lower]) + '.';

  // Longest partial phrase match
  let bestMatch = '';
  let bestTranslation = '';
  for (const [phrase, translation] of Object.entries(PHRASE_MAP)) {
    if (lower.includes(phrase) && phrase.length > bestMatch.length) {
      bestMatch = phrase;
      bestTranslation = translation;
    }
  }
  if (bestMatch) return capitalizeFirst(bestTranslation) + '.';

  // Word-by-word
  const words = lower.split(/\s+/);
  const translated: string[] = [];
  for (const word of words) {
    const mapped = WORD_MAP[word];
    if (mapped === '') continue;
    if (mapped !== undefined) translated.push(mapped);
    else translated.push(word);
  }

  if (translated.length === 0) return text;

  let result = translated.join(' ')
    .replace(/\bI unable to\b/gi, 'I am unable to')
    .replace(/\bI not\b/gi, 'I am not')
    .replace(/\s{2,}/g, ' ')
    .trim();

  return capitalizeFirst(result) + (result.endsWith('.') ? '' : '.');
}

// ── English grammar improvement ───────────────────────────────────────────────
function improveEnglishGrammar(text: string): string {
  let result = text.trim();
  const fixes: [RegExp, string][] = [
    [/\bi am not able to\b/gi, 'I am unable to'],
    [/\bi cant\b/gi, "I can't"],
    [/\bi cannot\b/gi, 'I am unable to'],
    [/\bits not working\b/gi, 'it is not working'],
    [/\bim getting\b/gi, 'I am getting'],
    [/\bim having\b/gi, 'I am having'],
    [/\bim unable\b/gi, 'I am unable'],
    [/\bplz\b/gi, 'please'],
    [/\bwont\b/gi, "won't"],
    [/\bdont\b/gi, "don't"],
    [/\bdoesnt\b/gi, "doesn't"],
    [/\bisnt\b/gi, "isn't"],
    [/\s{2,}/g, ' '],
  ];
  for (const [pattern, replacement] of fixes) {
    result = result.replace(pattern, replacement);
  }
  return capitalizeFirst(result.trim());
}

// ── Main public API ───────────────────────────────────────────────────────────

/**
 * Synchronous local-only translation (instant, no network).
 */
export function translateToEnglish(text: string): TranslationResult {
  const lang = detectLanguage(text);
  if (lang === 'english') {
    return { original: text, translated: improveEnglishGrammar(text), detectedLanguage: 'english', wasTranslated: false, method: 'grammar' };
  }
  const translated = localTranslate(text);
  return { original: text, translated, detectedLanguage: lang, wasTranslated: true, method: 'local-dictionary' };
}

/**
 * Async translation — tries free APIs first, falls back to local dictionary.
 * Priority: Google Translate → MyMemory → Gemini → Local
 */
export async function translateToEnglishAsync(
  text: string,
  geminiApiKey?: string
): Promise<TranslationResult> {
  const lang = detectLanguage(text);

  // Pure English — just fix grammar locally
  if (lang === 'english') {
    return {
      original: text,
      translated: improveEnglishGrammar(text),
      detectedLanguage: 'english',
      wasTranslated: false,
      method: 'grammar',
    };
  }

  // 1. Google Translate (free, no key, best quality)
  const googleResult = await googleTranslate(text, lang);
  if (googleResult && googleResult.trim().length > 0) {
    return {
      original: text,
      translated: capitalizeFirst(googleResult.trim()),
      detectedLanguage: lang,
      wasTranslated: true,
      method: 'google-translate',
    };
  }

  // 2. MyMemory (free backup)
  const myMemoryResult = await myMemoryTranslate(text, lang);
  if (myMemoryResult && myMemoryResult.trim().length > 0) {
    return {
      original: text,
      translated: capitalizeFirst(myMemoryResult.trim()),
      detectedLanguage: lang,
      wasTranslated: true,
      method: 'mymemory',
    };
  }

  // 3. Gemini API (if key available)
  if (geminiApiKey) {
    const geminiResult = await geminiTranslate(text, lang, geminiApiKey);
    if (geminiResult) {
      return {
        original: text,
        translated: capitalizeFirst(geminiResult),
        detectedLanguage: lang,
        wasTranslated: true,
        method: 'gemini',
      };
    }
  }

  // 4. Laravel backend (if configured)
  try {
    const res = await fetch('/api/ai/translate', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ text, source_language: lang }),
    });
    if (res.ok) {
      const data = await res.json();
      if (data.translated_text) {
        return {
          original: text,
          translated: data.translated_text,
          detectedLanguage: lang,
          wasTranslated: true,
          method: 'laravel-ai',
        };
      }
    }
  } catch { /* ignore */ }

  // 5. Local dictionary (always works offline)
  return {
    original: text,
    translated: localTranslate(text),
    detectedLanguage: lang,
    wasTranslated: true,
    method: 'local-dictionary',
  };
}

// ── Utilities ─────────────────────────────────────────────────────────────────

function capitalizeFirst(str: string): string {
  if (!str) return str;
  return str.charAt(0).toUpperCase() + str.slice(1);
}

export function getLanguageLabel(lang: 'tamil' | 'tanglish' | 'english'): string {
  switch (lang) {
    case 'tamil':    return 'Tamil';
    case 'tanglish': return 'Tanglish';
    case 'english':  return 'English';
  }
}
