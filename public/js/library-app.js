const STORAGE_KEY = 'biblioteca-app-state';
let state = { settings: { maxBooks: 3, loanDays: 14 }, users: [], books: [], loans: [], penalties: [] };
let currentUser = null;

const $ = (id) => document.getElementById(id);

async function bootstrap() {
  const cached = localStorage.getItem(STORAGE_KEY);
  if (cached) {
    state = JSON.parse(cached);
  }

  try {
    const response = await fetch('/api/bootstrap');
    if (response.ok) {
      state = await response.json();
      persist();
    }
  } catch (_) {
    // Si API no disponible, se mantiene localStorage.
  }

  render();
}

function persist() {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
}

function login() {
  const email = $('login-email').value.trim().toLowerCase();
  currentUser = state.users.find((u) => u.email.toLowerCase() === email) || null;
  if (!currentUser) {
    alert('Usuario no encontrado.');
    return;
  }
  render();
}

function canBorrow(user) {
  if (!user) return false;
  if (user.penaltyUntil && new Date(user.penaltyUntil) > new Date()) return false;
  const active = state.loans.filter((l) => l.user_id === user.id && !l.returned_at).length;
  return active < state.settings.maxBooks;
}

function borrowBook(bookId) {
  if (!currentUser) return;
  if (!canBorrow(currentUser)) {
    alert('No puedes pedir más libros o tienes penalización.');
    return;
  }
  const book = state.books.find((b) => b.id === bookId);
  if (!book || !book.available) return;

  const due = new Date();
  due.setDate(due.getDate() + state.settings.loanDays);
  state.loans.push({
    id: Math.max(0, ...state.loans.map((l) => l.id)) + 1,
    user_id: currentUser.id,
    book_id: bookId,
    borrowed_at: new Date().toISOString().slice(0, 10),
    due_date: due.toISOString().slice(0, 10),
    returned_at: null,
  });
  book.available = false;
  book.timesLoaned = (book.timesLoaned || 0) + 1;
  persist();
  render();
}

function returnBook(loanId) {
  const loan = state.loans.find((l) => l.id === loanId);
  if (!loan || loan.returned_at) return;
  loan.returned_at = new Date().toISOString().slice(0, 10);

  const book = state.books.find((b) => b.id === loan.book_id);
  if (book) book.available = true;

  if (loan.returned_at > loan.due_date) {
    const user = state.users.find((u) => u.id === loan.user_id);
    if (user) {
      const penaltyEnd = new Date();
      penaltyEnd.setDate(penaltyEnd.getDate() + 7);
      user.penaltyUntil = penaltyEnd.toISOString().slice(0, 10);
      user.lateReturns = (user.lateReturns || 0) + 1;
      state.penalties.push({
        id: Math.max(0, ...state.penalties.map((p) => p.id)) + 1,
        user_id: user.id,
        until: user.penaltyUntil,
        reason: 'Retraso en devolución',
      });
    }
  }

  persist();
  render();
}

function renderCatalog() {
  const el = $('catalog');
  el.innerHTML = '';
  state.books.forEach((book) => {
    const div = document.createElement('div');
    div.className = 'book';
    const disabled = !book.available || !canBorrow(currentUser);
    div.innerHTML = `
      <strong>${book.title}</strong> - ${book.author}
      <div>Estado: ${book.available ? 'Disponible' : 'Prestado'}</div>
      <button ${disabled ? 'disabled' : ''} data-book="${book.id}">Solicitar préstamo</button>
    `;
    div.querySelector('button')?.addEventListener('click', () => borrowBook(book.id));
    el.appendChild(div);
  });
}

function renderLoans() {
  const el = $('my-loans');
  if (!currentUser) return;
  const myLoans = state.loans.filter((l) => l.user_id === currentUser.id);
  el.innerHTML = myLoans.map((loan) => {
    const book = state.books.find((b) => b.id === loan.book_id);
    return `<div class="book">
      <strong>${book?.title || 'Libro'}</strong><br>
      Devolución: ${loan.due_date}<br>
      Penalización activa: ${currentUser.penaltyUntil || 'No'}<br>
      ${loan.returned_at ? `Devuelto: ${loan.returned_at}` : `<button onclick="returnBook(${loan.id})">Devolver</button>`}
    </div>`;
  }).join('') || 'Sin préstamos.';
}

function renderAdmin() {
  if (!currentUser || currentUser.role !== 'admin') return;
  $('max-books').value = state.settings.maxBooks;
  $('loan-days').value = state.settings.loanDays;

  const topBooks = [...state.books].sort((a, b) => (b.timesLoaned || 0) - (a.timesLoaned || 0)).slice(0, 3);
  const topLateUsers = [...state.users].sort((a, b) => (b.lateReturns || 0) - (a.lateReturns || 0)).slice(0, 3);
  const durations = state.loans.filter((l) => l.returned_at).map((l) =>
    (new Date(l.returned_at) - new Date(l.borrowed_at)) / 86400000
  );
  const avgReturn = durations.length ? (durations.reduce((a, b) => a + b, 0) / durations.length).toFixed(2) : '0';

  $('stats').textContent = JSON.stringify({ topBooks, topLateUsers, avgReturnDays: avgReturn }, null, 2);
}

function saveSettings() {
  state.settings.maxBooks = Number($('max-books').value || 3);
  state.settings.loanDays = Number($('loan-days').value || 14);
  persist();
  render();
}

function render() {
  $('screen-login').classList.toggle('hidden', !!currentUser);
  $('screen-catalog').classList.toggle('hidden', !currentUser);
  $('screen-loans').classList.toggle('hidden', !currentUser);
  $('screen-admin').classList.toggle('hidden', !(currentUser && currentUser.role === 'admin'));

  if (currentUser) {
    renderCatalog();
    renderLoans();
    renderAdmin();
  }
}

$('btn-login').addEventListener('click', login);
$('save-settings').addEventListener('click', saveSettings);
bootstrap();
window.returnBook = returnBook;
