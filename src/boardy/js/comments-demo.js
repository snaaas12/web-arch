// Паттерн: загрузить массив из API и отрисовать
//
// fetch — HTTP-запрос из браузера (как curl)
// await — ждём ответ от сервера
// .json() — парсим JSON в JS-объект
// .map() — для каждого элемента создаём HTML
// .join('') — склеиваем массив строк в одну
// innerHTML — вставляем в контейнер

const API = 'https://api.snaaas.ai-info.ru';    // ← ваш домен
const POST_ID = 21;                      // ← ID родителя

async function loadItems() {
    const res = await fetch(`${API}/api/posts/${POST_ID}/comments`);
    const data = await res.json();
    document.getElementById('list').innerHTML = data.items.map(item => `
        <div>
            <strong>${esc(item.author_name)}</strong>
            <p>${esc(item.body)}</p>
        </div>
    `).join('');
}

loadItems();  // запускаем при загрузке страницы


// Паттерн: отправить данные из формы в API
//
// addEventListener — реагируем на клик без перезагрузки
// JSON.stringify — превращаем JS-объект в JSON-строку
// Content-Type — говорим серверу: тело в формате JSON
// После успеха — очищаем поле и перезагружаем список

document.getElementById('btn').addEventListener('click', async () => {
    const body = document.getElementById('body').value.trim();
    if (!body) return;                    // ← не отправляем пустой
    await fetch(`${API}/api/posts/${POST_ID}/comments`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({body: body})  // ← поля из вашей модели
    });
    document.getElementById('body').value = '';  // ← очистить
    loadItems();                           // ← обновить список
});

// Паттерн: экранирование HTML
// Без этого пользователь может вставить <script>
// и выполнить произвольный JS в браузерах других людей
//
// textContent автоматически экранирует спецсимволы
// innerHTML — вставляет как HTML (опасно!)

function esc(str) {
    const div = document.createElement('div');
    div.textContent = str;     // ← экранирует < > & "
    return div.innerHTML;      // ← безопасная строка
}
