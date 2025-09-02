// Campaigns page client-side validation
// Validates create/update campaign forms; non-blocking for linking/assigning forms
(function(){
  'use strict';

  var ALLOWED_STATUS = ['draft','active','archived'];

  function getAction(form){
    var a = form.querySelector('input[name="action"]');
    return a ? String(a.value).trim() : '';
    }

  function clearErrors(form){
    // Remove top error box
    var box = form.querySelector('.js-form-errors');
    if (box && box.parentNode) box.parentNode.removeChild(box);
    // Clear field classes
    form.querySelectorAll('.is-invalid').forEach(function(el){ el.classList.remove('is-invalid'); });
  }

  function showErrors(form, errors, firstInvalid){
    if (!errors || !errors.length) return;
    var box = document.createElement('div');
    box.className = 'alert alert-danger js-form-errors';
    box.style.marginBottom = '12px';
    var ul = document.createElement('ul');
    ul.style.margin = '0 0 0 18px';
    errors.forEach(function(msg){
      var li = document.createElement('li');
      li.textContent = msg;
      ul.appendChild(li);
    });
    box.appendChild(ul);
    // Insert at top of form
    if (form.firstChild) form.insertBefore(box, form.firstChild); else form.appendChild(box);
    if (firstInvalid && typeof firstInvalid.focus === 'function') {
      try { firstInvalid.focus(); } catch(_) {}
      firstInvalid.scrollIntoView({behavior: 'smooth', block: 'center'});
    }
  }

  function validateCampaignForm(form){
    var errors = [];
    var firstInvalid = null;

    var titleEl = form.querySelector('[name="title"]');
    var descEl = form.querySelector('[name="description"]');
    var startEl = form.querySelector('[name="start_at"]');
    var endEl = form.querySelector('[name="end_at"]');
    var statusEl = form.querySelector('select[name="status"]');

    function mark(el){
      if (el && !firstInvalid) firstInvalid = el;
      if (el) el.classList.add('is-invalid');
    }

    // Title: 3..120 chars
    if (titleEl){
      var t = (titleEl.value || '').trim();
      if (t.length < 3) { errors.push('Title must be at least 3 characters.'); mark(titleEl); }
      else if (t.length > 120) { errors.push('Title must be at most 120 characters.'); mark(titleEl); }
    }

    // Status validation
    if (statusEl){
      var st = String(statusEl.value || '').trim();
      if (ALLOWED_STATUS.indexOf(st) === -1){ errors.push('Invalid status selected.'); mark(statusEl); }
    }

    // Date order check (if both provided)
    var startVal = startEl && startEl.value ? new Date(startEl.value) : null;
    var endVal = endEl && endEl.value ? new Date(endEl.value) : null;
    if (startVal && endVal && (endVal.getTime() < startVal.getTime())){
      errors.push('End date/time must be after start date/time.');
      mark(endEl);
    }

    return { valid: errors.length === 0, errors: errors, firstInvalid: firstInvalid };
  }

  function handleSubmit(e){
    var form = e.target;
    var action = getAction(form);
    if (!action) return; // ignore unrelated forms

    clearErrors(form);

    var result = { valid: true, errors: [], firstInvalid: null };
    if (action === 'create' || action === 'update') {
      result = validateCampaignForm(form);
    } else if (action === 'link_surveys') {
      // optional: allow empty to clear links
      // could add size/limit checks here later
      result.valid = true;
    } else if (action === 'assign_agents') {
      // optional: allow empty to clear assignments
      result.valid = true;
    }

    if (!result.valid){
      e.preventDefault();
      showErrors(form, result.errors, result.firstInvalid);
    }
  }

  function init(){
    document.querySelectorAll('form').forEach(function(form){
      var action = getAction(form);
      if (['create','update','link_surveys','assign_agents'].indexOf(action) !== -1){
        form.addEventListener('submit', handleSubmit);
      }
    });
  }

  if (document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();

