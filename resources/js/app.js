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
    initVideoUpload();
    initBankQuestionForms();
    initExamRuleForm();
    initExamAttempt();
    initPayoutForm();
    initSecretToggles();
    initReveal();
    initSelect2();
    initNotifications();
    initAnnouncementForm();
    initChatBadge();
    initChat();
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
    let pendingAction = null;

    window.edConfirm = (message, label, isDanger, onAccept) => {
        pendingAction = onAccept;

        if (bodyElement) {
            bodyElement.textContent = message || defaultBody;
        }
        if (acceptButton) {
            acceptButton.textContent = label || genericLabel;
            acceptButton.classList.toggle('btn-danger', isDanger);
            acceptButton.classList.toggle('btn-primary', !isDanger);
        }

        confirmationToast.show();
    };

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
            window.edConfirm(form.dataset.confirmMessage, form.dataset.confirmLabel || (isDeleteForm ? defaultLabel : genericLabel), isDeleteForm, () => {
                form.dataset.confirmed = 'true';
                form.requestSubmit();
            });
        });
    });

    acceptButton?.addEventListener('click', () => {
        confirmationToast.hide();
        const action = pendingAction;
        pendingAction = null;
        action?.();
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

            updateSubmitState();
        };

        const updateSubmitState = () => {
            const submitButton = form.querySelector('[data-lesson-submit]');
            const videoIdField = form.querySelector('[data-video-id-field]');
            if (!submitButton || !videoIdField) {
                return;
            }
            submitButton.disabled = typeSelect.value === 'video' && !videoIdField.value;
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

function initVideoUpload() {
    document.querySelectorAll('[data-video-input]').forEach((input) => {
        const form = input.closest('[data-lesson-form]');
        if (!form) {
            return;
        }

        const progressWrap = form.querySelector('[data-video-progress-wrap]');
        const progressBar = form.querySelector('[data-video-progress]');
        const statusEl = form.querySelector('[data-video-status]');
        const videoIdField = form.querySelector('[data-video-id-field]');
        const submitButton = form.querySelector('[data-lesson-submit]');
        const titleField = form.querySelector('input[name="title"]');
        const tokenField = form.querySelector('input[name="_token"]');

        const setStatus = (message) => {
            if (statusEl) {
                statusEl.textContent = message;
            }
        };

        const setProgress = (percent) => {
            if (progressWrap) {
                progressWrap.classList.remove('d-none');
            }
            if (progressBar) {
                progressBar.style.width = `${percent}%`;
            }
        };

        input.addEventListener('change', () => {
            const file = input.files?.[0];
            videoIdField.value = '';
            if (submitButton) {
                submitButton.disabled = true;
            }

            if (!file) {
                return;
            }

            const title = titleField?.value?.trim() || file.name;
            setStatus(form.dataset.uploadingLabel || 'Uploading...');
            setProgress(0);

            fetch(form.dataset.videoCredentialsUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': tokenField ? tokenField.value : '',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ title }),
            })
                .then((response) => response.json())
                .then((credentials) => {
                    if (!credentials.videoId) {
                        setStatus(form.dataset.failedLabel || 'Upload failed.');
                        return;
                    }

                    if (credentials.demo || !credentials.clientPayload) {
                        videoIdField.value = credentials.videoId;
                        setProgress(100);
                        setStatus(form.dataset.readyLabel || 'Video ready.');
                        if (submitButton) {
                            submitButton.disabled = false;
                        }
                        return;
                    }

                    const payload = credentials.clientPayload;
                    const uploadData = new FormData();
                    uploadData.append('key', payload.key);
                    uploadData.append('x-amz-credential', payload['x-amz-credential']);
                    uploadData.append('x-amz-algorithm', payload['x-amz-algorithm']);
                    uploadData.append('x-amz-date', payload['x-amz-date']);
                    uploadData.append('policy', payload.policy);
                    uploadData.append('x-amz-signature', payload['x-amz-signature']);
                    uploadData.append('success_action_status', '201');
                    uploadData.append('success_action_redirect', '');
                    uploadData.append('file', file);

                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', payload.uploadLink);

                    xhr.upload.addEventListener('progress', (event) => {
                        if (event.lengthComputable) {
                            setProgress(Math.round((event.loaded / event.total) * 100));
                        }
                    });

                    xhr.addEventListener('load', () => {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            videoIdField.value = credentials.videoId;
                            setStatus(form.dataset.processingLabel || 'Upload complete, processing...');
                            if (submitButton) {
                                submitButton.disabled = false;
                            }
                        } else {
                            console.error('VdoCipher upload failed', xhr.status, xhr.responseText);
                            setStatus(`${form.dataset.failedLabel || 'Upload failed.'} (${xhr.status})`);
                        }
                    });

                    xhr.addEventListener('error', () => {
                        console.error('VdoCipher upload network error', xhr.status, xhr.responseText);
                        setStatus(form.dataset.failedLabel || 'Upload failed.');
                    });

                    xhr.send(uploadData);
                })
                .catch(() => {
                    setStatus(form.dataset.failedLabel || 'Upload failed.');
                });
        });
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

function initNotifications() {
    const list = document.getElementById('notifList');
    const badge = document.getElementById('notifBadge');
    const toggle = document.getElementById('notifToggle');
    if (!list || !badge || !toggle) {
        return;
    }

    const recentUrl = list.dataset.recentUrl;
    const readUrlTemplate = list.dataset.readUrlTemplate;
    let loaded = false;

    const render = (payload) => {
        const count = payload.unread_count || 0;
        badge.textContent = count > 9 ? '9+' : String(count);
        badge.classList.toggle('d-none', count === 0);

        list.innerHTML = '';

        if (!payload.items || payload.items.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'px-3 py-4 text-center text-muted small';
            empty.textContent = toggle.dataset.emptyLabel || 'No notifications yet.';
            list.appendChild(empty);
            return;
        }

        payload.items.forEach((item) => {
            const link = document.createElement('a');
            link.href = readUrlTemplate.replace('__ID__', item.id);
            link.className = 'ed-notif-item' + (item.read ? '' : ' is-unread');

            const message = document.createElement('div');
            message.className = 'ed-notif-item__message';
            message.textContent = item.message;

            const time = document.createElement('div');
            time.className = 'ed-notif-item__time';
            time.textContent = item.created_at;

            link.appendChild(message);
            link.appendChild(time);
            list.appendChild(link);
        });
    };

    const load = () => {
        fetch(recentUrl, { headers: { Accept: 'application/json' } })
            .then((response) => response.json())
            .then(render)
            .catch(() => {});
    };

    toggle.addEventListener('click', () => {
        if (!loaded) {
            loaded = true;
            load();
        }
    });

    load();
    setInterval(load, 60000);
}

function initChatBadge() {
    const badge = document.querySelector('[data-chat-badge]');
    if (!badge) {
        return;
    }

    const countEl = badge.querySelector('[data-chat-badge-count]');
    const url = badge.dataset.unreadUrl;

    const load = () => {
        fetch(url, { headers: { Accept: 'application/json' } })
            .then((response) => response.json())
            .then((payload) => {
                const count = payload.unread_count || 0;
                countEl.textContent = count > 9 ? '9+' : String(count);
                countEl.classList.toggle('d-none', count === 0);
            })
            .catch(() => {});
    };

    load();
    setInterval(load, 15000);
}

function initAnnouncementForm() {
    const form = document.querySelector('[data-announcement-form]');
    if (!form) {
        return;
    }

    const audienceInputs = [...form.querySelectorAll('[data-announcement-audience]')];
    const studentPanel = form.querySelector('[data-announcement-students]');
    if (!audienceInputs.length || !studentPanel) {
        return;
    }

    const studentSelect = studentPanel.querySelector('select');

    const sync = () => {
        const selected = audienceInputs.find((input) => input.checked)?.value;
        const isSelected = selected === 'selected';
        studentPanel.classList.toggle('d-none', !isSelected);
        if (studentSelect) {
            studentSelect.required = isSelected;
        }
    };

    audienceInputs.forEach((input) => {
        input.addEventListener('change', sync);
    });

    sync();
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

function initChat() {
    const root = document.querySelector('[data-chat-app]');
    if (!root) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const listEl = root.querySelector('[data-chat-conversations]');
    const searchInput = root.querySelector('[data-chat-search]');
    const threadEl = root.querySelector('[data-chat-thread]');
    const modalEl = document.getElementById('chatNewConversationModal');
    const startBtn = modalEl?.querySelector('[data-chat-start-btn]');
    const pickerSelect = modalEl?.querySelector('[data-chat-picker]');

    let activeId = root.dataset.activeConversation ? Number(root.dataset.activeConversation) : null;
    let pollTimer = null;
    let listPollTimer = null;

    const escapeHtml = (value) => {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    };

    const requestJson = (url, options = {}) => {
        const method = (options.method || 'GET').toUpperCase();
        const headers = { Accept: 'application/json', ...(options.headers || {}) };
        if (method !== 'GET') {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }
        if (options.body) {
            headers['Content-Type'] = 'application/json';
        }

        return fetch(url, { ...options, headers }).then((response) => response.json());
    };

    const conversationItemHtml = (conversation) => `
        <span class="ed-chat__avatar">${escapeHtml(conversation.avatar)}</span>
        <span class="ed-chat__conv-body">
            <span class="ed-chat__conv-top">
                <span class="ed-chat__conv-name">${escapeHtml(conversation.name)}</span>
                <span class="ed-chat__conv-time" data-chat-conv-time>${escapeHtml(conversation.last_message_at)}</span>
            </span>
            <span class="ed-chat__conv-bottom">
                <span class="ed-chat__conv-preview" data-chat-conv-preview>${escapeHtml(conversation.last_message || root.dataset.sayHelloLabel || '')}</span>
                <span class="ed-chat__badge ${conversation.unread_count ? '' : 'd-none'}" data-chat-conv-badge>${conversation.unread_count || ''}</span>
            </span>
        </span>
    `;

    const renderConversationList = (conversations) => {
        if (!listEl) {
            return;
        }

        if (!conversations.length) {
            listEl.innerHTML = `<div class="ed-chat__empty-list" data-chat-empty-list><i class="bi bi-chat-dots"></i><p>${escapeHtml(root.dataset.emptyLabel || '')}</p></div>`;
            return;
        }

        listEl.innerHTML = conversations.map((conversation) => `
            <button type="button" class="ed-chat__conv ${conversation.id === activeId ? 'is-active' : ''}"
                data-chat-conversation-item data-id="${conversation.id}" data-name="${escapeHtml(conversation.name)}">
                ${conversationItemHtml(conversation)}
            </button>
        `).join('');

        applySearchFilter();
    };

    const applySearchFilter = () => {
        const term = (searchInput?.value || '').trim().toLowerCase();
        listEl?.querySelectorAll('[data-chat-conversation-item]').forEach((item) => {
            const matches = !term || item.dataset.name.toLowerCase().includes(term);
            item.classList.toggle('d-none', !matches);
        });
    };

    const loadConversations = () => {
        requestJson(root.dataset.conversationsUrl)
            .then((payload) => renderConversationList(payload.conversations || []))
            .catch(() => {});
    };

    const dayDividerHtml = (label) => `<div class="ed-chat__day-divider"><span>${escapeHtml(label)}</span></div>`;

    const bubbleHtml = (message) => `
        <div class="ed-chat__bubble-row ${message.is_mine ? 'is-mine' : ''}" data-chat-message-id="${message.id}">
            <div class="ed-chat__bubble">
                <span class="ed-chat__bubble-text">${escapeHtml(message.body)}</span>
                <span class="ed-chat__bubble-meta">
                    <span class="ed-chat__bubble-time">${escapeHtml(message.time)}</span>
                    ${message.is_mine ? `<i class="bi ${message.read ? 'bi-check2-all text-primary' : 'bi-check2'}"></i>` : ''}
                </span>
                ${message.is_mine ? `<button type="button" class="ed-chat__bubble-delete" data-chat-delete-message aria-label="${escapeHtml(root.dataset.deleteLabel || '')}"><i class="bi bi-trash3"></i></button>` : ''}
            </div>
        </div>
    `;

    const scrollThreadToBottom = () => {
        const messagesEl = threadEl.querySelector('[data-chat-messages]');
        if (messagesEl) {
            messagesEl.scrollTop = messagesEl.scrollHeight;
        }
    };

    const lastRenderedDate = () => {
        const dividers = threadEl.querySelectorAll('.ed-chat__day-divider span');
        return dividers.length ? dividers[dividers.length - 1].textContent : null;
    };

    const lastRenderedMessageId = () => {
        const rows = threadEl.querySelectorAll('[data-chat-message-id]');
        if (!rows.length) {
            return 0;
        }
        return Math.max(...[...rows].map((row) => Number(row.dataset.chatMessageId)));
    };

    const appendMessages = (messages) => {
        const list = threadEl.querySelector('[data-chat-message-list]');
        if (!list) {
            return;
        }

        let lastDate = lastRenderedDate();
        let html = '';
        messages.forEach((message) => {
            if (message.date_label !== lastDate) {
                html += dayDividerHtml(message.date_label);
                lastDate = message.date_label;
            }
            html += bubbleHtml(message);
        });

        list.insertAdjacentHTML('beforeend', html);
    };

    const renderThreadShell = (conversation) => {
        threadEl.classList.add('is-open');
        root.classList.add('is-thread-active');
        threadEl.innerHTML = `
            <div class="ed-chat__thread-head">
                <button type="button" class="btn btn-sm ed-chat__back" data-chat-back aria-label="${escapeHtml(root.dataset.backLabel || '')}">
                    <i class="bi bi-arrow-${document.documentElement.dir === 'rtl' ? 'right' : 'left'}"></i>
                </button>
                <span class="ed-chat__avatar">${escapeHtml(conversation.avatar)}</span>
                <span class="ed-chat__thread-name" data-chat-thread-name>${escapeHtml(conversation.name)}</span>
            </div>
            <div class="ed-chat__messages" data-chat-messages data-has-more="0" data-next-page="2">
                <div class="ed-chat__load-more d-none"><button type="button" class="btn btn-sm btn-outline-secondary" data-chat-load-more>${escapeHtml(root.dataset.loadMoreLabel || '')}</button></div>
                <div data-chat-message-list></div>
            </div>
            <form class="ed-chat__composer" data-chat-composer>
                <textarea name="body" rows="1" class="ed-chat__input" data-chat-input placeholder="${escapeHtml(root.dataset.typeLabel || '')}" maxlength="2000" required></textarea>
                <button type="submit" class="ed-chat__send" aria-label="${escapeHtml(root.dataset.sendLabel || '')}"><i class="bi bi-send-fill"></i></button>
            </form>
        `;
        bindThreadEvents();
    };

    const openConversation = (id, name, avatar) => {
        activeId = Number(id);
        renderThreadShell({ name, avatar: avatar || (name ? name.charAt(0) : '') });

        listEl?.querySelectorAll('[data-chat-conversation-item]').forEach((item) => {
            item.classList.toggle('is-active', Number(item.dataset.id) === activeId);
            if (Number(item.dataset.id) === activeId) {
                item.querySelector('[data-chat-conv-badge]')?.classList.add('d-none');
                if (item.querySelector('[data-chat-conv-badge]')) {
                    item.querySelector('[data-chat-conv-badge]').textContent = '';
                }
            }
        });

        const url = new URL(window.location.href);
        url.searchParams.set('conversation', String(activeId));
        window.history.pushState({}, '', url);

        loadMessages({ reset: true });

        clearInterval(pollTimer);
        pollTimer = setInterval(pollActiveThread, 3000);
    };

    const loadMessages = ({ reset = false, page = null } = {}) => {
        if (!activeId) {
            return;
        }

        const base = root.dataset.messagesUrlBase.replace('__ID__', activeId);
        const url = page ? `${base}?page=${page}` : base;

        requestJson(url).then((payload) => {
            const messagesEl = threadEl.querySelector('[data-chat-messages]');
            if (!messagesEl) {
                return;
            }

            if (reset) {
                threadEl.querySelector('[data-chat-message-list]').innerHTML = '';
            }

            const list = threadEl.querySelector('[data-chat-message-list]');
            const beforeHeight = list.scrollHeight;

            if (page) {
                let html = '';
                let lastDate = null;
                payload.messages.forEach((message) => {
                    if (message.date_label !== lastDate) {
                        html += dayDividerHtml(message.date_label);
                        lastDate = message.date_label;
                    }
                    html += bubbleHtml(message);
                });
                list.insertAdjacentHTML('afterbegin', html);
                const messagesScroll = threadEl.querySelector('[data-chat-messages]');
                messagesScroll.scrollTop = list.scrollHeight - beforeHeight;
            } else {
                appendMessages(payload.messages);
                scrollThreadToBottom();
            }

            messagesEl.dataset.hasMore = payload.has_more ? '1' : '0';
            messagesEl.dataset.nextPage = String(payload.next_page);
            threadEl.querySelector('[data-chat-load-more]')?.parentElement.classList.toggle('d-none', !payload.has_more);
        }).catch(() => {});
    };

    const pollActiveThread = () => {
        if (!activeId) {
            return;
        }

        const base = root.dataset.messagesUrlBase.replace('__ID__', activeId);
        requestJson(base).then((payload) => {
            const lastId = lastRenderedMessageId();
            const fresh = (payload.messages || []).filter((message) => message.id > lastId);
            if (fresh.length) {
                appendMessages(fresh);
                scrollThreadToBottom();
            }
        }).catch(() => {});
    };

    const bindThreadEvents = () => {
        threadEl.querySelector('[data-chat-back]')?.addEventListener('click', () => {
            root.classList.remove('is-thread-active');
        });

        threadEl.querySelector('[data-chat-load-more]')?.addEventListener('click', () => {
            const messagesEl = threadEl.querySelector('[data-chat-messages]');
            loadMessages({ page: Number(messagesEl.dataset.nextPage) });
        });

        const input = threadEl.querySelector('[data-chat-input]');
        input?.addEventListener('input', () => {
            input.style.height = 'auto';
            input.style.height = `${input.scrollHeight}px`;
        });
        input?.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                threadEl.querySelector('[data-chat-composer]')?.requestSubmit();
            }
        });

        threadEl.querySelector('[data-chat-composer]')?.addEventListener('submit', (event) => {
            event.preventDefault();
            const textarea = threadEl.querySelector('[data-chat-input]');
            const body = textarea.value.trim();
            if (!body || !activeId) {
                return;
            }

            const sendUrl = root.dataset.sendUrlBase.replace('__ID__', activeId);
            requestJson(sendUrl, { method: 'POST', body: JSON.stringify({ body }) })
                .then((payload) => {
                    if (!payload.message) {
                        return;
                    }
                    appendMessages([payload.message]);
                    scrollThreadToBottom();
                    textarea.value = '';
                    textarea.style.height = 'auto';
                    loadConversations();
                })
                .catch(() => {});
        });

        threadEl.addEventListener('click', (event) => {
            const deleteButton = event.target.closest('[data-chat-delete-message]');
            if (!deleteButton) {
                return;
            }

            const row = deleteButton.closest('[data-chat-message-id]');
            const messageId = row?.dataset.chatMessageId;
            if (!messageId) {
                return;
            }

            window.edConfirm(root.dataset.deleteConfirmLabel || '', root.dataset.deleteLabel || '', true, () => {
                const deleteUrl = root.dataset.deleteUrlBase.replace('__ID__', messageId);
                requestJson(deleteUrl, { method: 'DELETE' }).then(() => {
                    row.remove();
                });
            });
        });
    };

    listEl?.addEventListener('click', (event) => {
        const item = event.target.closest('[data-chat-conversation-item]');
        if (!item) {
            return;
        }
        openConversation(item.dataset.id, item.dataset.name, item.querySelector('.ed-chat__avatar')?.textContent);
    });

    searchInput?.addEventListener('input', applySearchFilter);

    if (threadEl.querySelector('[data-chat-composer]')) {
        bindThreadEvents();
    }

    startBtn?.addEventListener('click', () => {
        const selectedId = pickerSelect?.value;
        if (!selectedId) {
            return;
        }

        requestJson(root.dataset.startUrl, {
            method: 'POST',
            body: JSON.stringify({ [root.dataset.pickerField]: selectedId }),
        }).then((payload) => {
            if (!payload.conversation) {
                return;
            }

            if (window.bootstrap && modalEl) {
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            }

            loadConversations();
            openConversation(payload.conversation.id, payload.conversation.name, payload.conversation.avatar);
        }).catch(() => {});
    });

    listPollTimer = setInterval(loadConversations, 6000);
    if (activeId) {
        pollTimer = setInterval(pollActiveThread, 3000);
    }

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearInterval(pollTimer);
            clearInterval(listPollTimer);
            return;
        }
        loadConversations();
        if (activeId) {
            pollActiveThread();
        }
        listPollTimer = setInterval(loadConversations, 6000);
        if (activeId) {
            pollTimer = setInterval(pollActiveThread, 3000);
        }
    });
}
