@extends('layouts.admin')

@section('title', 'AI Chat Assistant')

@section('content')
<div class="flex items-center justify-between gap-4 mb-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
            <i class="bi bi-chat-left-dots text-brand-red"></i> AI Chat Assistant
        </h1>
        <p class="text-sm text-gray-500 mt-1">Ask sales, stock, customers, categories — in English / Sinhala / Singlish.</p>
    </div>
    <button id="clear-btn" type="button" class="flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-brand-red transition-colors px-3 py-1.5 rounded-lg hover:bg-gray-100">
        <i class="bi bi-trash3"></i> Clear
    </button>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

    {{-- Main Chat --}}
    <div class="lg:col-span-3 flex flex-col" style="min-height: 520px;">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col flex-1 overflow-hidden">
            {{-- Toolbar --}}
            <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between bg-gray-50/50 shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-brand-red flex items-center justify-center">
                        <i class="bi bi-robot text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900">PrintWorks AI</p>
                        <p id="status" class="text-[11px] text-gray-400">Ready</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span id="confidence-badge" class="hidden text-[11px] font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700"></span>
                    <span id="intent-badge" class="hidden text-[11px] font-semibold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700"></span>
                </div>
            </div>

            {{-- Messages --}}
            <div id="chat-messages" class="flex-1 overflow-y-auto px-5 py-4 space-y-5" style="min-height: 360px; max-height: 520px;">
                {{-- Welcome message --}}
                <div class="flex gap-3">
                    <div class="w-7 h-7 rounded-xl bg-brand-red flex items-center justify-center shrink-0 mt-0.5">
                        <i class="bi bi-robot text-white text-xs"></i>
                    </div>
                    <div class="flex flex-col gap-1 max-w-[85%]">
                        <div class="bg-gray-50 border border-gray-100 rounded-2xl rounded-tl-sm px-4 py-3 text-sm text-gray-800 leading-relaxed">
                            Hi! I can answer business questions about PrintWorks — sales, stock, customers, categories, campaigns.
                            Try: <span class="font-semibold text-brand-red">"ada sales kohomda?"</span> or <span class="font-semibold text-brand-red">"show me top products this month"</span>.
                        </div>
                        <span class="text-[10px] text-gray-400 pl-1">Just now</span>
                    </div>
                </div>
            </div>

            {{-- Input --}}
            <div class="px-5 py-4 border-t border-gray-100 shrink-0 bg-white">
                <div class="flex gap-3 items-end">
                    <div class="flex-1">
                        <textarea
                            id="chat-input"
                            rows="2"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-red/20 focus:border-brand-red transition-all resize-none leading-relaxed"
                            placeholder="Ask something in English, Sinhala, or Singlish…"
                        ></textarea>
                    </div>
                    <button
                        id="send-btn"
                        type="button"
                        class="bg-brand-red text-white px-5 h-[52px] rounded-xl font-semibold hover:bg-red-dark transition-colors text-sm flex items-center gap-2 shrink-0 shadow-sm shadow-brand-red/20"
                    >
                        <i class="bi bi-send-fill text-sm"></i> Send
                    </button>
                </div>
                <p class="text-[11px] text-gray-400 mt-2">Press <kbd class="bg-gray-100 border border-gray-200 rounded px-1 py-0.5 font-mono text-[10px]">Enter</kbd> to send · <kbd class="bg-gray-100 border border-gray-200 rounded px-1 py-0.5 font-mono text-[10px]">Shift+Enter</kbd> for newline · All numbers come from real data.</p>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-5">
        {{-- Quick Suggestions --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <h2 class="font-semibold text-gray-900 flex items-center gap-2 mb-3 text-sm">
                <i class="bi bi-lightning-charge-fill text-brand-red"></i> Quick Questions
            </h2>
            <div class="flex flex-col gap-1.5">
                @php
                    $suggestions = [
                        ['text' => 'ada sales kohomda', 'icon' => 'bi-cash-stack'],
                        ['text' => 'me mase top products', 'icon' => 'bi-trophy'],
                        ['text' => 'stock adu wenna yanna items', 'icon' => 'bi-exclamation-triangle'],
                        ['text' => 'aye ganna puluwan customers kawda', 'icon' => 'bi-people'],
                        ['text' => 'what is the sales forecast', 'icon' => 'bi-graph-up-arrow'],
                        ['text' => 'business summary today', 'icon' => 'bi-clipboard-data'],
                        ['text' => 'how to get more customers', 'icon' => 'bi-lightbulb'],
                        ['text' => 'last week campaign impact', 'icon' => 'bi-megaphone'],
                    ];
                @endphp
                @foreach($suggestions as $s)
                    <button
                        type="button"
                        class="text-left bg-gray-50 hover:bg-red-50 hover:border-brand-red/20 border border-transparent rounded-lg px-3 py-2 text-xs transition-colors group flex items-center gap-2"
                        onclick="useSuggestion(@json($s['text']))"
                    >
                        <i class="bi {{ $s['icon'] }} text-gray-400 group-hover:text-brand-red transition-colors w-4 text-center"></i>
                        <span class="text-gray-700 group-hover:text-gray-900">{{ $s['text'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- About --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <h2 class="font-semibold text-gray-900 flex items-center gap-2 mb-3 text-sm">
                <i class="bi bi-shield-check-fill text-brand-red"></i> How it works
            </h2>
            <ul class="space-y-2 text-xs text-gray-600">
                <li class="flex items-start gap-2"><i class="bi bi-check-circle-fill text-green-500 mt-0.5 shrink-0"></i> Gemini classifies your intent</li>
                <li class="flex items-start gap-2"><i class="bi bi-check-circle-fill text-green-500 mt-0.5 shrink-0"></i> Real numbers from your database</li>
                <li class="flex items-start gap-2"><i class="bi bi-check-circle-fill text-green-500 mt-0.5 shrink-0"></i> Sinhala, Singlish, English all work</li>
                <li class="flex items-start gap-2"><i class="bi bi-check-circle-fill text-green-500 mt-0.5 shrink-0"></i> No hallucination — grounded only</li>
            </ul>
        </div>
    </div>
</div>

<script>
    const chatMessagesEl = document.getElementById('chat-messages');
    const chatInputEl = document.getElementById('chat-input');
    const sendBtn = document.getElementById('send-btn');
    const statusEl = document.getElementById('status');
    const confidenceBadge = document.getElementById('confidence-badge');
    const intentBadge = document.getElementById('intent-badge');
    const clearBtn = document.getElementById('clear-btn');

    const endpoint = @json(route('admin.ai.chat.api'));
    const CSRF_TOKEN = @json(csrf_token());

    function useSuggestion(text) {
        chatInputEl.value = text;
        chatInputEl.focus();
    }

    function escapeHtml(s) {
        return String(s ?? '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    // Basic markdown to HTML (bold, italic, bullet lists, numbered lists)
    function renderMarkdown(text) {
        let html = escapeHtml(text);
        html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        html = html.replace(/\*(.+?)\*/g, '<em>$1</em>');
        // numbered lists
        html = html.replace(/^(\d+)\.\s+(.+)$/gm, '<li class="ml-4 list-decimal">$2</li>');
        // bullet lists
        html = html.replace(/^[-•]\s+(.+)$/gm, '<li class="ml-4 list-disc">$1</li>');
        // wrap consecutive <li> items
        html = html.replace(/((<li.*<\/li>\n?)+)/g, '<ul class="space-y-0.5 my-1 text-sm">$1</ul>');
        // line breaks
        html = html.replace(/\n/g, '<br>');
        return html;
    }

    function nowLabel() {
        return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function appendMessage({ role, text, recommendations, table_data, metrics, error, intent, confidence }) {
        const isUser = role === 'user';
        const wrap = document.createElement('div');
        wrap.className = 'flex gap-3 ' + (isUser ? 'justify-end' : 'justify-start');

        if (!isUser) {
            const avatar = document.createElement('div');
            avatar.className = 'w-7 h-7 rounded-xl bg-brand-red flex items-center justify-center shrink-0 mt-0.5';
            avatar.innerHTML = '<i class="bi bi-robot text-white text-xs"></i>';
            wrap.appendChild(avatar);
        }

        const colWrap = document.createElement('div');
        colWrap.className = 'flex flex-col gap-1 ' + (isUser ? 'items-end' : 'items-start') + ' max-w-[85%]';

        const bubble = document.createElement('div');
        bubble.className = isUser
            ? 'bg-brand-red text-white rounded-2xl rounded-tr-sm px-4 py-3 text-sm leading-relaxed'
            : 'bg-gray-50 border border-gray-100 rounded-2xl rounded-tl-sm px-4 py-3 text-sm text-gray-800 leading-relaxed';

        bubble.innerHTML = `<div>${renderMarkdown(text || '')}</div>`;

        if (!isUser && error) {
            const errDiv = document.createElement('div');
            errDiv.className = 'mt-3 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 flex items-start gap-2';
            errDiv.innerHTML = '<i class="bi bi-exclamation-triangle-fill mt-0.5"></i><span>' + escapeHtml(error) + '</span>';
            bubble.appendChild(errDiv);
        }

        if (!isUser && recommendations && recommendations.length) {
            const recDiv = document.createElement('div');
            recDiv.className = 'mt-3 pt-3 border-t border-gray-200';
            recDiv.innerHTML = '<p class="text-[11px] font-black text-gray-500 uppercase tracking-wider mb-2">Recommendations</p>';
            const ul = document.createElement('ul');
            ul.className = 'space-y-1.5';
            recommendations.slice(0, 6).forEach(r => {
                const li = document.createElement('li');
                li.className = 'text-xs text-gray-700 flex items-start gap-1.5';
                li.innerHTML = '<i class="bi bi-check-circle-fill text-green-500 mt-0.5 shrink-0"></i><span>' + escapeHtml(r) + '</span>';
                ul.appendChild(li);
            });
            recDiv.appendChild(ul);
            bubble.appendChild(recDiv);
        }

        if (!isUser && table_data && Array.isArray(table_data) && table_data.length) {
            const cols = Object.keys(table_data[0]).slice(0, 7);
            const tableWrap = document.createElement('div');
            tableWrap.className = 'mt-3 overflow-x-auto rounded-xl border border-gray-200';
            const table = document.createElement('table');
            table.className = 'min-w-full text-xs';
            const thead = document.createElement('thead');
            thead.innerHTML = '<tr class="bg-gray-50">' + cols.map(c => `<th class="px-3 py-2 text-left font-semibold text-gray-600 whitespace-nowrap">${escapeHtml(c)}</th>`).join('') + '</tr>';
            const tbody = document.createElement('tbody');
            table_data.slice(0, 10).forEach((row, idx) => {
                const tr = document.createElement('tr');
                tr.className = idx % 2 === 0 ? 'bg-white' : 'bg-gray-50/50';
                tr.innerHTML = cols.map(c => `<td class="px-3 py-2 text-gray-700 whitespace-nowrap">${escapeHtml(String(row[c] ?? ''))}</td>`).join('');
                tbody.appendChild(tr);
            });
            table.appendChild(thead);
            table.appendChild(tbody);
            tableWrap.appendChild(table);
            bubble.appendChild(tableWrap);
        }

        colWrap.appendChild(bubble);

        // Timestamp
        const ts = document.createElement('span');
        ts.className = 'text-[10px] text-gray-400 ' + (isUser ? 'pr-1' : 'pl-1');
        ts.textContent = nowLabel();
        colWrap.appendChild(ts);

        wrap.appendChild(colWrap);

        if (isUser) {
            const avatarU = document.createElement('div');
            avatarU.className = 'w-7 h-7 rounded-xl bg-gray-700 flex items-center justify-center shrink-0 mt-0.5';
            avatarU.innerHTML = '<i class="bi bi-person-fill text-white text-xs"></i>';
            wrap.appendChild(avatarU);
        }

        chatMessagesEl.appendChild(wrap);
        chatMessagesEl.scrollTop = chatMessagesEl.scrollHeight;
    }

    function appendTyping() {
        const div = document.createElement('div');
        div.id = 'typing-indicator';
        div.className = 'flex gap-3';
        div.innerHTML = `
            <div class="w-7 h-7 rounded-xl bg-brand-red flex items-center justify-center shrink-0">
                <i class="bi bi-robot text-white text-xs"></i>
            </div>
            <div class="bg-gray-50 border border-gray-100 rounded-2xl rounded-tl-sm px-4 py-3 flex items-center gap-1.5">
                <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></span>
                <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:.15s"></span>
                <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:.3s"></span>
            </div>
        `;
        chatMessagesEl.appendChild(div);
        chatMessagesEl.scrollTop = chatMessagesEl.scrollHeight;
    }

    function removeTyping() {
        document.getElementById('typing-indicator')?.remove();
    }

    async function sendChat() {
        const msg = chatInputEl.value.trim();
        if (!msg) return;

        appendMessage({ role: 'user', text: msg });
        chatInputEl.value = '';
        chatInputEl.style.height = 'auto';

        sendBtn.disabled = true;
        sendBtn.innerHTML = '<i class="bi bi-hourglass-split animate-spin text-sm"></i> Thinking…';
        statusEl.textContent = 'Processing…';
        intentBadge.classList.add('hidden');
        confidenceBadge.classList.add('hidden');

        appendTyping();

        try {
            const res = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ message: msg })
            });

            const json = await res.json().catch(() => ({}));
            removeTyping();

            if (!res.ok) {
                appendMessage({
                    role: 'assistant',
                    text: json.response_text || 'Request failed.',
                    error: json.error || 'AI request failed.',
                });
                return;
            }

            appendMessage({
                role: 'assistant',
                text: json.response_text || '',
                recommendations: json.recommendations || [],
                table_data: json.table_data || null,
                metrics: json.metrics || null,
                error: json.error || null,
            });

            // Update intent/confidence badges
            if (json.intent && json.intent !== 'unknown') {
                intentBadge.textContent = json.intent.replace(/_/g, ' ');
                intentBadge.classList.remove('hidden');
            }
            if (json.confidence > 0) {
                const pct = Math.round(json.confidence * 100);
                confidenceBadge.textContent = pct + '% confidence';
                confidenceBadge.className = 'text-[11px] font-semibold px-2 py-0.5 rounded-full ' +
                    (pct >= 80 ? 'bg-green-100 text-green-700' : pct >= 60 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700');
                confidenceBadge.classList.remove('hidden');
            }
        } catch {
            removeTyping();
            appendMessage({
                role: 'assistant',
                text: 'AI service unavailable. Please ensure the AI service is running on port 8001.',
                error: 'Connection failed.'
            });
        } finally {
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="bi bi-send-fill text-sm"></i> Send';
            statusEl.textContent = 'Ready';
        }
    }

    sendBtn.addEventListener('click', sendChat);

    chatInputEl.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendChat();
        }
    });

    // Auto-grow textarea
    chatInputEl.addEventListener('input', () => {
        chatInputEl.style.height = 'auto';
        chatInputEl.style.height = Math.min(chatInputEl.scrollHeight, 120) + 'px';
    });

    clearBtn.addEventListener('click', () => {
        chatMessagesEl.innerHTML = '';
        intentBadge.classList.add('hidden');
        confidenceBadge.classList.add('hidden');
    });
</script>
@endsection
