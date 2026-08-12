import './bootstrap';
import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;

document.addEventListener('DOMContentLoaded', () => {
    initToasts();
    initDeleteConfirmation();
    initSecurePlayer();
    initLessonForms();
    initReveal();
});

function initToasts() {
    const showToasts = () => {
        document.querySelectorAll('[data-ed-toast]').forEach((element) => {
            bootstrap.Toast.getOrCreateInstance(element, {
                animation: true,
                autohide: true,
                delay: 5500,
            }).show();
        });
    };

    // Wait until the page loader is gone so toasts are visible.
    const loader = document.getElementById('ed-page-loader');
    if (!loader) {
        showToasts();
        return;
    }

    const observer = new MutationObserver(() => {
        if (!document.getElementById('ed-page-loader')) {
            observer.disconnect();
            showToasts();
        }
    });

    observer.observe(document.body, { childList: true, subtree: true });
    setTimeout(() => {
        observer.disconnect();
        showToasts();
    }, 11000);
}

function initDeleteConfirmation() {
    const confirmationElement = document.getElementById('ed-delete-confirmation');
    if (!confirmationElement) {
        return;
    }

    const confirmationToast = bootstrap.Toast.getOrCreateInstance(confirmationElement, { autohide: false });
    const confirmButton = confirmationElement.querySelector('[data-confirm-delete]');
    let pendingForm = null;

    document.querySelectorAll('form').forEach((form) => {
        const isDeleteForm = form.matches('[data-confirm-delete]')
            || form.querySelector('input[name="_method"][value="DELETE"]');

        if (!isDeleteForm) {
            return;
        }

        form.addEventListener('submit', (event) => {
            if (form.dataset.confirmed === 'true') {
                return;
            }

            event.preventDefault();
            pendingForm = form;
            confirmationToast.show();
        });
    });

    confirmButton?.addEventListener('click', () => {
        if (!pendingForm) {
            return;
        }

        pendingForm.dataset.confirmed = 'true';
        confirmationToast.hide();
        pendingForm.requestSubmit();
    });

    confirmationElement.addEventListener('hidden.bs.toast', () => {
        if (pendingForm?.dataset.confirmed !== 'true') {
            pendingForm = null;
        }
    });
}

function initReveal() {
    const items = document.querySelectorAll('.ed-reveal');
    if (!items.length) {
        return;
    }

    if (!('IntersectionObserver' in window)) {
        items.forEach((item) => item.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });

    items.forEach((item) => observer.observe(item));
}

function initSecurePlayer() {
    const player = document.querySelector('.secure-player');
    if (!player) {
        return;
    }

    player.addEventListener('contextmenu', (event) => event.preventDefault());

    const watermark = player.querySelector('.player-watermark');
    if (!watermark) {
        return;
    }

    const move = () => {
        const top = 10 + Math.random() * 70;
        const side = Math.random() > 0.5 ? 'left' : 'right';
        const offset = 5 + Math.random() * 55;
        watermark.style.top = `${top}%`;
        watermark.style.left = side === 'left' ? `${offset}%` : 'auto';
        watermark.style.right = side === 'right' ? `${offset}%` : 'auto';
    };

    move();
    setInterval(move, 8000);
}

function initLessonForms() {
    document.querySelectorAll('[data-lesson-form]').forEach((form) => {
        const typeSelect = form.querySelector('[data-lesson-type]');
        const questionsList = form.querySelector('[data-questions-list]');
        const addQuestionBtn = form.querySelector('[data-add-question]');
        const template = document.getElementById('lesson-question-template');

        if (!typeSelect) {
            return;
        }

        const syncPanels = () => {
            const type = typeSelect.value;
            form.querySelectorAll('[data-panel]').forEach((panel) => {
                panel.classList.toggle('d-none', panel.dataset.panel !== type);
            });

            const article = form.querySelector('[data-article-content]');
            const fileInput = form.querySelector('[data-file-input]');
            const quizFields = form.querySelectorAll('[data-quiz-field]');

            if (article) {
                article.disabled = type !== 'article';
            }
            if (fileInput) {
                fileInput.disabled = type !== 'file';
                if (type !== 'file') {
                    fileInput.value = '';
                }
            }
            quizFields.forEach((field) => {
                field.disabled = type !== 'quiz';
            });

            if (type === 'quiz' && questionsList && questionsList.children.length === 0) {
                addQuestion();
            }
        };

        const reindexQuestions = () => {
            if (!questionsList) {
                return;
            }

            [...questionsList.children].forEach((item, index) => {
                const label = item.querySelector('[data-question-label]');
                if (label) {
                    const base = label.dataset.baseLabel || 'Question';
                    label.dataset.baseLabel = base;
                    label.textContent = `${base} ${index + 1}`;
                }

                const text = item.querySelector('[data-q-text]');
                const options = item.querySelectorAll('[data-q-option]');
                const correct = item.querySelector('[data-q-correct]');

                if (text) {
                    text.name = `questions[${index}][question]`;
                    text.required = typeSelect.value === 'quiz';
                }
                options.forEach((option) => {
                    option.name = `questions[${index}][options][]`;
                });
                if (correct) {
                    correct.name = `questions[${index}][correct_index]`;
                }
            });
        };

        const addQuestion = () => {
            if (!template || !questionsList) {
                return;
            }

            const node = template.content.cloneNode(true);
            const item = node.querySelector('[data-question-item]');
            const label = item.querySelector('[data-question-label]');
            label.dataset.baseLabel = label.textContent.trim() || 'Question';

            item.querySelector('[data-remove-question]')?.addEventListener('click', () => {
                if (questionsList.children.length <= 1 && typeSelect.value === 'quiz') {
                    return;
                }
                item.remove();
                reindexQuestions();
            });

            questionsList.appendChild(item);
            reindexQuestions();
        };

        addQuestionBtn?.addEventListener('click', addQuestion);
        typeSelect.addEventListener('change', syncPanels);
        syncPanels();
    });
}
