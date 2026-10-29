(async () => {
    'use strict';

    const root = document.querySelector('[data-hs-induction]');
    if (!root) return;

    const viewer = root.querySelector('[data-hs-viewer]');
    const stage = root.querySelector('.hs-induction__stage');
    const image = root.querySelector('[data-hs-slide]');
    const animationLayer = root.querySelector('[data-hs-animation-layer]');
    const mediaLayer = root.querySelector('[data-hs-media-layer]');
    const mediaButton = root.querySelector('[data-hs-media-button]');
    const mediaLabel = root.querySelector('[data-hs-media-label]');
    const loading = root.querySelector('[data-hs-loading]');
    const prevButton = root.querySelector('[data-hs-prev]');
    const nextButton = root.querySelector('[data-hs-next]');
    const restartButton = root.querySelector('[data-hs-restart]');
    const fullscreenButton = root.querySelector('[data-hs-fullscreen]');
    const indexButton = root.querySelector('[data-hs-index-toggle]');
    const currentLabel = root.querySelector('[data-hs-current]');
    const titleLabel = root.querySelector('[data-hs-title]');
    const buildState = root.querySelector('[data-hs-build-state]');
    const progressBar = root.querySelector('[data-hs-progress]');
    const thumbs = Array.from(root.querySelectorAll('[data-hs-thumb]'));
    const manifestNode = root.parentElement?.querySelector('[data-hs-manifest]') || document.querySelector('[data-hs-manifest]');
    const evaluationPanel = root.querySelector('[data-hs-evaluation]');
    const evaluationForm = root.querySelector('[data-hs-evaluation-form]');
    const evaluationOpen = root.querySelector('[data-hs-evaluation-open]');
    const evaluationBack = root.querySelector('[data-hs-evaluation-back]');
    const evaluationSubmit = root.querySelector('[data-hs-evaluation-submit]');
    const evaluationResult = root.querySelector('[data-hs-evaluation-result]');
    const evaluationHistoryBody = root.querySelector('[data-hs-evaluation-history-body]');
    const evaluationHistoryCount = root.querySelector('[data-hs-evaluation-history-count]');
    const evaluationUrl = root.dataset.evaluationUrl || '';
    const hasEvaluation = root.dataset.hasEvaluation === '1';
    let evaluationVisible = false;
    let evaluationRedirectTimer = null;

    if (!image || !manifestNode) return;

    let manifest = [];
    try { manifest = JSON.parse(manifestNode.textContent || '[]'); } catch (_) { manifest = []; }
    if (!Array.isArray(manifest) || !manifest.length) return;

    const slides = manifest.map((slide, idx) => ({
        ...slide,
        number: Number(slide.number),
        animation: slide.animation || {maxStep: 0, effects: [], layers: []},
        thumb: thumbs[idx] || null,
    }));
    const moduleSlideNumbers = slides.map((slide) => Number(slide.number));
    const slideIndexByNumber = new Map(moduleSlideNumbers.map((number, idx) => [number, idx]));
    const moduleStart = moduleSlideNumbers[0];
    const moduleEnd = moduleSlideNumbers[moduleSlideNumbers.length - 1];
    const moduleId = root.dataset.moduleId || 'module-01';
    const userId = root.dataset.userId || 'anonymous';
    const userRole = root.dataset.userRole || 'usuario';
    const inductionId = root.dataset.inductionId || 'health-safety';
    const storageKey = `sct:${inductionId}:${encodeURIComponent(userId)}:${encodeURIComponent(userRole)}:${moduleId}:progress`;
    const progressUrl = root.dataset.progressUrl || '';
    const csrfToken = root.dataset.csrfToken || '';
    let serverSyncTimer = null;

    const defaultProgress = () => ({lastSlide: moduleStart, completed: [], updatedAt: null});
    const readProgress = () => {
        try {
            const raw = JSON.parse(localStorage.getItem(storageKey) || 'null');
            if (!raw || typeof raw !== 'object') return defaultProgress();
            const completed = Array.isArray(raw.completed)
                ? raw.completed.map(Number).filter((n) => Number.isInteger(n) && moduleSlideNumbers.includes(n))
                : [];
            const parsedLast = Number.parseInt(raw.lastSlide || String(moduleStart), 10) || moduleStart;
            const lastSlide = moduleSlideNumbers.includes(parsedLast) ? parsedLast : moduleStart;
            return {lastSlide, completed: [...new Set(completed)].sort((a,b) => a-b), updatedAt: raw.updatedAt || null};
        } catch (_) {
            return defaultProgress();
        }
    };
    let progressState = readProgress();

    const normalizeProgress = (raw) => {
        if (!raw || typeof raw !== 'object') return defaultProgress();
        const completed = Array.isArray(raw.completed)
            ? raw.completed.map(Number).filter((n) => Number.isInteger(n) && moduleSlideNumbers.includes(n))
            : [];
        return {
            lastSlide: moduleSlideNumbers.includes(Number.parseInt(raw.last_slide ?? raw.lastSlide ?? String(moduleStart), 10)) ? Number.parseInt(raw.last_slide ?? raw.lastSlide ?? String(moduleStart), 10) : moduleStart,
            completed: [...new Set(completed)].sort((a,b) => a-b),
            updatedAt: raw.updated_at ?? raw.updatedAt ?? null,
        };
    };

    const readServerProgress = async () => {
        if (!progressUrl) return null;
        try {
            const requestUrl = new URL(progressUrl, window.location.href);
            requestUrl.searchParams.set('induction_id', inductionId);
            requestUrl.searchParams.set('module_id', moduleId);
            const response = await fetch(requestUrl, {credentials: 'same-origin', headers: {'Accept':'application/json'}});
            if (!response.ok) return null;
            const payload = await response.json();
            if (!payload?.success || !payload.data) return null;
            return normalizeProgress(payload.data);
        } catch (_) {
            return null;
        }
    };

    const writeServerProgress = async () => {
        if (!progressUrl || !csrfToken) return;
        try {
            await fetch(progressUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type':'application/json','Accept':'application/json'},
                keepalive: true,
                body: JSON.stringify({
                    csrf_token: csrfToken,
                    induction_id: inductionId,
                    module_id: moduleId,
                    last_slide: progressState.lastSlide,
                    completed: progressState.completed,
                }),
            });
        } catch (_) {}
    };

    const serverProgress = await readServerProgress();
    if (serverProgress) {
        const mergedCompleted = [...new Set([...progressState.completed, ...serverProgress.completed])].sort((a,b)=>a-b);
        const serverIsNewer = Boolean(serverProgress.updatedAt) && (!progressState.updatedAt || String(serverProgress.updatedAt) > String(progressState.updatedAt));
        progressState = {
            lastSlide: serverIsNewer ? serverProgress.lastSlide : progressState.lastSlide,
            completed: mergedCompleted,
            updatedAt: serverIsNewer ? serverProgress.updatedAt : progressState.updatedAt,
        };
        try { localStorage.setItem(storageKey, JSON.stringify(progressState)); } catch (_) {}
    }

    const persistProgress = () => {
        progressState.updatedAt = new Date().toISOString();
        try { localStorage.setItem(storageKey, JSON.stringify(progressState)); } catch (_) {}
        if (serverSyncTimer !== null) window.clearTimeout(serverSyncTimer);
        serverSyncTimer = window.setTimeout(() => { writeServerProgress(); }, 180);
    };
    const isSlideCompleted = (slideNumber) => progressState.completed.includes(Number(slideNumber));
    const highestUnlockedSlide = () => {
        for (const number of moduleSlideNumbers) {
            if (!isSlideCompleted(number)) return number;
        }
        return moduleEnd;
    };
    const isSlideAccessible = (slideNumber) => {
        const n = Number(slideNumber);
        const idx = slideIndexByNumber.get(n);
        if (idx === undefined) return false;
        if (isSlideCompleted(n)) return true;
        const unlockedIdx = slideIndexByNumber.get(highestUnlockedSlide());
        return idx <= unlockedIdx;
    };
    const saveLastSlide = (slideNumber) => {
        const n = Number(slideNumber);
        if (!isSlideAccessible(n)) return;
        progressState.lastSlide = moduleSlideNumbers.includes(n) ? n : moduleStart;
        persistProgress();
    };
    const markSlideCompleted = (slideNumber) => {
        const n = Number(slideNumber);
        if (!Number.isInteger(n) || !moduleSlideNumbers.includes(n)) return;
        if (!isSlideCompleted(n)) {
            progressState.completed.push(n);
            progressState.completed.sort((a,b) => a-b);
        }
        progressState.lastSlide = n;
        persistProgress();
        updateThumbAccess();
    };

    const url = new URL(window.location.href);
    const requested = Number.parseInt(url.searchParams.get('slide') || '', 10);
    const desiredSlide = Number.isInteger(requested) ? requested : progressState.lastSlide;
    const safeInitialSlide = isSlideAccessible(desiredSlide) ? desiredSlide : highestUnlockedSlide();
    let index = slideIndexByNumber.get(safeInitialSlide) ?? 0;

    let currentStep = 0;
    let activeMedia = null;
    let layerMap = new Map();
    let loadingSlide = false;
    let renderToken = 0;
    let timers = [];
    let sequenceRunId = 0;
    let sequenceRunning = false;
    let sequenceStarted = false;
    const reducedMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches === true;
    const VISUAL_MEDIA_LEAD_MS = 3000;

    let mediaState = 'idle'; // idle | ready | playing | completed
    let mediaCompletionTimer = null;
    let sequenceCompleted = false;

    const clearTimers = () => {
        timers.forEach((id) => window.clearTimeout(id));
        timers = [];
    };

    const clearMediaCompletionTimer = () => {
        if (mediaCompletionTimer !== null) {
            window.clearTimeout(mediaCompletionTimer);
            mediaCompletionTimer = null;
        }
    };

    const cancelSequence = () => {
        sequenceRunId += 1;
        sequenceRunning = false;
        sequenceStarted = false;
        sequenceCompleted = false;
    };

    const currentSlide = () => slides[index];

    function updateThumbAccess() {
        thumbs.forEach((thumb) => {
            const number = Number.parseInt(thumb.dataset.slideNumber || '', 10);
            const accessible = isSlideAccessible(number);
            thumb.disabled = !accessible;
            thumb.setAttribute('aria-disabled', accessible ? 'false' : 'true');
            thumb.title = accessible ? '' : 'Completa la lámina anterior para desbloquear esta lámina.';
        });
        if (evaluationOpen) {
            const allSlidesDone = moduleSlideNumbers.every(isSlideCompleted);
            evaluationOpen.disabled = !allSlidesDone;
            evaluationOpen.setAttribute('aria-disabled', allSlidesDone ? 'false' : 'true');
            evaluationOpen.title = allSlidesDone ? '' : 'Completa todas las láminas del módulo para rendir la evaluación.';
        }
    }

    const currentSlideCompleted = () => isSlideCompleted(currentSlide()?.number);

    const maybeMarkPresentationCompleted = () => {
        const slide = currentSlide();
        if (!slide || currentSlideCompleted()) return;
        if (presentationComplete(slide)) markSlideCompleted(slide.number);
    };

    const slideHasPresentation = (slide = currentSlide()) => {
        if (!slide) return false;
        return Boolean(slide.media) || Number(slide.animation?.maxStep || 0) > 0;
    };

    const mediaBlocksNavigation = (slide = currentSlide()) => {
        if (!slide?.media || !sequenceStarted) return false;
        return mediaState !== 'completed';
    };

    const presentationComplete = (slide = currentSlide()) => {
        if (!slideHasPresentation(slide)) return false;
        const maxStep = Number(slide.animation?.maxStep || 0);
        const visualDone = maxStep === 0 || sequenceCompleted || currentStep >= maxStep;
        const mediaDone = !slide.media || mediaState === 'completed';
        return visualDone && mediaDone && sequenceStarted && !sequenceRunning;
    };

    const refreshControls = () => {
        const slide = currentSlide();
        if (!slide) return;
        const maxStep = Number(slide.animation?.maxStep || 0);
        const blocking = sequenceRunning || mediaBlocksNavigation(slide);
        if (prevButton) {
            const initialStep = maxStep > 0 ? 1 : 0;
            prevButton.disabled = loadingSlide || (index === 0 && currentStep <= initialStep && !sequenceStarted);
        }
        if (nextButton) {
            const moduleFinishedHere = index >= slides.length - 1 && currentSlideCompleted() && !hasEvaluation;
            nextButton.disabled = loadingSlide || blocking || moduleFinishedHere;
            nextButton.setAttribute('aria-busy', blocking ? 'true' : 'false');
        }
        if (restartButton) {
            restartButton.hidden = !presentationComplete(slide);
            restartButton.disabled = loadingSlide || sequenceRunning;
        }
    };

    const setLoading = (isLoading) => {
        loadingSlide = isLoading;
        if (loading) loading.hidden = !isLoading;
        refreshControls();
    };

    const stopActiveMedia = () => {
        clearMediaCompletionTimer();
        if (activeMedia && typeof activeMedia.pause === 'function') {
            try { activeMedia.pause(); } catch (_) {}
        }
        activeMedia = null;
        mediaState = 'idle';
        if (mediaLayer) mediaLayer.innerHTML = '';
        refreshControls();
    };

    const stylePosition = (element, item) => {
        element.style.left = `${Number(item.x || 0)}%`;
        element.style.top = `${Number(item.y || 0)}%`;
        element.style.width = `${Number(item.w || 0)}%`;
        element.style.height = `${Number(item.h || 0)}%`;
    };

    const markMediaCompleted = () => {
        mediaState = 'completed';
        clearMediaCompletionTimer();
        const hotspot = mediaLayer?.querySelector('.hs-induction__audio-hotspot');
        hotspot?.classList.remove('is-playing');
        refreshControls();
        if (buildState && sequenceCompleted) {
            buildState.hidden = false;
            buildState.textContent = 'Contenido completado';
        }
        maybeMarkPresentationCompleted();
    };

    const mountMedia = (slide, {play = false} = {}) => {
        stopActiveMedia();
        if (!mediaLayer || !slide.media || !slide.media.src) return;
        const media = slide.media;
        mediaState = 'ready';

        if (media.type === 'video') {
            const video = document.createElement('video');
            video.className = 'hs-induction__video';
            video.src = media.src;
            if (media.poster) video.poster = media.poster;
            video.controls = true;
            video.preload = 'metadata';
            video.playsInline = true;
            video.setAttribute('aria-label', `Video de la lámina ${slide.number}: ${slide.title}`);
            stylePosition(video, media);
            video.addEventListener('play', () => {
                mediaState = 'playing';
                refreshControls();
            });
            video.addEventListener('ended', markMediaCompleted);
            mediaLayer.appendChild(video);
            activeMedia = video;
            if (play) {
                video.play().catch(() => {
                    mediaState = 'ready';
                    refreshControls();
                });
            }
            refreshControls();
            return;
        }

        if (media.type === 'animation') {
            const animated = document.createElement('img');
            animated.className = 'hs-induction__animation';
            animated.alt = `Animación de la lámina ${slide.number}: ${slide.title}`;
            animated.hidden = true;
            stylePosition(animated, media);
            mediaLayer.appendChild(animated);
            activeMedia = animated;
            refreshControls();
            if (play) startMountedMedia();
            return;
        }

        if (media.type === 'audio') {
            // Audio is controlled exclusively from the toolbar button.
            // The PPT speaker icon is removed from the patched base assets to avoid duplicate controls.
            const audio = document.createElement('audio');
            audio.src = media.src;
            audio.preload = 'metadata';
            audio.setAttribute('aria-label', `Audio de la lámina ${slide.number}: ${slide.title}`);
            audio.addEventListener('play', () => {
                mediaState = 'playing';
                refreshControls();
            });
            audio.addEventListener('ended', markMediaCompleted);
            mediaLayer.appendChild(audio);
            activeMedia = audio;
            if (play) {
                audio.play().catch(() => {
                    mediaState = 'ready';
                    refreshControls();
                });
            }
            refreshControls();
            return;
        }
    };

    const startMountedMedia = () => {
        const slide = currentSlide();
        const media = slide?.media;
        if (!media || !activeMedia) return;

        clearMediaCompletionTimer();

        if (media.type === 'audio') {
            const panel = mediaLayer?.querySelector('.hs-induction__audio-panel');
            const hotspot = mediaLayer?.querySelector('.hs-induction__audio-hotspot');
            if (panel) panel.hidden = false;
            hotspot?.classList.add('is-playing');
            try { activeMedia.currentTime = 0; } catch (_) {}
            mediaState = 'playing';
            refreshControls();
            activeMedia.play().catch(() => {
                mediaState = 'ready';
                hotspot?.classList.remove('is-playing');
                refreshControls();
            });
            return;
        }

        if (media.type === 'video') {
            try { activeMedia.currentTime = 0; } catch (_) {}
            mediaState = 'playing';
            refreshControls();
            activeMedia.play().catch(() => {
                mediaState = 'ready';
                refreshControls();
            });
            return;
        }

        if (media.type === 'animation') {
            activeMedia.hidden = false;
            activeMedia.src = `${media.src}${media.src.includes('?') ? '&' : '?'}restart=${Date.now()}`;
            mediaState = 'playing';
            refreshControls();
            const durationMs = Math.max(500, Number(media.durationMs || 6000));
            mediaCompletionTimer = window.setTimeout(markMediaCompleted, durationMs);
        }
    };

    const mountAnimationLayers = (slide) => {
        clearTimers();
        layerMap = new Map();
        if (!animationLayer) return;
        animationLayer.innerHTML = '';
        const layers = Array.isArray(slide.animation?.layers) ? slide.animation.layers : [];
        layers.forEach((layer) => {
            const el = document.createElement('img');
            el.className = 'hs-induction__object-layer';
            if (layer.className) {
                String(layer.className).split(/\s+/).filter(Boolean).forEach((className) => el.classList.add(className));
            }
            el.src = layer.asset;
            el.alt = '';
            el.dataset.shapeId = String(layer.shapeId);
            stylePosition(el, layer);
            el.hidden = !layer.initialVisible;
            el.style.opacity = layer.initialVisible ? '1' : '0';
            el.style.transform = 'translate(0px, 0px)';
            animationLayer.appendChild(el);
            layerMap.set(String(layer.shapeId), el);
        });
    };

    const entranceFrames = (effect) => {
        const filter = String(effect.filter || '').toLowerCase();
        const preset = String(effect.presetId || '');
        if (filter.includes('barn')) {
            return [
                {opacity: 1, clipPath: 'inset(50% 0 50% 0)'},
                {opacity: 1, clipPath: 'inset(0 0 0 0)'}
            ];
        }
        if (preset === '2') {
            return [
                {opacity: 0, transform: 'translateY(26px)'},
                {opacity: 1, transform: 'translateY(0px)'}
            ];
        }
        if (preset === '1') {
            return [{opacity: 0}, {opacity: 1}];
        }
        if (preset === '31') {
            return [
                {opacity: 0, transform: 'scale(.96)'},
                {opacity: 1, transform: 'scale(1)'}
            ];
        }
        return [{opacity: 0}, {opacity: 1}];
    };

    const setEffectFinalState = (effect) => {
        const el = layerMap.get(String(effect.shapeId));
        if (!el) return;
        el.hidden = false;
        el.style.opacity = '1';
        el.style.clipPath = 'none';
        if (effect.class === 'path') {
            const dx = Number(effect.dx || 0) * (stage?.clientWidth || 0);
            const dy = Number(effect.dy || 0) * (stage?.clientHeight || 0);
            el.style.transform = `translate(${dx}px, ${dy}px)`;
        } else {
            el.style.transform = 'translate(0px, 0px)';
        }
    };

    const animateEffect = (effect, timingScale = 1) => {
        const el = layerMap.get(String(effect.shapeId));
        if (!el) return;
        el.hidden = false;
        const duration = reducedMotion ? 1 : Math.max(60, Math.min(2200, Number(effect.durationMs || 500) * Math.max(0.1, timingScale)));

        if (effect.class === 'path') {
            const dx = Number(effect.dx || 0) * (stage?.clientWidth || 0);
            const dy = Number(effect.dy || 0) * (stage?.clientHeight || 0);
            el.style.opacity = '1';
            if (reducedMotion || !el.animate) {
                el.style.transform = `translate(${dx}px, ${dy}px)`;
                return;
            }
            const anim = el.animate([
                {transform: 'translate(0px, 0px)'},
                {transform: `translate(${dx}px, ${dy}px)`}
            ], {duration, easing: 'ease-in-out', fill: 'forwards'});
            anim.onfinish = () => { el.style.transform = `translate(${dx}px, ${dy}px)`; };
            return;
        }

        const frames = entranceFrames(effect);
        if (reducedMotion || !el.animate) {
            setEffectFinalState(effect);
            return;
        }
        const anim = el.animate(frames, {duration, easing: 'ease-out', fill: 'forwards'});
        anim.onfinish = () => setEffectFinalState(effect);
    };

    const applyStepState = (step, {animate = false, timingScale = 1} = {}) => {
        const slide = slides[index];
        const effects = Array.isArray(slide.animation?.effects) ? slide.animation.effects : [];
        const maxStep = Number(slide.animation?.maxStep || 0);
        currentStep = Math.max(0, Math.min(maxStep, step));
        clearTimers();

        // Reset all object layers to their initial presentation state.
        (slide.animation?.layers || []).forEach((layer) => {
            const el = layerMap.get(String(layer.shapeId));
            if (!el) return;
            el.getAnimations?.().forEach((a) => a.cancel());
            el.hidden = !layer.initialVisible;
            el.style.opacity = layer.initialVisible ? '1' : '0';
            el.style.transform = 'translate(0px, 0px)';
            el.style.clipPath = 'none';
        });

        // Restore effects from previous build steps immediately.
        effects.filter((effect) => Number(effect.step) < currentStep).forEach(setEffectFinalState);

        // Current build step gets the original entrance/motion effect.
        effects.filter((effect) => Number(effect.step) === currentStep).forEach((effect) => {
            if (!animate || currentStep === 0) {
                setEffectFinalState(effect);
                return;
            }
            const delay = reducedMotion ? 0 : Math.max(0, Math.min(6000, Number(effect.delayMs || 0) * Math.max(0.1, timingScale)));
            timers.push(window.setTimeout(() => animateEffect(effect, timingScale), delay));
        });

        if (buildState) {
            buildState.hidden = maxStep === 0;
            buildState.textContent = maxStep ? (sequenceRunning ? `Efecto ${currentStep} / ${maxStep}` : (currentStep >= maxStep ? 'Secuencia completada' : 'Listo para iniciar')) : '';
        }
        refreshControls();
    };

    const stepDurationMs = (slide, step) => {
        const effects = (slide.animation?.effects || []).filter((effect) => Number(effect.step) === Number(step));
        if (!effects.length) return reducedMotion ? 1 : 450;
        const longest = Math.max(...effects.map((effect) => {
            const delay = Math.max(0, Number(effect.delayMs || 0));
            const duration = Math.max(120, Math.min(2200, Number(effect.durationMs || 500)));
            return delay + duration;
        }));
        return reducedMotion ? 1 : Math.min(3000, longest + 180);
    };

    const sequencePlan = (slide, fromStep, toStep) => {
        const steps = [];
        for (let step = fromStep; step <= toStep; step += 1) {
            steps.push({step, nominalMs: stepDurationMs(slide, step)});
        }
        const nominalTotal = steps.reduce((sum, item) => sum + item.nominalMs, 0);
        const mediaDurationMs = Math.max(0, Number(slide.media?.durationMs || 0));
        const visualBudgetMs = mediaDurationMs > VISUAL_MEDIA_LEAD_MS
            ? Math.max(1, mediaDurationMs - VISUAL_MEDIA_LEAD_MS)
            : nominalTotal;
        const waitScale = nominalTotal > 0 && mediaDurationMs > 0 && nominalTotal > visualBudgetMs
            ? Math.max(0.1, visualBudgetMs / nominalTotal)
            : 1;
        const effectScale = Math.min(1, waitScale);
        return {steps, waitScale, effectScale};
    };

    const playAutomaticSequence = async () => {
        if (loadingSlide || sequenceRunning || mediaBlocksNavigation()) return;
        const slide = currentSlide();
        const maxStep = Number(slide.animation?.maxStep || 0);
        const runId = ++sequenceRunId;
        sequenceStarted = true;
        sequenceRunning = true;
        sequenceCompleted = maxStep <= currentStep;

        // Multimedia is always the first timed component.
        if (slide.media) startMountedMedia();
        refreshControls();

        if (buildState) {
            buildState.hidden = false;
            buildState.textContent = maxStep > currentStep ? 'Reproduciendo secuencia…' : 'Multimedia en reproducción';
        }

        const plan = sequencePlan(slide, currentStep + 1, maxStep);
        for (const item of plan.steps) {
            if (runId !== sequenceRunId || slide !== currentSlide()) return;
            applyStepState(item.step, {animate: true, timingScale: plan.effectScale});
            const waitMs = reducedMotion ? 1 : Math.max(1, item.nominalMs * plan.waitScale);
            await new Promise((resolve) => window.setTimeout(resolve, waitMs));
        }

        if (runId !== sequenceRunId || slide !== currentSlide()) return;
        sequenceRunning = false;
        sequenceCompleted = true;
        refreshControls();
        maybeMarkPresentationCompleted();

        if (buildState) {
            buildState.hidden = false;
            if (slide.media && mediaState !== 'completed') {
                buildState.textContent = 'Secuencia visual completada · multimedia en curso';
            } else {
                buildState.textContent = 'Contenido completado';
            }
        }
    };

    const updateMediaButton = (slide) => {
        if (!mediaButton) return;
        const media = slide.media;
        mediaButton.hidden = !media;
        if (!media) return;
        const isVideo = media.type === 'video';
        const isAudio = media.type === 'audio';
        const icon = mediaButton.querySelector('i');
        if (icon) icon.className = isVideo ? 'bi bi-play-circle-fill' : (isAudio ? 'bi bi-volume-up-fill' : 'bi bi-arrow-repeat');
        if (mediaLabel) mediaLabel.textContent = isVideo ? 'Reproducir video' : (isAudio ? 'Reproducir audio' : 'Reproducir animación');
        mediaButton.title = `Esta lámina contiene ${isVideo ? 'video' : (isAudio ? 'audio' : 'animación')}`;
    };

    const updateFullscreenIcon = () => {
        if (!fullscreenButton) return;
        const icon = fullscreenButton.querySelector('i');
        if (icon) icon.className = document.fullscreenElement ? 'bi bi-fullscreen-exit' : 'bi bi-fullscreen';
        fullscreenButton.setAttribute('aria-label', document.fullscreenElement ? 'Salir de pantalla completa' : 'Pantalla completa');
    };

    const updateNavigationMeta = (slide) => {
        if (titleLabel) titleLabel.textContent = slide.title;
        if (currentLabel) currentLabel.textContent = `${index + 1} / ${slides.length}`;
        if (progressBar) progressBar.style.width = `${((index + 1) / slides.length) * 100}%`;
        updateMediaButton(slide);
        updateThumbAccess();
        thumbs.forEach((thumb, thumbIndex) => {
            const active = thumbIndex === index;
            thumb.classList.toggle('is-active', active);
            thumb.setAttribute('aria-current', active ? 'true' : 'false');
        });
        thumbs[index]?.scrollIntoView({behavior: reducedMotion ? 'auto' : 'smooth', block: 'nearest', inline: 'nearest'});
    };

    const renderSlide = (nextIndex, {step = 'initial', pushState = true, saveProgress = true} = {}) => {
        const requestedIndex = Math.max(0, Math.min(slides.length - 1, Number(nextIndex) || 0));
        const requestedNumber = slides[requestedIndex].number;
        const safeNumber = isSlideAccessible(requestedNumber) ? requestedNumber : highestUnlockedSlide();
        index = slideIndexByNumber.get(safeNumber) ?? 0;
        const slide = slides[index];
        const maxStep = Number(slide.animation?.maxStep || 0);
        const configuredInitialStep = Number(slide.animation?.initialStep);
        const initialStep = Number.isFinite(configuredInitialStep)
            ? Math.max(0, Math.min(maxStep, configuredInitialStep))
            : (maxStep > 0 ? 1 : 0);
        // VS4 functional constant:
        // - First visit: preserve the initial build and require the user's second interaction
        //   to launch the automatic visual sequence.
        // - Previously completed slide: render its complete visual state immediately so the
        //   user can review/skip it without losing content that was already completed.
        const completedReview = step === 'initial' && isSlideCompleted(slide.number);
        const targetStep = step === 'end'
            ? maxStep
            : (completedReview
                ? maxStep
                : (step === 'initial'
                    ? initialStep
                    : Math.max(0, Math.min(maxStep, Number(step || 0)))));
        const token = ++renderToken;

        cancelSequence();
        clearTimers();
        stopActiveMedia();
        if (animationLayer) animationLayer.innerHTML = '';
        layerMap = new Map();
        setLoading(true);
        image.classList.add('is-changing');
        updateNavigationMeta(slide);

        const finish = () => {
            if (token !== renderToken) return;
            mountAnimationLayers(slide);
            mountMedia(slide);
            applyStepState(targetStep, {animate: false});

            // A completed slide opened for review is already in its final visual state.
            // Do not replay multimedia automatically and do not mark unseen slides complete.
            sequenceCompleted = targetStep >= maxStep && maxStep > 0 && (step === 'end' || completedReview);
            refreshControls();
            image.classList.remove('is-changing');
            image.classList.add('is-entering');
            window.setTimeout(() => image.classList.remove('is-entering'), reducedMotion ? 1 : 260);
            setLoading(false);
        };

        const onLoad = () => finish();
        image.addEventListener('load', onLoad, {once: true});
        image.src = slide.asset;
        image.alt = slide.title;
        if (image.complete && image.naturalWidth > 0) {
            image.removeEventListener('load', onLoad);
            finish();
        }

        if (saveProgress) saveLastSlide(slide.number);
        if (pushState) {
            const nextUrl = new URL(window.location.href);
            const activeModuleNumber = Number.parseInt(String(moduleId).replace(/\D+/g,''),10) || 1;
            nextUrl.searchParams.set('module', String(activeModuleNumber));
            nextUrl.searchParams.set('slide', String(slide.number));
            history.replaceState(null, '', nextUrl);
        }
        // Preload the next initial-state slide.
        const nextSlide = slides[index + 1];
        if (nextSlide?.asset) { const preload = new Image(); preload.src = nextSlide.asset; }
    };

    const advance = () => {
        if (evaluationVisible) return;
        if (loadingSlide || sequenceRunning || mediaBlocksNavigation()) return;
        const slide = currentSlide();
        const maxStep = Number(slide.animation?.maxStep || 0);
        const configuredInitialStep = Number(slide.animation?.initialStep);
        const initialStep = Number.isFinite(configuredInitialStep)
            ? Math.max(0, Math.min(maxStep, configuredInitialStep))
            : (maxStep > 0 ? 1 : 0);

        // Once a slide has been completed at least once, it can be skipped freely.
        if (currentSlideCompleted()) {
            if (index < slides.length - 1 && isSlideAccessible(slides[index + 1].number)) {
                renderSlide(index + 1, {step: 'initial'});
            } else if (hasEvaluation && index === slides.length - 1) {
                showEvaluation();
            }
            return;
        }

        // Static slides are completed on the first explicit advance interaction.
        if (!slideHasPresentation(slide)) {
            markSlideCompleted(slide.number);
            if (index < slides.length - 1 && isSlideAccessible(slides[index + 1].number)) {
                renderSlide(index + 1, {step: 'initial'});
            } else if (hasEvaluation && index === slides.length - 1) {
                showEvaluation();
            }
            return;
        }

        // First interaction starts the complete presentation; it never skips an unseen slide.
        if (!sequenceStarted && (currentStep === initialStep || maxStep === 0)) {
            playAutomaticSequence();
            return;
        }

        if (currentStep < maxStep) {
            playAutomaticSequence();
            return;
        }

        maybeMarkPresentationCompleted();
        if (currentSlideCompleted() && index < slides.length - 1 && isSlideAccessible(slides[index + 1].number)) {
            renderSlide(index + 1, {step: 'initial'});
        } else if (currentSlideCompleted() && hasEvaluation && index === slides.length - 1) {
            showEvaluation();
        }
    };

    const restartCurrentSlide = () => {
        if (loadingSlide || sequenceRunning) return;
        const slide = currentSlide();
        const maxStep = Number(slide.animation?.maxStep || 0);
        const initialStep = maxStep > 0 ? 1 : 0;

        clearTimers();
        clearMediaCompletionTimer();
        if (activeMedia && typeof activeMedia.pause === 'function') {
            try {
                activeMedia.pause();
                if ('currentTime' in activeMedia) activeMedia.currentTime = 0;
            } catch (_) {}
        }

        sequenceRunId += 1;
        sequenceRunning = false;
        sequenceStarted = false;
        sequenceCompleted = false;
        mediaState = slide.media ? 'ready' : 'idle';

        mountAnimationLayers(slide);
        mountMedia(slide);
        applyStepState(initialStep, {animate: false});
        refreshControls();

        window.setTimeout(() => playAutomaticSequence(), reducedMotion ? 1 : 80);
    };

    const goBack = () => {
        if (evaluationVisible) return;
        if (loadingSlide) return;
        if (sequenceRunning) cancelSequence();
        if (currentSlideCompleted() && !sequenceStarted && index > 0) {
            renderSlide(index - 1, {step: 'initial'});
            return;
        }
        const maxStep = Number(slides[index].animation?.maxStep || 0);
        const initialStep = maxStep > 0 ? 1 : 0;

        if (currentStep > initialStep || sequenceStarted) {
            clearMediaCompletionTimer();
            sequenceStarted = false;
            sequenceCompleted = false;
            mediaState = slides[index].media ? 'ready' : 'idle';
            applyStepState(initialStep, {animate: false});
            if (activeMedia && typeof activeMedia.pause === 'function') {
                try {
                    activeMedia.pause();
                    if ('currentTime' in activeMedia) activeMedia.currentTime = 0;
                } catch (_) {}
            }
            refreshControls();
            return;
        }
        if (index > 0) renderSlide(index - 1, {step: currentSlideCompleted() ? 'initial' : 'end'});
    };

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const formatAttemptDate = (value) => {
        if (!value) return '—';
        const date = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) return String(value);
        try {
            return new Intl.DateTimeFormat(document.documentElement.lang || 'es-CL', {
                dateStyle: 'short',
                timeStyle: 'short',
            }).format(date);
        } catch (_) {
            return String(value);
        }
    };

    const renderEvaluationHistory = (attempts = []) => {
        if (!evaluationHistoryBody) return;
        const items = Array.isArray(attempts) ? attempts : [];
        if (evaluationHistoryCount) {
            evaluationHistoryCount.textContent = `${items.length} ${items.length === 1 ? 'intento' : 'intentos'}`;
        }
        if (!items.length) {
            evaluationHistoryBody.innerHTML = '<p class="hs-induction__evaluation-history-empty">Aún no registras intentos en esta evaluación.</p>';
            return;
        }
        const rows = items.map((attempt) => {
            const passed = Number(attempt.passed) === 1;
            const score = Number(attempt.score_percentage || 0);
            const correct = Number(attempt.correct_count || 0);
            const total = Number(attempt.question_count || 0);
            return `<tr>
                <td data-label="Evaluación">N° ${Number(attempt.evaluation_number || 0)}</td>
                <td data-label="Intento">N° ${Number(attempt.attempt_number || 0)}</td>
                <td data-label="Resultado">${correct}/${total} · ${Number.isFinite(score) ? score.toFixed(0) : '0'}%</td>
                <td data-label="Estado"><span class="hs-induction__attempt-status ${passed ? 'is-pass' : 'is-fail'}">${passed ? 'Aprobado' : 'No aprobado'}</span></td>
                <td data-label="Fecha">${escapeHtml(formatAttemptDate(attempt.submitted_at))}</td>
            </tr>`;
        }).join('');
        evaluationHistoryBody.innerHTML = `<div class="hs-induction__evaluation-history-table-wrap">
            <table class="hs-induction__evaluation-history-table">
                <thead><tr><th>Evaluación</th><th>Intento</th><th>Resultado</th><th>Estado</th><th>Fecha</th></tr></thead>
                <tbody>${rows}</tbody>
            </table>
        </div>`;
    };

    const loadEvaluationHistory = async () => {
        if (!evaluationUrl || !evaluationHistoryBody) return;
        try {
            const requestUrl = new URL(evaluationUrl, window.location.href);
            const response = await fetch(requestUrl, {
                credentials: 'same-origin',
                headers: {'Accept':'application/json'},
                cache: 'no-store',
            });
            const payload = await response.json();
            if (!response.ok || !payload?.success) throw new Error(payload?.message || 'No fue posible consultar el historial.');
            renderEvaluationHistory(payload.data?.attempts || []);
        } catch (_) {
            evaluationHistoryBody.innerHTML = '<p class="hs-induction__evaluation-history-empty">No fue posible cargar el historial en este momento.</p>';
        }
    };

    const clearEvaluationRedirect = () => {
        if (evaluationRedirectTimer !== null) {
            window.clearTimeout(evaluationRedirectTimer);
            evaluationRedirectTimer = null;
        }
    };

    const scheduleEvaluationRedirect = (url, delayMs = 4200) => {
        clearEvaluationRedirect();
        if (!url) return;
        evaluationRedirectTimer = window.setTimeout(() => window.location.assign(url), delayMs);
    };

    const hideEvaluation = () => {
        clearEvaluationRedirect();
        if (!evaluationPanel) return;
        evaluationPanel.hidden = true;
        evaluationVisible = false;
        if (stage) stage.hidden = false;
        refreshControls();
    };

    const showEvaluation = () => {
        if (!evaluationPanel || !moduleSlideNumbers.every(isSlideCompleted)) return;
        stopActiveMedia(); cancelSequence(); clearTimers();
        if (stage) stage.hidden = true;
        evaluationPanel.hidden = false;
        evaluationVisible = true;
        if (titleLabel) titleLabel.textContent = 'Evaluación Módulo 1';
        if (currentLabel) currentLabel.textContent = '4 preguntas';
        if (progressBar) progressBar.style.width = '100%';
        if (prevButton) prevButton.disabled = true;
        if (nextButton) nextButton.disabled = true;
        if (restartButton) restartButton.hidden = true;
        if (mediaButton) mediaButton.hidden = true;
        evaluationPanel.scrollTop = 0;
        loadEvaluationHistory();
    };

    evaluationBack?.addEventListener('click', () => {
        hideEvaluation();
        renderSlide(slides.length - 1, {step:'initial'});
    });
    evaluationOpen?.addEventListener('click', showEvaluation);

    evaluationForm?.addEventListener('change', (event) => {
        const input = event.target;
        if (!(input instanceof HTMLInputElement) || input.type !== 'radio') return;
        const fieldset = input.closest('.onboarding-question');
        fieldset?.classList.add('is-answered');
        fieldset?.querySelectorAll('.onboarding-option').forEach((option) => option.classList.toggle('is-selected', option.contains(input) && input.checked));
    });

    evaluationForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!evaluationUrl || !csrfToken) return;
        const data = new FormData(evaluationForm);
        const answers = {};
        for (const [key,value] of data.entries()) answers[key] = String(value);
        if (Object.keys(answers).length !== 4) {
            if (evaluationResult) { evaluationResult.hidden=false; evaluationResult.className='hs-induction__evaluation-result is-fail'; evaluationResult.textContent='Debes responder las 4 preguntas antes de enviar.'; }
            return;
        }
        if (evaluationSubmit) evaluationSubmit.disabled = true;
        try {
            const response = await fetch(evaluationUrl,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','Accept':'application/json'},body:JSON.stringify({csrf_token:csrfToken,answers})});
            const payload = await response.json();
            if (!response.ok || !payload?.success) throw new Error(payload?.message || 'No fue posible guardar la evaluación.');
            const r = payload.data;
            const redirectUrl = String(r.redirect_url || (r.passed ? 'health-safety.php?module=2&slide=14' : 'health-safety.php?module=1&slide=1'));
            if (evaluationResult) {
                evaluationResult.hidden = false;
                evaluationResult.className = `hs-induction__evaluation-result ${r.passed ? 'is-pass' : 'is-fail'}`;
                evaluationResult.innerHTML = `<strong>Evaluación N° ${Number(r.evaluation_number)} · Intento N° ${Number(r.attempt_number)}</strong>
                    <span>${Number(r.correct_count)}/${Number(r.question_count)} respuestas correctas · ${Number(r.score_percentage).toFixed(0)}%</span>
                    <span>${r.passed ? 'Aprobado. El Módulo 2 quedó habilitado.' : 'No aprobado. Debes obtener 50% o más para aprobar.'}</span>
                    <small>${r.passed ? 'Te llevaremos a la primera lámina del Módulo 2.' : 'Te llevaremos a la primera lámina del Módulo 1 para repasar el contenido.'}</small>
                    <a class="btn ${r.passed ? 'btn-primary-custom' : 'btn-outline-custom'}" href="${escapeHtml(redirectUrl)}">${r.passed ? 'Ir al Módulo 2' : 'Volver al Módulo 1'} <i class="bi bi-arrow-right"></i></a>`;
            }
            await loadEvaluationHistory();
            if (r.passed) {
                const tab = document.querySelector('.hs-induction__module-tab.is-disabled[title^="Aprueba"]');
                if (tab) {
                    const link = document.createElement('a');
                    link.className = 'hs-induction__module-tab';
                    link.href = 'health-safety.php?module=2&slide=14';
                    link.textContent = 'Módulo 2';
                    tab.replaceWith(link);
                }
            }
            scheduleEvaluationRedirect(redirectUrl);
        } catch (error) {
            if (evaluationResult) { evaluationResult.hidden=false; evaluationResult.className='hs-induction__evaluation-result is-fail'; evaluationResult.textContent=error instanceof Error?error.message:'No fue posible guardar la evaluación.'; }
        } finally { if (evaluationSubmit) evaluationSubmit.disabled=false; }
    });

    image.addEventListener('error', () => {
        setLoading(false);
        if (loading) {
            loading.hidden = false;
            loading.textContent = 'No fue posible cargar esta lámina.';
        }
    });

    mediaButton?.addEventListener('click', () => {
        if (sequenceRunning) return;
        if (!sequenceStarted) {
            playAutomaticSequence();
            return;
        }
        if (!currentSlide()?.media) return;
        startMountedMedia();
    });
    restartButton?.addEventListener('click', restartCurrentSlide);
    prevButton?.addEventListener('click', goBack);
    nextButton?.addEventListener('click', advance);

    thumbs.forEach((thumb) => thumb.addEventListener('click', () => {
        const number = Number.parseInt(thumb.dataset.slideNumber || '', 10);
        if (!isSlideAccessible(number)) return;
        const targetIndex = slideIndexByNumber.get(number);
        if (targetIndex === undefined) return;
        hideEvaluation();
        renderSlide(targetIndex, {step: 'initial'});
    }));

    indexButton?.addEventListener('click', () => {
        root.classList.toggle('is-index-hidden');
        const hidden = root.classList.contains('is-index-hidden');
        indexButton.setAttribute('aria-pressed', hidden ? 'true' : 'false');
        const label = indexButton.querySelector('span');
        if (label) label.textContent = hidden ? 'Mostrar índice' : 'Ocultar índice';
    });

    fullscreenButton?.addEventListener('click', async () => {
        try {
            if (document.fullscreenElement) {
                await document.exitFullscreen();
                return;
            }
            // Functional constant VS4: every fullscreen session begins at slide 1.
            hideEvaluation();
            renderSlide(0, {step: 'initial', pushState: true, saveProgress: false});
            if (viewer?.requestFullscreen) await viewer.requestFullscreen();
        } catch (_) {}
    });
    document.addEventListener('fullscreenchange', updateFullscreenIcon);

    stage?.addEventListener('click', (event) => {
        if (event.target.closest('video,audio,button')) return;
        advance();
    });

    document.addEventListener('keydown', (event) => {
        const target = event.target;
        if (evaluationVisible && !(target instanceof HTMLInputElement || target instanceof HTMLButtonElement || target instanceof HTMLAnchorElement)) return;
        if (target instanceof HTMLInputElement || target instanceof HTMLTextAreaElement || target instanceof HTMLSelectElement || target instanceof HTMLAudioElement || target instanceof HTMLVideoElement) return;
        if (event.key === 'ArrowLeft' || event.key === 'PageUp') {
            event.preventDefault(); goBack();
        } else if (event.key === 'ArrowRight' || event.key === 'PageDown' || event.key === ' ') {
            event.preventDefault(); advance();
        } else if (event.key === 'Home') {
            event.preventDefault(); hideEvaluation(); renderSlide(0, {step: 'initial'});
        } else if (event.key === 'End') {
            event.preventDefault(); hideEvaluation(); renderSlide(slideIndexByNumber.get(highestUnlockedSlide()) ?? 0, {step: 'initial'});
        }
    });

    let pointerStartX = null;
    viewer?.addEventListener('pointerdown', (event) => {
        if (evaluationVisible) return;
        if (event.pointerType === 'mouse' || event.target.closest('video,audio,button')) return;
        pointerStartX = event.clientX;
    });
    viewer?.addEventListener('pointerup', (event) => {
        if (evaluationVisible) return;
        if (pointerStartX === null || event.pointerType === 'mouse' || event.target.closest('video,audio,button')) return;
        const delta = event.clientX - pointerStartX;
        pointerStartX = null;
        if (Math.abs(delta) < 45) return;
        delta > 0 ? goBack() : advance();
    });

    window.addEventListener('resize', () => {
        // Motion-path translations are relative to the stage, so rebuild current state after resize.
        if (!loadingSlide) applyStepState(currentStep, {animate: false});
    });

    updateThumbAccess();
    renderSlide(index, {step: 'initial', pushState: true});
    updateFullscreenIcon();
})();
