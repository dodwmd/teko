/**
 * Teko comment system functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the comments system if the section exists
    const commentsSection = document.getElementById('comments-section');
    if (commentsSection) {
        initializeComments();
    }
});

/**
 * Initialize the comments system
 */
function initializeComments() {
    const commentsList = document.querySelector('.comments-list');
    const commentForm = document.getElementById('new-comment-form');
    const commentableId = commentsList.dataset.commentableId;
    const commentableType = commentsList.dataset.commentableType;
    
    // Load comments
    loadComments(commentableId, commentableType);
    
    // Setup form submission
    commentForm.addEventListener('submit', function(e) {
        e.preventDefault();
        submitComment(this);
    });
    
    // Setup reply cancellation
    document.querySelector('.cancel-reply').addEventListener('click', function() {
        cancelReply();
    });
    
    // Track comment events for analytics
    trackCommentEvents();
}

/**
 * Load comments for a commentable entity
 */
function loadComments(commentableId, commentableType) {
    const commentsList = document.querySelector('.comments-list');
    
    fetch(`/comments/list?commentable_id=${commentableId}&commentable_type=${commentableType}`)
        .then(response => response.json())
        .then(data => {
            // Clear loading indicator
            commentsList.innerHTML = '';
            
            if (data.comments.length === 0) {
                commentsList.innerHTML = '<div class="text-center py-3 text-muted">No comments yet. Be the first to comment!</div>';
                return;
            }
            
            // Render each comment
            data.comments.forEach(comment => {
                renderComment(comment, commentsList);
            });
            
            // Setup event listeners for actions
            setupCommentActionListeners();
        })
        .catch(error => {
            console.error('Error loading comments:', error);
            commentsList.innerHTML = '<div class="alert alert-danger">Failed to load comments. Please try again later.</div>';
        });
}

/**
 * Render a comment and its replies
 */
function renderComment(comment, container, isReply = false) {
    const template = document.getElementById(isReply ? 'reply-template' : 'comment-template');
    const commentElement = document.importNode(template.content, true).firstElementChild;
    
    // Set comment data
    commentElement.dataset.commentId = comment.id;
    
    // Set user information
    const userName = commentElement.querySelector('.user-name');
    const userInitials = commentElement.querySelector('.user-initials');
    userName.textContent = comment.user.name;
    userInitials.textContent = getInitials(comment.user.name);
    
    // Set comment content and time
    const commentContent = commentElement.querySelector('.comment-content');
    const commentTime = commentElement.querySelector('.comment-time');
    commentContent.textContent = comment.content;
    commentTime.textContent = formatDate(comment.created_at);
    
    // Set edit form content
    const editContent = commentElement.querySelector('.edit-content');
    editContent.textContent = comment.content;
    
    // Show sync badge if applicable
    const syncBadge = commentElement.querySelector('.sync-badge');
    if (comment.external_id) {
        syncBadge.innerHTML = `<span class="badge bg-info text-white">Synced</span>`;
    }
    
    // Show/hide edit and delete actions based on ownership
    const editAction = commentElement.querySelector('.edit-action');
    const deleteAction = commentElement.querySelector('.delete-action');
    
    if (comment.user_id !== getCurrentUserId()) {
        editAction.parentElement.classList.add('d-none');
        // Only show delete for admins or comment owner
        if (!hasPermission('comment.delete.any')) {
            deleteAction.parentElement.classList.add('d-none');
        }
    }
    
    // Add to container
    container.appendChild(commentElement);
    
    // Render replies if any
    if (!isReply && comment.replies && comment.replies.length > 0) {
        const repliesContainer = commentElement.querySelector('.replies');
        comment.replies.forEach(reply => {
            renderComment(reply, repliesContainer, true);
        });
    }
}

/**
 * Submit a new comment or reply
 */
function submitComment(form) {
    const formData = new FormData(form);
    
    // Add tracking for analytics
    trackEvent('Comments', 'Submit', formData.get('parent_id') ? 'Reply' : 'Comment');
    
    fetch('/comments', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.comment) {
            const commentsList = document.querySelector('.comments-list');
            const emptyMessage = commentsList.querySelector('.text-muted');
            
            // Remove empty message if it exists
            if (emptyMessage) {
                emptyMessage.remove();
            }
            
            // If it's a reply, find the parent comment
            if (data.comment.parent_id) {
                const parentComment = document.querySelector(`.comment[data-comment-id="${data.comment.parent_id}"]`);
                if (parentComment) {
                    const repliesContainer = parentComment.querySelector('.replies');
                    renderComment(data.comment, repliesContainer, true);
                    cancelReply();
                }
            } else {
                // It's a top-level comment
                renderComment(data.comment, commentsList);
            }
            
            // Reset form
            form.reset();
            setupCommentActionListeners();
        }
    })
    .catch(error => {
        console.error('Error submitting comment:', error);
        alert('Failed to submit comment. Please try again.');
    });
}

/**
 * Setup reply mode
 */
function setupReply(commentId, userName) {
    const replyParentId = document.getElementById('reply-parent-id');
    const replyIndicator = document.querySelector('.reply-indicator');
    const replyToUser = document.querySelector('.reply-to-user');
    const commentContent = document.getElementById('comment-content');
    
    replyParentId.value = commentId;
    replyToUser.textContent = userName;
    replyIndicator.classList.remove('d-none');
    
    // Focus the textarea
    commentContent.focus();
    
    // Scroll to comment form
    document.querySelector('.comment-form').scrollIntoView({ behavior: 'smooth' });
}

/**
 * Cancel reply mode
 */
function cancelReply() {
    const replyParentId = document.getElementById('reply-parent-id');
    const replyIndicator = document.querySelector('.reply-indicator');
    
    replyParentId.value = '';
    replyIndicator.classList.add('d-none');
}

/**
 * Setup event listeners for comment actions
 */
function setupCommentActionListeners() {
    // Reply buttons
    document.querySelectorAll('.reply-btn, .reply-action').forEach(button => {
        button.addEventListener('click', function() {
            const comment = findParentComment(this);
            const commentId = comment.dataset.commentId;
            const userName = comment.querySelector('.user-name').textContent;
            setupReply(commentId, userName);
        });
    });
    
    // Edit buttons
    document.querySelectorAll('.edit-action').forEach(button => {
        button.addEventListener('click', function() {
            const comment = findParentComment(this);
            const contentElement = comment.querySelector('.comment-content');
            const editForm = comment.querySelector('.comment-edit-form');
            
            contentElement.classList.add('d-none');
            editForm.classList.remove('d-none');
        });
    });
    
    // Cancel edit buttons
    document.querySelectorAll('.cancel-edit').forEach(button => {
        button.addEventListener('click', function() {
            const comment = findParentComment(this);
            const contentElement = comment.querySelector('.comment-content');
            const editForm = comment.querySelector('.comment-edit-form');
            
            contentElement.classList.remove('d-none');
            editForm.classList.add('d-none');
        });
    });
    
    // Save edit buttons
    document.querySelectorAll('.save-edit').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const comment = findParentComment(this);
            const commentId = comment.dataset.commentId;
            const editTextarea = comment.querySelector('.edit-content');
            const contentElement = comment.querySelector('.comment-content');
            const editForm = comment.querySelector('.comment-edit-form');
            
            // Track edit for analytics
            trackEvent('Comments', 'Edit', 'Comment');
            
            updateComment(commentId, editTextarea.value)
                .then(updatedComment => {
                    contentElement.textContent = updatedComment.content;
                    contentElement.classList.remove('d-none');
                    editForm.classList.add('d-none');
                })
                .catch(error => {
                    console.error('Error updating comment:', error);
                    alert('Failed to update comment. Please try again.');
                });
        });
    });
    
    // Delete buttons
    document.querySelectorAll('.delete-action').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this comment?')) {
                const comment = findParentComment(this);
                const commentId = comment.dataset.commentId;
                
                // Track deletion for analytics
                trackEvent('Comments', 'Delete', 'Comment');
                
                deleteComment(commentId)
                    .then(() => {
                        comment.remove();
                    })
                    .catch(error => {
                        console.error('Error deleting comment:', error);
                        alert('Failed to delete comment. Please try again.');
                    });
            }
        });
    });
}

/**
 * Update a comment
 */
function updateComment(commentId, content) {
    return fetch(`/comments/${commentId}`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ content })
    })
    .then(response => response.json())
    .then(data => data.comment);
}

/**
 * Delete a comment
 */
function deleteComment(commentId) {
    return fetch(`/comments/${commentId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    })
    .then(response => response.json());
}

/**
 * Find the parent comment element
 */
function findParentComment(element) {
    let current = element;
    while (current && !current.classList.contains('comment') && !current.classList.contains('reply')) {
        current = current.parentElement;
    }
    return current;
}

/**
 * Get the current user ID
 */
function getCurrentUserId() {
    // This would be populated from a global variable set in your Blade layout
    return window.tekoUserId || null;
}

/**
 * Check if the current user has a permission
 */
function hasPermission(permission) {
    // This would be populated from a global variable set in your Blade layout
    return window.tekoUserPermissions && window.tekoUserPermissions.includes(permission);
}

/**
 * Format a date string
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    
    // If less than 24 hours ago, show relative time
    if (now - date < 24 * 60 * 60 * 1000) {
        const hours = Math.floor((now - date) / (60 * 60 * 1000));
        if (hours < 1) {
            const minutes = Math.floor((now - date) / (60 * 1000));
            return minutes <= 1 ? 'just now' : `${minutes} minutes ago`;
        }
        return `${hours} ${hours === 1 ? 'hour' : 'hours'} ago`;
    }
    
    // Otherwise show the date
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Get initials from a name
 */
function getInitials(name) {
    if (!name) return '?';
    return name.split(' ')
        .map(part => part.charAt(0))
        .join('')
        .toUpperCase()
        .substring(0, 2);
}

/**
 * Track comment events for analytics
 */
function trackCommentEvents() {
    // Reference to window.trackEvent function from google-analytics.blade.php
    if (typeof window.trackEvent !== 'function') {
        return;
    }
    
    // Initial page load
    trackEvent('Comments', 'View', 'Comment Section');
}
