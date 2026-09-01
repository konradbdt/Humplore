(function () {
  'use strict';

  const likeBusy = new Set();
  const saveBusy = new Set();

  function csrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? (meta.getAttribute('content') || '') : '';
  }

  function buttonsFor(selector, postId) {
    return Array.from(document.querySelectorAll(selector)).filter((button) => (
      button.getAttribute('data-post-id') === String(postId)
    ));
  }

  function setButtonsDisabled(selector, postId, disabled) {
    buttonsFor(selector, postId).forEach((button) => {
      button.disabled = disabled;
    });
  }

  function setLikeButtons(postId, liked, count) {
    buttonsFor('.like-button[data-post-id]', postId).forEach((button) => {
      button.classList.toggle('liked', liked);
      button.setAttribute('aria-pressed', liked ? 'true' : 'false');
      button.setAttribute('aria-label', liked ? 'Neues gelernt! entfernen' : 'Neues gelernt! markieren');
      const countElement = button.querySelector('.like-count');
      if (countElement && Number.isInteger(count)) {
        countElement.textContent = String(count);
      }
    });
  }

  function setSavedPostButtons(postId, saved) {
    buttonsFor('.save-post-button[data-post-id]', postId).forEach((button) => {
      button.classList.toggle('saved', saved);
      button.setAttribute('aria-pressed', saved ? 'true' : 'false');
      button.setAttribute('aria-label', saved ? 'Beitrag nicht mehr merken' : 'Beitrag merken');
    });
  }

  function showError(message) {
    let status = document.getElementById('post-action-status');
    if (!status) {
      status = document.createElement('div');
      status.id = 'post-action-status';
      status.setAttribute('role', 'status');
      status.setAttribute('aria-live', 'polite');
      status.style.cssText = 'position:fixed;left:50%;bottom:80px;transform:translateX(-50%);background:#991b1b;color:#fff;padding:10px 14px;border-radius:10px;z-index:2000;font-size:.95rem';
      document.body.appendChild(status);
    }
    status.textContent = message;
    window.setTimeout(() => {
      if (status.textContent === message) status.textContent = '';
    }, 1800);
  }

  async function parseJson(response) {
    try {
      return await response.json();
    } catch (_) {
      return null;
    }
  }

  window.toggleLike = async function toggleLike(button) {
    const postId = button && button.getAttribute('data-post-id');
    if (!postId || likeBusy.has(postId)) return;

    likeBusy.add(postId);
    setButtonsDisabled('.like-button[data-post-id]', postId, true);

    try {
      const formData = new FormData();
      formData.append('post_id', postId);
      formData.append('csrf_token', csrfToken());

      const response = await fetch('like_handler.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
        headers: { 'X-CSRF-Token': csrfToken() }
      });
      const data = await parseJson(response);
      if (!response.ok || !data || data.success !== true || typeof data.liked !== 'boolean') {
        throw new Error((data && data.error) || 'Like failed');
      }

      setLikeButtons(postId, data.liked, Number.parseInt(data.likeCount, 10));
    } catch (error) {
      console.error(error);
      showError('Reaktion konnte nicht gespeichert werden.');
    } finally {
      likeBusy.delete(postId);
      setButtonsDisabled('.like-button[data-post-id]', postId, false);
    }
  };

  window.setSavedPostButtons = setSavedPostButtons;
  window.toggleSavedPost = async function toggleSavedPost(button) {
    const postId = button && button.getAttribute('data-post-id');
    if (!postId || saveBusy.has(postId)) return;

    saveBusy.add(postId);
    setButtonsDisabled('.save-post-button[data-post-id]', postId, true);

    try {
      const formData = new FormData();
      formData.append('post_id', postId);
      formData.append('csrf_token', csrfToken());

      const response = await fetch('save_post_handler.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
        headers: { 'X-CSRF-Token': csrfToken() }
      });
      const data = await parseJson(response);
      if (!response.ok || !data || typeof data.saved !== 'boolean' || Number(data.post_id) <= 0) {
        throw new Error((data && data.error) || 'Save failed');
      }

      setSavedPostButtons(String(data.post_id), data.saved);
      if (!data.saved && document.body.dataset.savedPostsPage === 'true') {
        window.location.reload();
      }
    } catch (error) {
      console.error(error);
      showError('Merken konnte nicht aktualisiert werden.');
    } finally {
      saveBusy.delete(postId);
      setButtonsDisabled('.save-post-button[data-post-id]', postId, false);
    }
  };

  window.toggleComments = function toggleComments(postId, trigger) {
    const scopedCard = trigger ? trigger.closest('.post-card') : null;
    const section = scopedCard
      ? scopedCard.querySelector(`#comments-${postId}`)
      : document.getElementById(`comments-${postId}`);
    if (!section) return;

    const isOpen = section.classList.contains('open');
    if (isOpen) {
      section.style.maxHeight = `${section.scrollHeight}px`;
      window.requestAnimationFrame(() => {
        section.classList.remove('open');
        section.style.maxHeight = '0px';
      });
      return;
    }

    section.classList.add('open');
    section.style.maxHeight = `${section.scrollHeight}px`;
    window.setTimeout(() => {
      section.style.maxHeight = '1000px';
      const textarea = section.querySelector('textarea[name="comment_text"]');
      if (textarea) textarea.focus();
    }, 310);
  };

  function legacyCopyToClipboard(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.setAttribute('readonly', '');
    textarea.style.position = 'fixed';
    textarea.style.left = '-9999px';
    document.body.appendChild(textarea);
    textarea.select();
    try { document.execCommand('copy'); } catch (_) {}
    textarea.remove();
    return Promise.resolve();
  }

  function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
      return navigator.clipboard.writeText(text).catch(() => legacyCopyToClipboard(text));
    }

    return legacyCopyToClipboard(text);
  }

  window.sharePost = function sharePost(postId) {
    const params = new URLSearchParams(window.location.search);
    params.delete('post_id');
    params.set('post_id', String(postId));
    const url = `${window.location.origin}${window.location.pathname}?${params.toString()}`;
    const title = document.body.dataset.postShareTitle || 'Beitrag auf humplore';
    const text = document.body.dataset.postShareText || 'Schau dir diesen Beitrag an.';

    if (navigator.share) {
      navigator.share({ title, text, url }).catch(() => {});
      return;
    }

    copyToClipboard(url).then(() => {
      const message = document.body.dataset.postShareConfirmation || 'Link kopiert!';
      let status = document.getElementById('post-share-status');
      if (!status) {
        status = document.createElement('div');
        status.id = 'post-share-status';
        status.setAttribute('role', 'status');
        status.setAttribute('aria-live', 'polite');
        status.style.cssText = 'position:fixed;left:50%;bottom:80px;transform:translateX(-50%);background:#111827;color:#fff;padding:10px 14px;border-radius:10px;z-index:2000;font-size:.95rem';
        document.body.appendChild(status);
      }
      status.textContent = message;
      window.setTimeout(() => {
        if (status.textContent === message) status.textContent = '';
      }, 1500);
    });
  };
}());
