{{-- ================================================================
     KAHOOT BUILDER PARTIAL
     Included inside the event create/edit form.
     Fully managed by vanilla JS - no page reload needed.
     ================================================================ --}}

@php
    $fieldMap = [];
    foreach (($fields ?? []) as $f) {
        if (!empty($f['name'])) {
            $fieldMap[$f['name']] = $f;
        }
    }

    $kahootCenterOptions = $fieldMap['educational_center_id']['options'] ?? [];
    $kahootCenterValue = old('kahoot_educational_center_id', $fieldMap['educational_center_id']['selectedValue'] ?? '');
    $kahootRoleOptions = $fieldMap['target_role']['options'] ?? [];
    $kahootRoleValue = old('kahoot_target_role', $fieldMap['target_role']['selectedValue'] ?? '');
@endphp

<div id="kahoot-section" class="hidden mt-8 border-t border-white/10 pt-6">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-violet-500 to-pink-500 flex items-center justify-center shadow-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.347.346A3.976 3.976 0 0 1 14 20H10a3.976 3.976 0 0 1-2.79-1.155l-.347-.346z"/>
            </svg>
        </div>
        <div>
            <h2 class="text-white font-bold text-lg leading-tight">Modo Kahoot</h2>
            <p class="text-white/40 text-xs">Crea un cuestionario interactivo para este evento</p>
        </div>
    </div>

    {{-- Kahoot base event fields --}}
    <div class="mb-6 p-4 rounded-2xl bg-white/5 border border-white/10">
        <h3 class="text-white font-semibold text-sm mb-3">Datos del Simulacro Kahoot</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="kahoot_title" class="block text-sm font-medium text-white/70 mb-1">Nombre del Kahoot <span class="text-red-500">*</span></label>
                <input id="kahoot_title" name="kahoot_title" type="text" value="{{ old('kahoot_title') }}" placeholder="Ej: Kahoot de Repaso"
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-hidden focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200 scheme-dark" required>
            </div>

            <div>
                <label for="kahoot_educational_center_id" class="block text-sm font-medium text-white/70 mb-1">Centro Organizador <span class="text-red-500">*</span></label>
                <select id="kahoot_educational_center_id" name="kahoot_educational_center_id"
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-hidden focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200 scheme-dark" required>
                    <option value="" class="bg-[#1a202c]">Seleccionar centro...</option>
                    @foreach($kahootCenterOptions as $val => $text)
                        <option value="{{ $val }}" {{ (string)$kahootCenterValue === (string)$val ? 'selected' : '' }} class="bg-[#1a202c]">{{ $text }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label for="kahoot_description" class="block text-sm font-medium text-white/70 mb-1">Descripcion</label>
                <textarea id="kahoot_description" name="kahoot_description" rows="3" placeholder="Detalles del simulacro..."
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-hidden focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200 scheme-dark">{{ old('kahoot_description') }}</textarea>
            </div>

            <div>
                <label for="kahoot_date" class="block text-sm font-medium text-white/70 mb-1">Fecha <span class="text-red-500">*</span></label>
                <input id="kahoot_date" name="kahoot_date" type="date" value="{{ old('kahoot_date') }}"
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-hidden focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200 scheme-dark" required>
            </div>

            <div>
                <label for="kahoot_location" class="block text-sm font-medium text-white/70 mb-1">Lugar Exacto</label>
                <input id="kahoot_location" name="kahoot_location" type="text" value="{{ old('kahoot_location') }}" placeholder="Ej: Aula de Informatica"
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-hidden focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200 scheme-dark">
            </div>

            <div>
                <label for="kahoot_start_time" class="block text-sm font-medium text-white/70 mb-1">Hora Inicio <span class="text-red-500">*</span></label>
                <input id="kahoot_start_time" name="kahoot_start_time" type="time" value="{{ old('kahoot_start_time') }}"
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-hidden focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200 scheme-dark" required>
            </div>

            <div>
                <label for="kahoot_end_time" class="block text-sm font-medium text-white/70 mb-1">Hora Fin <span class="text-red-500">*</span></label>
                <input id="kahoot_end_time" name="kahoot_end_time" type="time" value="{{ old('kahoot_end_time') }}"
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-hidden focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200 scheme-dark" required>
            </div>

            <div>
                <label for="kahoot_target_role" class="block text-sm font-medium text-white/70 mb-1">Dirigido A</label>
                <select id="kahoot_target_role" name="kahoot_target_role"
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-hidden focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200 scheme-dark">
                    <option value="" class="bg-[#1a202c]">Todos los roles pueden unirse</option>
                    @foreach($kahootRoleOptions as $val => $text)
                        <option value="{{ $val }}" {{ (string)$kahootRoleValue === (string)$val ? 'selected' : '' }} class="bg-[#1a202c]">{{ $text }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Gemini AI section --}}
    <div class="mb-6 p-4 rounded-2xl bg-white/5 border border-violet-500/20">
        <div class="flex items-center gap-2 mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            <span class="text-violet-300 font-semibold text-sm">Generar preguntas con IA desde un PDF</span>
        </div>
        <p class="text-white/40 text-xs mb-4">Sube un PDF con el material del tema y Gemini AI creara las preguntas automaticamente.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div class="md:col-span-2">
                <label class="block text-white/60 text-xs font-medium mb-1">Numero de preguntas a generar</label>
                <input id="kahoot-num-questions" type="number" value="10" min="1" max="30"
                    class="w-32 bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-white text-sm focus:outline-none focus:border-violet-500/60 transition-colors" />
            </div>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            <label for="kahoot-pdf-input" id="kahoot-pdf-label"
                class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 hover:bg-white/15 text-white text-sm font-medium transition-all duration-200 border border-white/10">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span id="kahoot-pdf-label-text">Seleccionar PDF</span>
            </label>
            <input id="kahoot-pdf-input" type="file" accept="application/pdf" class="hidden" />

            <button type="button" id="kahoot-generate-btn"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-violet-600 to-pink-600 hover:from-violet-500 hover:to-pink-500 text-white text-sm font-semibold transition-all duration-200 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                <svg id="kahoot-generate-icon" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <svg id="kahoot-spinner" class="hidden w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span id="kahoot-generate-text">Generar con IA</span>
            </button>
        </div>

        <div id="kahoot-error" class="hidden mt-3 p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-xs"></div>
    </div>

    <div id="kahoot-questions-list" class="space-y-4 mb-4"></div>

    <button type="button" id="kahoot-add-btn"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 border-dashed text-white/60 hover:text-white text-sm transition-all duration-200 cursor-pointer">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Anadir pregunta
    </button>
</div>

<input type="hidden" name="is_kahoot" id="is_kahoot_input" value="0" />
<input type="hidden" name="kahoot_questions" id="kahoot_questions_input" value="" />

<style>
.kahoot-answer-btn {
    flex: 1;
    min-width: 0;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 12px;
    border: 2px solid rgba(255,255,255,0.1);
    background: rgba(255,255,255,0.03);
    color: rgba(255,255,255,0.7);
    cursor: pointer;
    transition: all 0.15s ease;
    text-align: left;
}
.kahoot-answer-btn:hover { background: rgba(255,255,255,0.07); border-color: rgba(255,255,255,0.2); }
.kahoot-answer-btn.correct {
    border-color: rgba(34,197,94,0.6);
    background: rgba(34,197,94,0.12);
    color: #86efac;
}
.kahoot-answer-btn input[type="text"] {
    background: transparent;
    border: none;
    outline: none;
    color: inherit;
    width: 100%;
    font-size: 0.85rem;
}
.kahoot-answer-btn input[type="text"]::placeholder { color: rgba(255,255,255,0.25); }
.kahoot-correct-radio { display: none; }
.answer-color-0 { --ac: #e55c5c; }
.answer-color-1 { --ac: #5b8dee; }
.answer-color-2 { --ac: #f5a623; }
.answer-color-3 { --ac: #57c77a; }
.answer-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    background: var(--ac, #888);
    flex-shrink: 0;
}
.kahoot-q-card {
    background: linear-gradient(135deg, rgba(255,255,255,0.06) 0%, rgba(255,255,255,0.02) 100%);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 16px;
    padding: 16px;
}
</style>

<script>
(function() {
    let kahootEnabled = false;
    let questions = [];
    let selectedPdfBase64 = null;

    const colors = ['#e55c5c','#5b8dee','#f5a623','#57c77a'];
    const letters = ['A','B','C','D'];

    const toggleNormalBtn = document.getElementById('toggle-normal-btn');
    const toggleKahootBtn = document.getElementById('toggle-kahoot-btn');
    const normalSection = document.getElementById('normal-event-section');
    const section = document.getElementById('kahoot-section');

    const listEl = document.getElementById('kahoot-questions-list');
    const addBtn = document.getElementById('kahoot-add-btn');
    const generateBtn = document.getElementById('kahoot-generate-btn');
    const pdfInput = document.getElementById('kahoot-pdf-input');
    const pdfLabelText = document.getElementById('kahoot-pdf-label-text');
    const numQInput = document.getElementById('kahoot-num-questions');
    const errorBox = document.getElementById('kahoot-error');
    const spinner = document.getElementById('kahoot-spinner');
    const generateIcon = document.getElementById('kahoot-generate-icon');
    const generateText = document.getElementById('kahoot-generate-text');
    const isKahootInput = document.getElementById('is_kahoot_input');
    const kahootQsInput = document.getElementById('kahoot_questions_input');
    const mainForm = document.getElementById('formGeneral');

    function setSectionInputsEnabled(sectionEl, enabled) {
        if (!sectionEl) return;
        sectionEl.querySelectorAll('input, select, textarea, button').forEach((el) => {
            el.disabled = !enabled;
        });
    }

    function switchMode(isKahoot) {
        kahootEnabled = isKahoot;

        if (normalSection) normalSection.classList.toggle('hidden', isKahoot);
        if (section) section.classList.toggle('hidden', !isKahoot);

        setSectionInputsEnabled(normalSection, !isKahoot);
        setSectionInputsEnabled(section, isKahoot);

        if (isKahootInput) isKahootInput.value = isKahoot ? '1' : '0';

        if (isKahoot) {
            if (toggleKahootBtn) toggleKahootBtn.classList.add('bg-white/10', 'text-white', 'shadow-sm');
            if (toggleKahootBtn) toggleKahootBtn.classList.remove('text-white/40', 'hover:text-white/70');
            if (toggleNormalBtn) toggleNormalBtn.classList.remove('bg-white/10', 'text-white', 'shadow-sm');
            if (toggleNormalBtn) toggleNormalBtn.classList.add('text-white/40', 'hover:text-white/70');
            if (questions.length === 0) addQuestion();
        } else {
            if (toggleNormalBtn) toggleNormalBtn.classList.add('bg-white/10', 'text-white', 'shadow-sm');
            if (toggleNormalBtn) toggleNormalBtn.classList.remove('text-white/40', 'hover:text-white/70');
            if (toggleKahootBtn) toggleKahootBtn.classList.remove('bg-white/10', 'text-white', 'shadow-sm');
            if (toggleKahootBtn) toggleKahootBtn.classList.add('text-white/40', 'hover:text-white/70');
            if (kahootQsInput) kahootQsInput.value = '';
        }
    }

    if (toggleNormalBtn) toggleNormalBtn.addEventListener('click', () => switchMode(false));
    if (toggleKahootBtn) toggleKahootBtn.addEventListener('click', () => switchMode(true));
    switchMode(false);

    if (addBtn) addBtn.addEventListener('click', () => { addQuestion(); });

    function addQuestion(data) {
        const q = data || { question: '', answers: ['', '', '', ''], correct: 0 };
        questions.push(q);
        renderAllQuestions();
    }

    function removeQuestion(idx) {
        questions.splice(idx, 1);
        renderAllQuestions();
    }

    function renderAllQuestions() {
        if (!listEl) return;
        listEl.innerHTML = '';
        questions.forEach((q, idx) => renderQuestion(q, idx));
        serializeToHidden();
    }

    function renderQuestion(q, idx) {
        const card = document.createElement('div');
        card.className = 'kahoot-q-card';
        card.dataset.idx = idx;

        const qRow = document.createElement('div');
        qRow.className = 'flex items-start gap-3 mb-4';
        qRow.innerHTML = `
            <span class="mt-1 flex-shrink-0 w-7 h-7 rounded-lg bg-violet-500/20 border border-violet-500/30 flex items-center justify-center text-violet-300 text-xs font-bold">${idx + 1}</span>
            <input type="text" class="flex-1 bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-white text-sm focus:outline-none focus:border-violet-500/60 transition-colors" placeholder="Escribe la pregunta..." value="${escHtml(q.question)}" data-q-idx="${idx}" />
            <button type="button" class="mt-1 flex-shrink-0 w-7 h-7 rounded-lg bg-red-500/10 hover:bg-red-500/20 border border-red-500/20 flex items-center justify-center text-red-400 transition-colors cursor-pointer" data-remove="${idx}" title="Eliminar pregunta">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>`;

        qRow.querySelector('input').addEventListener('input', function() {
            questions[idx].question = this.value;
            serializeToHidden();
        });
        qRow.querySelector('[data-remove]').addEventListener('click', function() {
            removeQuestion(parseInt(this.dataset.remove));
        });

        const aGrid = document.createElement('div');
        aGrid.className = 'grid grid-cols-1 sm:grid-cols-2 gap-2';

        q.answers.forEach((ans, aIdx) => {
            const aWrap = document.createElement('div');
            aWrap.className = `kahoot-answer-btn answer-color-${aIdx}` + (q.correct === aIdx ? ' correct' : '');
            aWrap.style.position = 'relative';
            aWrap.innerHTML = `
                <span class="answer-dot mt-0.5 flex-shrink-0"></span>
                <span class="text-xs font-bold flex-shrink-0" style="color:${colors[aIdx]}">${letters[aIdx]}</span>
                <input type="text" placeholder="Respuesta ${letters[aIdx]}..." value="${escHtml(ans)}" data-a-idx="${aIdx}" data-q-idx="${idx}" />
                <input type="radio" class="kahoot-correct-radio" name="correct-${idx}" value="${aIdx}" ${q.correct === aIdx ? 'checked' : ''} />
                <span class="ml-auto flex-shrink-0" title="Marcar como correcta">
                    ${q.correct === aIdx
                        ? '<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>'
                        : '<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white/20 hover:text-white/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>'
                    }
                </span>`;

            aWrap.querySelector('input[type="text"]').addEventListener('input', function() {
                questions[idx].answers[aIdx] = this.value;
                serializeToHidden();
            });
            aWrap.addEventListener('click', function(e) {
                if (e.target.tagName === 'INPUT' && e.target.type === 'text') return;
                questions[idx].correct = aIdx;
                renderAllQuestions();
            });

            aGrid.appendChild(aWrap);
        });

        card.appendChild(qRow);
        card.appendChild(aGrid);
        listEl.appendChild(card);
    }

    if (pdfInput) {
        pdfInput.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;
            pdfLabelText.textContent = file.name.length > 22 ? file.name.substring(0, 20) + '...' : file.name;
            const reader = new FileReader();
            reader.onload = function(e) {
                selectedPdfBase64 = e.target.result.split(',')[1];
            };
            reader.readAsDataURL(file);
        });
    }

    if (generateBtn) {
        generateBtn.addEventListener('click', async function() {
            if (!selectedPdfBase64) { showError('Selecciona un archivo PDF primero.'); return; }

            setLoading(true);
            hideError();

            try {
                const resp = await fetch('/api/events/generate-kahoot', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrf() },
                    body: JSON.stringify({
                        pdf_base64: selectedPdfBase64,
                        num_questions: parseInt(numQInput.value, 10) || 10,
                    }),
                });

                const data = await resp.json();
                if (!resp.ok || data.error) {
                    showError(data.error || 'Error de servidor. Revisa el PDF.');
                    return;
                }

                questions = data.questions.map(q => ({
                    question: q.question || '',
                    answers: (q.answers || ['','','','']).slice(0, 4).concat(['','','','']).slice(0, 4),
                    correct: typeof q.correct === 'number' ? q.correct : 0,
                }));
                renderAllQuestions();
            } catch(err) {
                showError('Error de red: ' + err.message);
            } finally {
                setLoading(false);
            }
        });
    }

    if (mainForm) {
        mainForm.addEventListener('submit', function() {
            if (kahootEnabled) {
                serializeToHidden();
            }
        });
    }

    function serializeToHidden() {
        if (kahootQsInput) kahootQsInput.value = JSON.stringify(questions);
    }

    function setLoading(on) {
        if (!generateBtn) return;
        generateBtn.disabled = on;
        if (spinner) spinner.classList.toggle('hidden', !on);
        if (generateIcon) generateIcon.classList.toggle('hidden', on);
        if (generateText) generateText.textContent = on ? 'Generando...' : 'Generar con IA';
    }
    function showError(msg) {
        if (!errorBox) return;
        errorBox.textContent = msg;
        errorBox.classList.remove('hidden');
    }
    function hideError() { if (errorBox) errorBox.classList.add('hidden'); }
    function escHtml(str) { return String(str).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
    function getCsrf() { return document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value || ''; }
})();
</script>
