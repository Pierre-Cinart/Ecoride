// manageUsers.js
document.addEventListener('DOMContentLoaded', () => {
  const filter = document.getElementById('userFilter');

  filter.addEventListener('change', (e) => {
    const selected = e.target.value;
    const url = new URL(window.location.href);
    url.searchParams.set('filter', selected);
    url.searchParams.set('page', '1'); // reset page à 1
    window.location.href = url.toString();
  });
});
