import './bootstrap';
import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;

document.addEventListener('DOMContentLoaded', () => {
    initToasts();
    initDeleteConfirmation();
    initSecurePlayer();
    initLessonForms();
    initBankQuestionForms();
    initExamRuleForm();
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

function initBankQuestionForms() {
    document.querySelectorAll('[data-question-form]').forEach((form) => {
        const typeSelect = form.querySelector('[data-question-type]');
        if (!typeSelect) {
            return;
        }

        const choicesList = form.querySelector('[data-choices-list]');
        const matchesList = form.querySelector('[data-matches-list]');

        const reindexChoices = () => {
            if (!choicesList) return;
            [...choicesList.children].forEach((row, index) => {
                const text = row.querySelector('[data-choice-text]');
                const correct = row.querySelector('[data-choice-correct]');
                const image = row.querySelector('[data-choice-image]');
                if (text) text.name = `choices[${index}][text]`;
                if (correct) correct.name = `choices[${index}][is_correct]`;
                if (image) image.name = `choices[${index}][image]`;
            });
        };

        const reindexMatches = () => {
            if (!matchesList) return;
            [...matchesList.children].forEach((row, index) => {
                const prompt = row.querySelector('[data-match-prompt]');
                const answer = row.querySelector('[data-match-answer]');
                if (prompt) prompt.name = `matches[${index}][prompt]`;
                if (answer) answer.name = `matches[${index}][match]`;
            });
        };

        const bindChoiceRow = (row) => {
            const correct = row.querySelector('[data-choice-correct]');
            correct?.addEventListener('change', () => {
                if (!correct.checked) {
                    return;
                }
                choicesList.querySelectorAll('[data-choice-correct]').forEach((other) => {
                    if (other !== correct) other.checked = false;
                });
            });
            row.querySelector('[data-remove-choice]')?.addEventListener('click', () => {
                if (choicesList.children.length <= 2) return;
                row.remove();
                reindexChoices();
            });
        };

        const bindMatchRow = (row) => {
            row.querySelector('[data-remove-match]')?.addEventListener('click', () => {
                if (matchesList.children.length <= 2) return;
                row.remove();
                reindexMatches();
            });
        };

        choicesList?.querySelectorAll('[data-choice-row]').forEach(bindChoiceRow);
        matchesList?.querySelectorAll('[data-match-row]').forEach(bindMatchRow);

        form.querySelector('[data-add-choice]')?.addEventListener('click', () => {
            const template = document.getElementById('bank-choice-row-template');
            if (!template || !choicesList) return;
            const node = template.content.cloneNode(true);
            const row = node.querySelector('[data-choice-row]');
            bindChoiceRow(row);
            choicesList.appendChild(row);
            reindexChoices();
        });

        form.querySelector('[data-add-match]')?.addEventListener('click', () => {
            const template = document.getElementById('bank-match-row-template');
            if (!template || !matchesList) return;
            const node = template.content.cloneNode(true);
            const row = node.querySelector('[data-match-row]');
            bindMatchRow(row);
            matchesList.appendChild(row);
            reindexMatches();
        });

        const syncPanels = () => {
            const type = typeSelect.value;
            form.querySelectorAll('[data-panel]').forEach((el) => {
                el.classList.toggle('d-none', el.dataset.panel !== type);
            });

            const hint = form.querySelector('[data-question-type-hint]');
            const selectedOption = typeSelect.options[typeSelect.selectedIndex];
            if (hint && selectedOption) {
                hint.textContent = selectedOption.dataset.hint || '';
            }
        };

        typeSelect.addEventListener('change', syncPanels);
        reindexChoices();
        reindexMatches();
        syncPanels();
    });
}

function initExamRuleForm() {
    document.querySelectorAll('[data-exam-form]').forEach((form) => {
        const panelsContainer = form.querySelector('[data-subject-panels]');
        if (!panelsContainer) {
            return;
        }

        const reindexRules = () => {
            let index = 0;
            panelsContainer.querySelectorAll('[data-type-row]').forEach((row) => {
                const subject = row.querySelector('[data-rule-subject]');
                const type = row.querySelector('[data-rule-type]');
                const count = row.querySelector('[data-rule-count]');
                if (subject) subject.name = `rules[${index}][subject_id]`;
                if (type) type.name = `rules[${index}][type]`;
                if (count) count.name = `rules[${index}][count]`;
                index++;
            });
        };

        const setRowDisabled = (row, disabled) => {
            row.querySelectorAll('input, select').forEach((field) => {
                field.disabled = disabled;
            });
        };

        const bindTypeRow = (row, panel) => {
            row.querySelector('[data-remove-type-row]')?.addEventListener('click', () => {
                const rowsList = panel.querySelector('[data-type-rows-list]');
                if (rowsList.children.length <= 1) return;
                row.remove();
                reindexRules();
            });
            setRowDisabled(row, panel.classList.contains('d-none'));
        };

        panelsContainer.querySelectorAll('[data-subject-panel]').forEach((panel) => {
            panel.querySelectorAll('[data-type-row]').forEach((row) => bindTypeRow(row, panel));

            panel.querySelector('[data-add-type-row]')?.addEventListener('click', () => {
                const template = document.getElementById('exam-type-row-template');
                const rowsList = panel.querySelector('[data-type-rows-list]');
                if (!template || !rowsList) return;

                const node = template.content.cloneNode(true);
                const row = node.querySelector('[data-type-row]');
                const subjectInput = row.querySelector('[data-rule-subject]');
                if (subjectInput) subjectInput.value = panel.id.replace('subject-panel-', '');

                bindTypeRow(row, panel);
                rowsList.appendChild(row);
                reindexRules();
            });
        });

        form.querySelectorAll('[data-subject-toggle]').forEach((checkbox) => {
            const panel = document.getElementById(checkbox.dataset.subjectTarget);
            if (!panel) return;

            checkbox.addEventListener('change', () => {
                panel.classList.toggle('d-none', !checkbox.checked);
                panel.querySelectorAll('[data-type-row]').forEach((row) => setRowDisabled(row, !checkbox.checked));
                reindexRules();
            });
        });

        reindexRules();
    });
}
