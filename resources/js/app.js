import './bootstrap';

// Header menus: [data-toggle="<id>"] buttons show/hide the element with that id.
// Without JavaScript, dropdowns still open on hover/focus via CSS and the
// mobile menu falls back to a plain list.
document.addEventListener('DOMContentLoaded', () => {
    const toggles = [...document.querySelectorAll('[data-toggle]')];

    const setOpen = (button, open) => {
        const target = document.getElementById(button.dataset.toggle);
        if (!target) return;
        button.setAttribute('aria-expanded', String(open));
        target.classList.toggle('hidden', !open);
        if (open && target.dataset.focus) {
            target.querySelector(target.dataset.focus)?.focus();
        }
    };

    const closeAll = (except = null) => {
        toggles
            .filter((b) => b !== except && b.dataset.group === 'dropdown' && b.getAttribute('aria-expanded') === 'true')
            .forEach((b) => setOpen(b, false));
    };

    toggles.forEach((button) => {
        button.addEventListener('click', (event) => {
            event.stopPropagation();
            const open = button.getAttribute('aria-expanded') !== 'true';
            if (button.dataset.group === 'dropdown') closeAll(button);
            setOpen(button, open);
        });
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-dropdown]')) closeAll();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        const open = toggles.find((b) => b.dataset.group === 'dropdown' && b.getAttribute('aria-expanded') === 'true');
        closeAll();
        open?.focus();
    });
});
