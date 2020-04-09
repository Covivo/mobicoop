export default function formData (path, params, method='post') {
  const form = document.createElement('form');
  form.method = method;
  form.action = window.location.origin+path;

  if (params) {
    for (const key in params) {
      if (params.hasOwnProperty(key)) {
        const hiddenField = document.createElement('input');
        hiddenField.type = 'hidden';
        hiddenField.name = key;
        hiddenField.value = params[key];
        form.appendChild(hiddenField);
      }
    }
  }

  document.body.appendChild(form);
  form.submit();
}