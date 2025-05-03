{{-- Comments section for commentable entities --}}
<div id="comments-section" class="bg-white rounded shadow-sm p-4 mb-4">
    <h5 class="mb-4">Comments</h5>
    
    {{-- Comments list --}}
    <div class="comments-list mb-4" data-commentable-id="{{ $commentableId }}" data-commentable-type="{{ $commentableType }}">
        {{-- Comments will be loaded here via JavaScript --}}
        <div class="text-center py-3 loading-indicator">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <span class="ms-2">Loading comments...</span>
        </div>
    </div>
    
    {{-- Comment form --}}
    <div class="comment-form">
        <form id="new-comment-form">
            <input type="hidden" name="commentable_id" value="{{ $commentableId }}">
            <input type="hidden" name="commentable_type" value="{{ $commentableType }}">
            <input type="hidden" name="parent_id" value="" id="reply-parent-id">
            
            <div class="mb-3">
                <label for="comment-content" class="form-label visually-hidden">Comment</label>
                <textarea class="form-control" id="comment-content" name="content" rows="3" placeholder="Add a comment..."></textarea>
            </div>
            
            <div class="d-flex justify-content-between align-items-center">
                <div class="reply-indicator d-none">
                    <span class="badge bg-light text-dark">Replying to: <span class="reply-to-user"></span></span>
                    <button type="button" class="btn btn-sm btn-link p-0 ms-2 cancel-reply">Cancel</button>
                </div>
                <div class="sync-indicator text-muted small">
                    @if(isset($externalUrl) && $externalUrl)
                    <i class="icon-refresh"></i> Comments will be synced with 
                    <a href="{{ $externalUrl }}" target="_blank">
                        {{ isset($externalId) ? "#$externalId" : "external issue" }}
                    </a>
                    @endif
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="icon-paper-plane"></i> Submit
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Comment template for JavaScript rendering --}}
<template id="comment-template">
    <div class="comment mb-4" data-comment-id="">
        <div class="d-flex">
            <div class="flex-shrink-0">
                <div class="avatar rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <span class="user-initials"></span>
                </div>
            </div>
            <div class="flex-grow-1 ms-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-0 user-name"></h6>
                        <small class="text-muted comment-time"></small>
                    </div>
                    <div class="dropdown comment-actions">
                        <button class="btn btn-sm btn-link dropdown-toggle no-caret" type="button" data-bs-toggle="dropdown">
                            <i class="icon-options-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><button class="dropdown-item reply-action" type="button">Reply</button></li>
                            <li><button class="dropdown-item edit-action" type="button">Edit</button></li>
                            <li><button class="dropdown-item delete-action" type="button">Delete</button></li>
                        </ul>
                    </div>
                </div>
                <div class="comment-content mt-2"></div>
                <div class="comment-edit-form mt-2 d-none">
                    <form>
                        <textarea class="form-control edit-content mb-2" rows="3"></textarea>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-sm btn-link cancel-edit">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-primary ms-2 save-edit">Save</button>
                        </div>
                    </form>
                </div>
                <div class="mt-2">
                    <button class="btn btn-sm btn-link p-0 reply-btn">Reply</button>
                    <span class="text-muted mx-1 small">•</span>
                    <span class="sync-badge"></span>
                </div>
                <div class="replies mt-3"></div>
            </div>
        </div>
    </div>
</template>

{{-- Reply template for JavaScript rendering --}}
<template id="reply-template">
    <div class="reply mb-3" data-comment-id="">
        <div class="d-flex">
            <div class="flex-shrink-0">
                <div class="avatar rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                    <span class="user-initials"></span>
                </div>
            </div>
            <div class="flex-grow-1 ms-2">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-0 small user-name"></h6>
                        <small class="text-muted comment-time"></small>
                    </div>
                    <div class="dropdown reply-actions">
                        <button class="btn btn-sm btn-link dropdown-toggle no-caret" type="button" data-bs-toggle="dropdown">
                            <i class="icon-options-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><button class="dropdown-item edit-action" type="button">Edit</button></li>
                            <li><button class="dropdown-item delete-action" type="button">Delete</button></li>
                        </ul>
                    </div>
                </div>
                <div class="comment-content mt-1 small"></div>
                <div class="comment-edit-form mt-2 d-none">
                    <form>
                        <textarea class="form-control edit-content mb-2" rows="2"></textarea>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-sm btn-link cancel-edit">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-primary ms-2 save-edit">Save</button>
                        </div>
                    </form>
                </div>
                <div class="mt-1">
                    <span class="sync-badge"></span>
                </div>
            </div>
        </div>
    </div>
</template>
