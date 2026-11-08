// Mobile menu toggle
const burger = document.getElementById('burger');
const menu = document.getElementById('menu');
if (burger && menu) {
    burger.addEventListener('click', () => menu.classList.toggle('show'));
}

// AI shopping assistant
(function () {
    const toggle = document.getElementById('ai-toggle');
    const box = document.getElementById('ai-box');
    const closeBtn = document.getElementById('ai-close');
    const form = document.getElementById('ai-form');
    const input = document.getElementById('ai-input');
    const msgs = document.getElementById('ai-msgs');
    if (!toggle || !box || !form || !input || !msgs) return;

    toggle.addEventListener('click', () => {
        box.hidden = !box.hidden;
        if (!box.hidden) input.focus();
    });

    closeBtn.addEventListener('click', () => {
        box.hidden = true;
    });

    function addMessage(html, cls) {
        const d = document.createElement('div');
        d.className = 'msg ' + cls;
        d.innerHTML = html;
        msgs.appendChild(d);
        msgs.scrollTop = msgs.scrollHeight;
        return d;
    }

    form.addEventListener('submit', async (ev) => {
        ev.preventDefault();
        const text = input.value.trim();
        if (!text) return;

        input.value = '';
        addMessage(text, 'user');
        const typing = addMessage('Thinking...', 'bot typing');

        try {
            const res = await fetch(window.NOCAP_AI_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: text })
            });
            const data = await res.json();
            typing.remove();
            addMessage(data.reply || 'No recommendations found.', 'bot');
        } catch (err) {
            typing.remove();
            addMessage('Connection error. Please try again.', 'bot');
        }
    });
})();
