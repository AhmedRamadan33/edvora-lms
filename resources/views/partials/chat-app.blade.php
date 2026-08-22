@php
    $conversationsUrl = route("{$routePrefix}.conversations");
    $startUrl = route("{$routePrefix}.start");
    $messagesUrlBase = route("{$routePrefix}.messages", ['conversation' => '__ID__']);
    $sendUrlBase = route("{$routePrefix}.send", ['conversation' => '__ID__']);
    $readUrlBase = route("{$routePrefix}.read", ['conversation' => '__ID__']);
    $deleteUrlBase = route("{$routePrefix}.messages.destroy", ['message' => '__ID__']);
@endphp
<div class="ed-chat"
    data-chat-app
    data-conversations-url="{{ $conversationsUrl }}"
    data-start-url="{{ $startUrl }}"
    data-messages-url-base="{{ $messagesUrlBase }}"
    data-send-url-base="{{ $sendUrlBase }}"
    data-read-url-base="{{ $readUrlBase }}"
    data-delete-url-base="{{ $deleteUrlBase }}"
    data-active-conversation="{{ $activeConversation?->id }}"
    data-current-user-id="{{ auth()->id() }}"
    data-picker-field="{{ $routePrefix === 'instructor.chat' ? 'student_id' : 'instructor_id' }}"
    data-say-hello-label="{{ __('Say hello!') }}"
    data-empty-label="{{ __('No conversations yet.') }}"
    data-back-label="{{ __('Back') }}"
    data-load-more-label="{{ __('Load older messages') }}"
    data-type-label="{{ __('Type a message') }}"
    data-send-label="{{ __('Send') }}"
    data-delete-label="{{ __('Delete message') }}"
    data-delete-confirm-label="{{ __('Delete this message? This cannot be undone.') }}">

    <div class="ed-chat__list">
        <div class="ed-chat__list-head">
            <h2 class="ed-chat__title">{{ __('Messages') }}</h2>
            <button type="button" class="btn btn-primary btn-sm ed-chat__new-btn" data-bs-toggle="modal" data-bs-target="#chatNewConversationModal">
                <i class="bi bi-plus-lg"></i> {{ __('New chat') }}
            </button>
        </div>
        <div class="ed-chat__search">
            <i class="bi bi-search"></i>
            <input type="search" data-chat-search placeholder="{{ __('Search conversations') }}" aria-label="{{ __('Search conversations') }}">
        </div>
        <div class="ed-chat__conversations" data-chat-conversations>
            @forelse ($conversations as $conversation)
                <button type="button" class="ed-chat__conv @if($activeConversation?->id === $conversation->id) is-active @endif"
                    data-chat-conversation-item
                    data-id="{{ $conversation->id }}"
                    data-name="{{ $conversation->other_party->name }}">
                    <span class="ed-chat__avatar">{{ mb_substr($conversation->other_party->name, 0, 1) }}</span>
                    <span class="ed-chat__conv-body">
                        <span class="ed-chat__conv-top">
                            <span class="ed-chat__conv-name">{{ $conversation->other_party->name }}</span>
                            <span class="ed-chat__conv-time" data-chat-conv-time>{{ $conversation->lastMessage?->created_at?->diffForHumans() }}</span>
                        </span>
                        <span class="ed-chat__conv-bottom">
                            <span class="ed-chat__conv-preview" data-chat-conv-preview>{{ Str::limit($conversation->lastMessage?->body, 42) ?: __('Say hello!') }}</span>
                            @if($conversation->unread_count > 0)
                                <span class="ed-chat__badge" data-chat-conv-badge>{{ $conversation->unread_count }}</span>
                            @else
                                <span class="ed-chat__badge d-none" data-chat-conv-badge></span>
                            @endif
                        </span>
                    </span>
                </button>
            @empty
                <div class="ed-chat__empty-list" data-chat-empty-list>
                    <i class="bi bi-chat-dots"></i>
                    <p>{{ __('No conversations yet.') }}</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="ed-chat__thread @if($activeConversation) is-open @endif" data-chat-thread>
        @if ($activeConversation)
            <div class="ed-chat__thread-head">
                <button type="button" class="btn btn-sm ed-chat__back" data-chat-back aria-label="{{ __('Back') }}">
                    <i class="bi bi-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i>
                </button>
                <span class="ed-chat__avatar">{{ mb_substr($activeConversation->other_party->name, 0, 1) }}</span>
                <span class="ed-chat__thread-name" data-chat-thread-name>{{ $activeConversation->other_party->name }}</span>
            </div>
            <div class="ed-chat__messages" data-chat-messages
                data-has-more="{{ $messages->hasMorePages() ? '1' : '0' }}"
                data-next-page="{{ $messages->currentPage() + 1 }}">
                @if ($messages->hasMorePages())
                    <div class="ed-chat__load-more">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-chat-load-more>{{ __('Load older messages') }}</button>
                    </div>
                @endif
                <div data-chat-message-list>
                    @php
                        $lastDate = null;
                    @endphp
                    @foreach ($messages->reverse() as $message)
                        @php
                            $dateLabel = $message->created_at->isToday() ? __('Today') : ($message->created_at->isYesterday() ? __('Yesterday') : $message->created_at->format('d M Y'));
                        @endphp
                        @if ($dateLabel !== $lastDate)
                            <div class="ed-chat__day-divider"><span>{{ $dateLabel }}</span></div>
                            @php
                                $lastDate = $dateLabel;
                            @endphp
                        @endif
                        <div class="ed-chat__bubble-row @if($message->sender_id === auth()->id()) is-mine @endif" data-chat-message-id="{{ $message->id }}">
                            <div class="ed-chat__bubble">
                                <span class="ed-chat__bubble-text">{{ $message->body }}</span>
                                <span class="ed-chat__bubble-meta">
                                    <span class="ed-chat__bubble-time">{{ $message->created_at->format('H:i') }}</span>
                                    @if($message->sender_id === auth()->id())
                                        <i class="bi {{ $message->read_at ? 'bi-check2-all text-primary' : 'bi-check2' }}"></i>
                                    @endif
                                </span>
                                @if($message->sender_id === auth()->id())
                                    <button type="button" class="ed-chat__bubble-delete" data-chat-delete-message aria-label="{{ __('Delete message') }}">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <form class="ed-chat__composer" data-chat-composer>
                <textarea name="body" rows="1" class="ed-chat__input" data-chat-input placeholder="{{ __('Type a message') }}" maxlength="2000" required></textarea>
                <button type="submit" class="ed-chat__send" aria-label="{{ __('Send') }}">
                    <i class="bi bi-send-fill"></i>
                </button>
            </form>
        @else
            <div class="ed-chat__placeholder" data-chat-placeholder>
                <i class="bi bi-chat-square-text"></i>
                <p>{{ __('Select a conversation to start chatting') }}</p>
            </div>
        @endif
    </div>
</div>

<div class="modal fade" id="chatNewConversationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Start a new conversation') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">{{ $pickerLabel }}</label>
                <select class="form-select" data-chat-picker>
                    <option value="">{{ __('Choose someone to message') }}</option>
                    @foreach ($pickerUsers as $pickerUser)
                        <option value="{{ $pickerUser->id }}">{{ $pickerUser->name }} ({{ $pickerUser->email }})</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" data-chat-start-btn>{{ __('Start chat') }}</button>
            </div>
        </div>
    </div>
</div>
