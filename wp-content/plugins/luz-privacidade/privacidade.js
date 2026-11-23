(() => {
  'use strict';
  const key = 'luz_privacy_notice_v1';
  const ttl = 180 * 24 * 60 * 60 * 1000;
  const notice = document.getElementById('luz-privacy-notice');
  if (!notice) return;
  let previousFocus;
  let saved;
  try { saved = JSON.parse(localStorage.getItem(key)); } catch (_) {}
  const valid = saved && saved.version === 1 && Number.isFinite(saved.timestamp) && Date.now() >= saved.timestamp && Date.now() - saved.timestamp < ttl;
  notice.hidden = Boolean(valid);
  document.querySelectorAll('[data-luz-reopen]').forEach(button => button.addEventListener('click', () => {
    previousFocus = button;
    notice.hidden = false;
    notice.querySelector('button').focus();
  }));
  notice.querySelector('[data-luz-close]').addEventListener('click', () => {
    try { localStorage.setItem(key, JSON.stringify({version: 1, timestamp: Date.now()})); } catch (_) {}
    notice.hidden = true;
    if (previousFocus) previousFocus.focus();
  });
})();
