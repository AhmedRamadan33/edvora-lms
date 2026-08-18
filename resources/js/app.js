import './bootstrap';
import * as bootstrap from 'bootstrap';
import $ from './jquery-global';
import select2 from 'select2';

select2();

window.bootstrap = bootstrap;

document.addEventListener('DOMContentLoaded', () => {
    initToasts();
    initFormConfirmations();
    initSecurePlayer();
    initLessonForms();
    initBankQuestionForms();
    initExamRuleForm();
    initExamAttempt();
    initPayoutForm();
    initSecretToggles();
    initReveal();
    initSelect2();
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

function initFormConfirmations() {
    const confirmationElement = document.getElementById('ed-confirm-toast');
    if (!confirmationElement) {
        return;
    }

    const confirmationToast = bootstrap.Toast.getOrCreateInstance(confirmationElement, { autohide: false });
    const acceptButton = confirmationElement.querySelector('[data-confirm-accept]');
    const bodyElement = confirmationElement.querySelector('[data-confirm-body]');
    const defaultBody = bodyElement?.textContent ?? '';
    const defaultLabel = acceptButton?.textContent ?? '';
    const genericLabel = acceptButton?.dataset.genericLabel || defaultLabel;
    let pendingForm = null;

    document.querySelectorAll('form').forEach((form) => {
        const isDeleteForm = form.matches('[data-confirm-delete]')
            || form.querySelector('input[name="_method"][value="DELETE"]');
        const isMessageForm = form.matches('[data-confirm-message]');

        if (!isDeleteForm && !isMessageForm) {
            return;
        }

        form.addEventListener('submit', (event) => {
            if (form.dataset.confirmed === 'true') {
                return;
            }

            event.preventDefault();
            pendingForm = form;

            if (bodyElement) {
                bodyElement.textContent = form.dataset.confirmMessage || defaultBody;
            }
            if (acceptButton) {
                acceptButton.textContent = form.dataset.confirmLabel || (isDeleteForm ? defaultLabel : genericLabel);
                acceptButton.classList.toggle('btn-danger', isDeleteForm);
                acceptButton.classList.toggle('btn-primary', !isDeleteForm);
            }

            confirmationToast.show();
        });
    });

    acceptButton?.addEventListener('click', () => {
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
        $(typeSelect).on('change', syncPanels);
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

        $(typeSelect).on('change', syncPanels);
        reindexChoices();
        reindexMatches();
        syncPanels();
    });
}

function initSecretToggles() {
    document.querySelectorAll('[data-toggle-secret]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = button.parentElement.querySelector('[data-secret-input]');
            const icon = button.querySelector('i');
            if (!input) {
                return;
            }

            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            icon.classList.toggle('bi-eye', !isPassword);
            icon.classList.toggle('bi-eye-slash', isPassword);
        });
    });
}

function initPayoutForm() {
    const form = document.querySelector('[data-payout-form]');
    if (!form) {
        return;
    }

    const methodSelect = form.querySelector('[data-payout-method]');
    if (!methodSelect) {
        return;
    }

    const syncPanels = () => {
        const method = methodSelect.value;
        form.querySelectorAll('[data-panel]').forEach((panel) => {
            const isActive = panel.dataset.panel === method;
            panel.classList.toggle('d-none', !isActive);
            panel.querySelectorAll('input').forEach((field) => {
                field.disabled = !isActive;
                field.required = isActive;
            });
        });
    };

    $(methodSelect).on('change', syncPanels);
    syncPanels();
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

function initSelect2() {
    const dir = document.documentElement.getAttribute('dir') || 'ltr';

    const apply = (select) => {
        if (select.classList.contains('select2-hidden-accessible') || select.offsetParent === null) {
            return;
        }

        $(select).select2({
            theme: 'bootstrap-5',
            width: 'element',
            dir,
        });

        const container = select.nextElementSibling;
        if (container && container.classList.contains('select2-container')) {
            select.classList.forEach((cls) => {
                if (/^m[tbsexy]?-(0|1|2|3|4|5|auto)$/.test(cls)) {
                    container.classList.add(cls);
                }
            });
        }

        select.addEventListener('invalid', () => {
            $(select).select2('open');
        });
    };

    document.querySelectorAll('select').forEach(apply);

    const observer = new MutationObserver(() => {
        document.querySelectorAll('select:not(.select2-hidden-accessible)').forEach(apply);
    });

    observer.observe(document.body, {
        attributes: true,
        attributeFilter: ['class'],
        childList: true,
        subtree: true,
    });
}

function initExamAttempt() {
    const form = document.querySelector('[data-exam-attempt]');
    if (!form) {
        return;
    }

    const panels = [...form.querySelectorAll('[data-question-panel]')];
    const navButtons = [...form.querySelectorAll('[data-question-nav] [data-nav-to]')];
    const progressCount = form.querySelector('[data-progress-count]');
    const nextButton = form.querySelector('[data-nav-next]');
    const submitButton = form.querySelector('[data-exam-submit]');
    let current = 0;

    const isAnswered = (index) => {
        const panel = panels[index];
        if (!panel) return false;
        return [...panel.querySelectorAll('[data-answer-input]')].some((field) => {
            if (field.type === 'radio') {
                return panel.querySelector(`input[name="${field.name}"]:checked`) !== null;
            }
            return field.value.trim() !== '';
        });
    };

    const updateProgress = () => {
        const answeredCount = panels.filter((_, index) => isAnswered(index)).length;
        if (progressCount) {
            progressCount.textContent = String(answeredCount);
        }
        navButtons.forEach((button, index) => {
            button.classList.remove('btn-outline-secondary', 'btn-success', 'btn-primary');
            if (index === current) {
                button.classList.add('btn-primary');
            } else if (isAnswered(index)) {
                button.classList.add('btn-success');
            } else {
                button.classList.add('btn-outline-secondary');
            }
        });
    };

    const updateNavButtons = () => {
        const isLast = current === panels.length - 1;
        nextButton?.classList.toggle('d-none', isLast);
        submitButton?.classList.toggle('d-none', !isLast);
    };

    const showQuestion = (index) => {
        if (index < 0 || index >= panels.length) return;
        panels[current]?.classList.add('d-none');
        current = index;
        panels[current]?.classList.remove('d-none');
        updateProgress();
        updateNavButtons();
    };

    navButtons.forEach((button, index) => {
        button.addEventListener('click', () => showQuestion(index));
    });

    form.querySelector('[data-nav-prev]')?.addEventListener('click', () => showQuestion(current - 1));
    form.querySelector('[data-nav-next]')?.addEventListener('click', () => showQuestion(current + 1));

    form.querySelectorAll('[data-answer-input]').forEach((field) => {
        $(field).on('change input', updateProgress);
    });

    updateProgress();
    updateNavButtons();

    const timerEl = form.querySelector('[data-exam-timer]');
    const durationMinutes = parseInt(form.dataset.durationMinutes, 10);

    if (timerEl && durationMinutes) {
        const deadline = new Date(form.dataset.startedAt).getTime() + durationMinutes * 60000;

        const tick = () => {
            const remainingMs = deadline - Date.now();

            if (remainingMs <= 0) {
                timerEl.textContent = '00:00';
                form.dataset.confirmed = 'true';
                form.requestSubmit();
                return;
            }

            const totalSeconds = Math.floor(remainingMs / 1000);
            const minutes = Math.floor(totalSeconds / 60);
            const seconds = totalSeconds % 60;
            timerEl.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        };

        tick();
        setInterval(tick, 1000);
    }
}
