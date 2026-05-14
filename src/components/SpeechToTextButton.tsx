/**
 * SpeechToTextButton — Tamil + Tanglish + English Speech-to-Text
 * ─────────────────────────────────────────────────────────────────────────────
 * Fixes from v1:
 *  - Auto mode now runs recognition with 'ta-IN' so Tamil Unicode is captured
 *    correctly, then Google Translate converts it to English.
 *  - Tamil Unicode is sent directly to Google Translate (free, no key).
 *  - Tanglish (Tamil in English letters) is also translated via Google.
 *  - English speech is grammar-corrected locally.
 *  - Live interim text shown while speaking.
 *  - Auto-stops after 3 s of silence.
 *  - Waveform animation while listening.
 * ─────────────────────────────────────────────────────────────────────────────
 */

import React, { useState, useEffect, useCallback, useRef } from 'react';
import { Mic, MicOff, Loader2, AlertCircle, CheckCircle2 } from 'lucide-react';
import { cn } from '@/lib/utils';
import {
  translateToEnglishAsync,
  detectLanguage,
  getLanguageLabel,
  type TranslationResult,
} from '../lib/tamilTranslator';

// ── Props ─────────────────────────────────────────────────────────────────────
interface SpeechToTextButtonProps {
  onTranscript: (text: string) => void;
  className?: string;
  defaultLanguage?: 'en-IN' | 'ta-IN' | 'en-US';
  fieldLabel?: string;
}

type LangMode = 'auto' | 'ta-IN' | 'en-IN';

const LANG_LABELS: Record<LangMode, string> = {
  auto:   'Auto',
  'ta-IN': 'Tamil',
  'en-IN': 'English',
};

// Gemini key from env (optional — Google Translate works without it)
const GEMINI_KEY = (import.meta as any).env?.VITE_GEMINI_API_KEY as string | undefined;

// ── Component ─────────────────────────────────────────────────────────────────
export function SpeechToTextButton({
  onTranscript,
  className,
  defaultLanguage = 'en-IN',
  fieldLabel,
}: SpeechToTextButtonProps) {
  const [isListening, setIsListening]     = useState(false);
  const [isTranslating, setIsTranslating] = useState(false);
  const [isSupported, setIsSupported]     = useState(true);
  const [langMode, setLangMode]           = useState<LangMode>('auto');
  const [error, setError]                 = useState<string | null>(null);
  const [liveText, setLiveText]           = useState('');
  const [lastResult, setLastResult]       = useState<TranslationResult | null>(null);
  const [showResult, setShowResult]       = useState(false);
  const [audioLevel, setAudioLevel]       = useState(0);

  const recognitionRef  = useRef<any>(null);
  const audioCtxRef     = useRef<AudioContext | null>(null);
  const streamRef       = useRef<MediaStream | null>(null);
  const animFrameRef    = useRef<number>(0);
  const silenceTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
  const finalBufferRef  = useRef<string>('');

  // ── Browser support check ──────────────────────────────────────────────────
  useEffect(() => {
    const SR = (window as any).SpeechRecognition || (window as any).webkitSpeechRecognition;
    if (!SR) setIsSupported(false);
    return () => stopAudio();
  }, []);

  // ── Audio waveform ─────────────────────────────────────────────────────────
  const startAudio = async () => {
    try {
      const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
      streamRef.current = stream;
      const ctx = new AudioContext();
      audioCtxRef.current = ctx;
      const analyser = ctx.createAnalyser();
      analyser.fftSize = 256;
      const source = ctx.createMediaStreamSource(stream);
      source.connect(analyser);
      const tick = () => {
        const data = new Uint8Array(analyser.frequencyBinCount);
        analyser.getByteFrequencyData(data);
        const avg = data.reduce((a, b) => a + b, 0) / data.length;
        setAudioLevel(Math.min(1, avg / 80));
        animFrameRef.current = requestAnimationFrame(tick);
      };
      tick();
    } catch { /* mic permission handled by recognition */ }
  };

  const stopAudio = () => {
    cancelAnimationFrame(animFrameRef.current);
    setAudioLevel(0);
    audioCtxRef.current?.close().catch(() => {});
    audioCtxRef.current = null;
    streamRef.current?.getTracks().forEach(t => t.stop());
    streamRef.current = null;
  };

  // ── Silence timer ──────────────────────────────────────────────────────────
  const resetSilenceTimer = () => {
    if (silenceTimerRef.current) clearTimeout(silenceTimerRef.current);
    silenceTimerRef.current = setTimeout(() => {
      recognitionRef.current?.stop();
    }, 3000);
  };

  // ── Stop ───────────────────────────────────────────────────────────────────
  const stopListening = useCallback(() => {
    recognitionRef.current?.stop();
    recognitionRef.current = null;
    if (silenceTimerRef.current) clearTimeout(silenceTimerRef.current);
    stopAudio();
    setIsListening(false);
    setLiveText('');
  }, []);

  // ── Process final text ─────────────────────────────────────────────────────
  const processFinal = useCallback(async (raw: string) => {
    const trimmed = raw.trim();
    if (!trimmed) return;

    setIsTranslating(true);
    setLiveText('');

    try {
      const result = await translateToEnglishAsync(trimmed, GEMINI_KEY);
      setLastResult(result);
      setShowResult(true);
      onTranscript(result.translated + ' ');
      setTimeout(() => setShowResult(false), 5000);
    } catch {
      onTranscript(trimmed + ' ');
    } finally {
      setIsTranslating(false);
    }
  }, [onTranscript]);

  // ── Start ──────────────────────────────────────────────────────────────────
  const startListening = useCallback(async () => {
    const SR = (window as any).SpeechRecognition || (window as any).webkitSpeechRecognition;
    if (!SR) return;

    setError(null);
    setLiveText('');
    setLastResult(null);
    setShowResult(false);
    finalBufferRef.current = '';

    await startAudio();

    const recognition = new SR();
    recognition.continuous     = true;
    recognition.interimResults = true;

    // KEY FIX: Always use 'ta-IN' so the browser captures Tamil Unicode
    // correctly. Google Translate then handles Tamil→English perfectly.
    // For English-only mode, use 'en-IN'.
    recognition.lang = langMode === 'en-IN' ? 'en-IN' : 'ta-IN';

    recognition.onstart = () => {
      setIsListening(true);
      resetSilenceTimer();
    };

    recognition.onerror = (event: any) => {
      const msgs: Record<string, string> = {
        'not-allowed':   'Microphone permission denied. Please allow mic access.',
        'network':       'Network error. Check your connection.',
        'no-speech':     'No speech detected. Please try again.',
        'audio-capture': 'No microphone found.',
        'aborted':       '',
      };
      const msg = msgs[event.error] ?? `Speech error: ${event.error}`;
      if (msg) setError(msg);
      stopListening();
    };

    recognition.onend = async () => {
      stopAudio();
      setIsListening(false);
      if (silenceTimerRef.current) clearTimeout(silenceTimerRef.current);
      const accumulated = finalBufferRef.current.trim();
      if (accumulated) await processFinal(accumulated);
      finalBufferRef.current = '';
    };

    recognition.onresult = (event: any) => {
      resetSilenceTimer();
      let interim = '';
      let newFinal = '';

      for (let i = event.resultIndex; i < event.results.length; i++) {
        const t = event.results[i][0].transcript;
        if (event.results[i].isFinal) {
          newFinal += t + ' ';
        } else {
          interim += t;
        }
      }

      if (interim) setLiveText(interim);
      if (newFinal) {
        finalBufferRef.current += newFinal;
        setLiveText('');
      }
    };

    recognitionRef.current = recognition;
    try {
      recognition.start();
    } catch (e: any) {
      setError('Could not start microphone: ' + e.message);
    }
  }, [langMode, processFinal, stopListening]);

  // ── Toggle ─────────────────────────────────────────────────────────────────
  const toggleListening = () => {
    if (isListening) stopListening();
    else startListening();
  };

  // ── Cycle language ─────────────────────────────────────────────────────────
  const cycleLang = (e: React.MouseEvent) => {
    e.stopPropagation();
    const modes: LangMode[] = ['auto', 'ta-IN', 'en-IN'];
    const next = modes[(modes.indexOf(langMode) + 1) % modes.length];
    setLangMode(next);
    if (isListening) {
      stopListening();
      setTimeout(() => startListening(), 200);
    }
  };

  // ── Waveform ───────────────────────────────────────────────────────────────
  const WaveformBars = () => (
    <span className="flex items-end gap-[2px] h-4">
      {[0.4, 0.7, 1.0, 0.7, 0.4].map((m, i) => (
        <span
          key={i}
          className="w-[3px] rounded-full bg-white transition-all duration-75"
          style={{ height: `${Math.max(3, audioLevel * 16 * m)}px` }}
        />
      ))}
    </span>
  );

  // ── Unsupported ────────────────────────────────────────────────────────────
  if (!isSupported) {
    return (
      <button
        type="button"
        disabled
        className={cn('p-1.5 text-muted-foreground/30 cursor-not-allowed opacity-50', className)}
        title="Speech recognition not supported. Use Chrome or Edge."
      >
        <MicOff className="w-4 h-4" />
      </button>
    );
  }

  // ── Render ─────────────────────────────────────────────────────────────────
  return (
    <div className="flex items-center gap-1 group/mic relative">

      {/* Mic button */}
      <button
        type="button"
        onClick={toggleListening}
        className={cn(
          'p-1.5 rounded-md transition-all duration-200 flex items-center gap-1.5',
          isListening
            ? 'bg-red-500 text-white shadow-lg shadow-red-200 ring-2 ring-red-400/30'
            : isTranslating
              ? 'bg-amber-500 text-white shadow-md shadow-amber-200'
              : 'hover:bg-muted text-muted-foreground border border-transparent hover:border-border',
          className
        )}
        title={
          isListening ? 'Stop recording'
          : isTranslating ? 'Translating...'
          : `Voice input${fieldLabel ? ` for ${fieldLabel}` : ''} — Tamil / Tanglish / English`
        }
      >
        {isTranslating ? (
          <>
            <Loader2 className="w-3.5 h-3.5 animate-spin" />
            <span className="text-[9px] font-black uppercase tracking-widest hidden sm:inline">Translating</span>
          </>
        ) : isListening ? (
          <>
            <WaveformBars />
            <span className="text-[9px] font-black uppercase tracking-widest hidden sm:inline">Listening</span>
          </>
        ) : (
          <Mic className="w-4 h-4" />
        )}
      </button>

      {/* Language toggle */}
      <button
        type="button"
        onClick={cycleLang}
        className={cn(
          'h-7 px-2 rounded border text-[9px] font-bold uppercase tracking-tighter transition-all',
          langMode === 'ta-IN'
            ? 'bg-amber-100 border-amber-300 text-amber-700'
            : langMode === 'auto'
              ? 'bg-purple-50 border-purple-200 text-purple-700'
              : 'bg-blue-50 border-blue-200 text-blue-700'
        )}
        title="Click to cycle: Auto → Tamil → English"
      >
        {LANG_LABELS[langMode]}
      </button>

      {/* Live transcription bubble */}
      {isListening && (
        <div className="absolute bottom-full mb-2 left-0 z-[200] bg-gray-900 text-white text-[11px] px-3 py-2 rounded-xl shadow-2xl min-w-[200px] max-w-[320px] border border-white/10">
          <div className="flex items-center gap-2 mb-1.5">
            <span className="w-2 h-2 rounded-full bg-red-400 animate-pulse" />
            <span className="text-[9px] font-black uppercase tracking-widest text-gray-400">
              {langMode === 'ta-IN' ? 'Tamil' : langMode === 'auto' ? 'Auto-detect' : 'English'} · Listening
            </span>
          </div>
          {liveText ? (
            <>
              <p className="text-white/90 leading-relaxed italic">"{liveText}"</p>
              <div className="mt-1.5 text-[9px] text-gray-500">
                Detected: <span className="text-amber-400">{getLanguageLabel(detectLanguage(liveText))}</span>
                {detectLanguage(liveText) !== 'english' && (
                  <span className="ml-1 text-green-400">→ will translate to English</span>
                )}
              </div>
            </>
          ) : (
            <p className="text-gray-500 italic text-[10px]">Speak now… stops automatically on silence</p>
          )}
          <div className="absolute -bottom-1.5 left-4 border-4 border-transparent border-t-gray-900" />
        </div>
      )}

      {/* Translation result badge */}
      {showResult && lastResult && (
        <div className={cn(
          'absolute bottom-full mb-2 left-0 z-[200] text-[11px] px-3 py-2 rounded-xl shadow-2xl min-w-[220px] max-w-[360px] border',
          lastResult.wasTranslated
            ? 'bg-green-900 text-green-100 border-green-700/50'
            : 'bg-gray-800 text-gray-100 border-gray-600/50'
        )}>
          <div className="flex items-center gap-2 mb-1">
            <CheckCircle2 className="w-3.5 h-3.5 text-green-400 shrink-0" />
            <span className="text-[9px] font-black uppercase tracking-widest text-green-400">
              {lastResult.wasTranslated
                ? `${getLanguageLabel(lastResult.detectedLanguage)} → English`
                : 'English (grammar corrected)'}
            </span>
          </div>
          <p className="leading-relaxed font-medium">"{lastResult.translated}"</p>
          {lastResult.wasTranslated && (
            <p className="mt-1 text-[9px] opacity-60 italic">Original: "{lastResult.original}"</p>
          )}
          <div className={cn(
            'absolute -bottom-1.5 left-4 border-4 border-transparent',
            lastResult.wasTranslated ? 'border-t-green-900' : 'border-t-gray-800'
          )} />
        </div>
      )}

      {/* Error badge */}
      {error && (
        <div className="absolute bottom-full mb-2 left-0 z-[200] bg-red-600 text-white text-[10px] font-bold px-3 py-2 rounded-xl shadow-xl flex items-center gap-2 max-w-[280px]">
          <AlertCircle className="w-3.5 h-3.5 shrink-0" />
          <span>{error}</span>
          <button onClick={() => setError(null)} className="ml-auto opacity-70 hover:opacity-100 text-sm">×</button>
          <div className="absolute -bottom-1.5 left-4 border-4 border-transparent border-t-red-600" />
        </div>
      )}
    </div>
  );
}
